import SwiftUI
import UIKit

struct ContentView: View {
    @Environment(\.appLanguage) private var language
    @Environment(\.horizontalSizeClass) private var horizontalSizeClass
    @State private var tabNavigation: AppTabNavigationState
    @StateObject private var gameCatalog = GameCatalogStore.shared
    @StateObject private var patchDraftCoordinator = PatchDraftCoordinator()
    @AppStorage(FeatureVisibility.cleanerStorageKey) private var cleanerEnabled = true
    @AppStorage(AppThemePreferences.accentStorageKey) private var accentRaw = AppThemePreferences.defaultAccent.rawValue

    init() {
#if targetEnvironment(simulator)
        let arguments = ProcessInfo.processInfo.arguments
        let initialTab: Int
        if arguments.contains("--simulate-files-tab") {
            initialTab = AppSection.files.rawValue
        } else if arguments.contains("--simulate-cleaner-tab") {
            initialTab = AppSection.cleaner.rawValue
        } else {
            initialTab = AppSection.home.rawValue
        }
#else
        let initialTab = AppSection.home.rawValue
#endif
        _tabNavigation = State(initialValue: AppTabNavigationState(selectedTab: initialTab))
    }

    private var accentColor: Color {
        (AppAccentSpectrum(rawValue: accentRaw) ?? .azure).color
    }

    var body: some View {
        ZStack {
            Color.clear.ignoresSafeArea()
            Group {
                if horizontalSizeClass == .regular {
                    regularLayout
                } else {
                    compactLayout
                }
            }
            .background(Color.clear)
        }
        .tint(accentColor)
        .imageScale(.small)
        .onChange(of: cleanerEnabled) { _ in
            tabNavigation.reconcileSelection(with: featureVisibility)
        }
        .onAppear {
            tabNavigation.reconcileSelection(with: featureVisibility)
        }
        .onReceive(gameCatalog.$catalog) { _ in
            tabNavigation.reconcileSelection(with: featureVisibility)
        }
        .apexScreenBackground()
    }

    private var compactLayout: some View {
        TabView(selection: tabSelection) {
            ForEach(featureVisibility.visibleSections) { section in
                sectionContent(section)
                    .tabItem {
                        CompactTabLabel(
                            title: language.text(section.titleKey),
                            systemImage: section.systemImage
                        )
                    }
                    .tag(section.rawValue)
            }
        }
    }

    private var regularLayout: some View {
        NavigationSplitView {
            List(selection: Binding<AppSection?>(
                get: { selectedVisibleSection },
                set: { if let section = $0 { tabNavigation.select(section.rawValue) } }
            )) {
                ForEach(featureVisibility.visibleSections) { section in
                    Label(
                        language.text(section.titleKey),
                        systemImage: section.systemImage
                    )
                    .tag(section)
                }
            }
            .navigationTitle("APEX IPA")
            .listStyle(.sidebar)
        } detail: {
            sectionContent(selectedVisibleSection)
        }
    }

    @ViewBuilder
    private func sectionContent(_ section: AppSection) -> some View {
        switch section {
        case .home:
            DashboardView(cleanerEnabled: $cleanerEnabled)
        case .games:
            GameCatalogView()
        case .patches:
            PatchProjectsView()
                .environmentObject(patchDraftCoordinator)
        case .wallpaper:
            WallpaperLabView()
        case .files:
            AppDataBrowserView(tabSession: filesTabSession)
        case .cleaner:
            CleanerView()
        }
    }

    private var tabSelection: Binding<Int> {
        Binding(
            get: { tabNavigation.selectedTab },
            set: { tabNavigation.select($0) }
        )
    }

    private var filesTabSession: Binding<FilesTabSession> {
        Binding(
            get: { tabNavigation.filesTabs },
            set: { tabNavigation.setFilesTabs($0) }
        )
    }

    private var featureVisibility: FeatureVisibility {
        FeatureVisibility(
            cleanerEnabled: gameCatalog.catalog.isPageVisible("cleaner"),
            homeEnabled: gameCatalog.catalog.isPageVisible("home"),
            gamesEnabled: gameCatalog.catalog.isPageVisible("games"),
            patchesEnabled: gameCatalog.catalog.isPageVisible("patches"),
            wallpaperEnabled: gameCatalog.catalog.isPageVisible("wallpaper"),
            filesEnabled: gameCatalog.catalog.isPageVisible("files")
        )
    }

    private var selectedVisibleSection: AppSection {
        guard let section = AppSection(rawValue: tabNavigation.selectedTab),
              featureVisibility.isVisible(section) else {
            return featureVisibility.visibleSections.first ?? .home
        }
        return section
    }
}

private struct CompactTabLabel: View {
    let title: String
    let systemImage: String

    @ViewBuilder
    var body: some View {
        if let image = UIImage(
            systemName: systemImage,
            withConfiguration: UIImage.SymbolConfiguration(pointSize: 17, weight: .medium)
        )?.withRenderingMode(.alwaysTemplate) {
            Image(uiImage: image)
        } else {
            Image(systemName: systemImage)
                .font(.system(size: 17, weight: .medium))
        }
        Text(title)
    }
}

private extension AppSection {
    var titleKey: String {
        switch self {
        case .home: return "tab.home"
        case .games: return "tab.games"
        case .patches: return "tab.patches"
        case .wallpaper: return "tab.wallpaper"
        case .files: return "tab.files"
        case .cleaner: return "tab.cleaner"
        }
    }

    var systemImage: String {
        switch self {
        case .home: return "house.fill"
        case .games: return "square.grid.2x2.fill"
        case .patches: return "square.stack.3d.up.fill"
        case .wallpaper: return "photo.on.rectangle.angled"
        case .files: return "folder.fill"
        case .cleaner: return "sparkles"
        }
    }
}

// MARK: - Home dashboard (redesigned)

private struct DashboardView: View {
    @Environment(\.appLanguage) private var language
    @EnvironmentObject private var appState: AppState
    @State private var showSettings = false
    @State private var licenseKeyCopied = false
    @StateObject private var catalogStore = GameCatalogStore.shared
    @Binding var cleanerEnabled: Bool
    @AppStorage(AppThemePreferences.accentStorageKey) private var accentRaw = AppThemePreferences.defaultAccent.rawValue

    private var accent: AppAccentSpectrum {
        AppAccentSpectrum(rawValue: accentRaw) ?? .azure
    }

    var body: some View {
        NavigationStack {
            ZStack {
                RemoteAppBackground()
                ScrollView {
                    VStack(spacing: 18) {
                        heroCard
                        deviceCard
                        licenseCard
                        socialCard
                        creditsCard
                    }
                    .padding(.horizontal, 16)
                    .padding(.vertical, 12)
                }
                .background(Color.clear)
            }
            .apexScreenBackground()
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button { showSettings = true } label: {
                        Image(systemName: "gearshape.fill")
                            .symbolRenderingMode(.hierarchical)
                    }
                    .accessibilityLabel(language.text("accessibility.open_settings"))
                }
            }
            .sheet(isPresented: $showSettings) {
                SettingsView()
                    .environmentObject(appState)
            }
        }
    }

    private var heroCard: some View {
        HStack(spacing: 16) {
            CachedRemoteImage(url: catalogStore.catalog.brandAvatarURL.flatMap(URL.init(string:))) {
                AppLogo(size: 64)
            }
            .scaledToFill()
            .frame(width: 64, height: 64)
            .clipShape(RoundedRectangle(cornerRadius: 15, style: .continuous))

            VStack(alignment: .leading, spacing: 6) {
                Text(catalogStore.catalog.brandTitle?.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty == false ? catalogStore.catalog.brandTitle! : "APEX IPA")
                    .font(.title2.weight(.bold))
                Text(catalogStore.catalog.brandSubtitle?.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty == false ? catalogStore.catalog.brandSubtitle! : language.text("dashboard.tagline"))
                    .font(.subheadline)
                    .foregroundStyle(.secondary)
                    .fixedSize(horizontal: false, vertical: true)

            }

            Spacer(minLength: 0)
        }
        .apexCard()
        .overlay(alignment: .topTrailing) {
            Circle()
                .fill(accent.color.opacity(0.18))
                .frame(width: 90, height: 90)
                .blur(radius: 2)
                .offset(x: 28, y: -28)
                .allowsHitTesting(false)
        }
        .clipShape(RoundedRectangle(cornerRadius: 20, style: .continuous))
    }

    private var deviceCard: some View {
        VStack(alignment: .leading, spacing: 14) {
            sectionHeader(
                title: language.text("common.device"),
                systemImage: "iphone"
            )

            metricRow(
                title: language.text("settings.device"),
                value: AppInfo.hardwareDisplayName
            )
            Divider().opacity(0.5)
            metricRow(
                title: language.text("settings.ios_version"),
                value: "\(AppInfo.osVersion) (\(AppInfo.osBuild))"
            )
            Divider().opacity(0.5)
            HStack {
                Text(language.text("settings.compatibility"))
                    .foregroundStyle(.secondary)
                Spacer()
                StatusBadge(
                    text: language.text(appState.isSupported ? "settings.supported" : "settings.unsupported"),
                    tone: appState.isSupported ? .success : .danger
                )
            }

            if appState.kernelExploitApplicable && AppInfo.versionTuple.major < 26 {
                Divider().opacity(0.5)
                HStack {
                    Text(language.text("dashboard.kernel_status"))
                        .foregroundStyle(.secondary)
                    Spacer()
                    if appState.kernelExploitRunning {
                        HStack(spacing: 6) {
                            ProgressView().controlSize(.small)
                            Text(language.text("dashboard.kernel_running"))
                                .font(.subheadline)
                                .foregroundStyle(.secondary)
                        }
                    } else {
                        StatusBadge(
                            text: language.text(appState.exploitStatus.isSuccess ? "dashboard.kernel_active" : "dashboard.kernel_inactive"),
                            tone: appState.exploitStatus.isSuccess ? .success : .neutral
                        )
                    }
                }
            }

            Text(language.text("settings.supported_range_summary"))
                .font(.caption)
                .foregroundStyle(.secondary)
                .padding(.top, 2)
        }
        .apexCard()
    }

    private var licenseCard: some View {
        VStack(alignment: .leading, spacing: 14) {
            sectionHeader(title: "License", systemImage: "checkmark.shield.fill")

            Button {
                UIPasteboard.general.string = fullLicenseKey
                licenseKeyCopied = true
                DispatchQueue.main.asyncAfter(deadline: .now() + 1.5) {
                    licenseKeyCopied = false
                }
            } label: {
                licenseRow(
                    label: "Key",
                    value: licenseKeyText,
                    trailingSymbol: licenseKeyCopied ? "checkmark" : "doc.on.doc"
                )
            }
            .buttonStyle(.plain)

            Divider().opacity(0.5)
            licenseRow(label: "Hạn", value: APIClientRenderTemplate("%tserver_timekeyt%"))
            Divider().opacity(0.5)
            licenseRow(label: "Thiết bị", value: APIClientRenderTemplate("%tserver_max_devices%"))
            Divider().opacity(0.5)
            licenseRow(
                label: "Trạng thái",
                value: APIClientRenderTemplate("%tserver_key_status%"),
                singleLine: true
            )
        }
        .frame(maxWidth: .infinity, alignment: .leading)
        .apexCard()
    }

    private var licenseKeyText: String {
        APIClientRenderTemplate("%tserver_key%")
    }

    private var fullLicenseKey: String {
        let info = APIClient.currentKeyInfo() as? [String: Any] ?? [:]
        for field in ["licenseKey", "license_key", "key"] {
            if let value = info[field] as? String,
               !value.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty {
                return value
            }
        }
        return APIClient.currentKeyText()
    }

    private func licenseRow(
        label: String,
        value: String,
        trailingSymbol: String? = nil,
        singleLine: Bool = false
    ) -> some View {
        HStack(alignment: .top, spacing: 12) {
            Text(label + ":")
                .foregroundStyle(.secondary)
                .fixedSize(horizontal: true, vertical: false)
                .frame(width: 82, alignment: .leading)

            Text(value)
                .foregroundStyle(.white)
                .multilineTextAlignment(.leading)
                .fixedSize(horizontal: false, vertical: true)
                .lineLimit(singleLine ? 1 : nil)
                .minimumScaleFactor(singleLine ? 0.72 : 1)
                .frame(maxWidth: .infinity, alignment: .leading)

            if let trailingSymbol {
                Image(systemName: trailingSymbol)
                    .foregroundStyle(.white)
                    .accessibilityHidden(true)
            }
        }
        .font(.subheadline.monospaced())
        .contentShape(Rectangle())
        .frame(maxWidth: .infinity, alignment: .leading)
    }

    private var socialCard: some View {
        linkCard(
            title: language.text("settings.social_media"),
            icon: "bubble.left.and.bubble.right.fill",
            rows: catalogStore.catalog.resolvedSocialLinks
        )
    }

    private var creditsCard: some View {
        linkCard(
            title: language.text("settings.credits"),
            icon: "star.fill",
            rows: catalogStore.catalog.resolvedCredits
        )
    }

    private func linkCard(title: String, icon: String, rows: [RemoteProfileLink]) -> some View {
        VStack(alignment: .leading, spacing: 4) {
            sectionHeader(title: title, systemImage: icon).padding(.bottom, 6)
            ForEach(Array(rows.enumerated()), id: \.offset) { index, row in
                if index > 0 { Divider().opacity(0.4).padding(.vertical, 4) }
                Button {
                    if let url = URL(string: row.url) { UIApplication.shared.open(url) }
                } label: {
                    HStack(spacing: 12) {
                        Image(systemName: resolvedSymbol(for: row)).foregroundStyle(accent.color).frame(width: 28)
                        VStack(alignment: .leading, spacing: 2) {
                            Text(row.name).font(.body.weight(.semibold)).foregroundStyle(.primary)
                            Text(row.role).font(.caption).foregroundStyle(.secondary).multilineTextAlignment(.leading)
                        }
                        Spacer()
                        Image(systemName: "arrow.up.right").font(.caption.bold()).foregroundStyle(.secondary)
                    }.contentShape(Rectangle())
                }.buttonStyle(.plain)
            }
        }.apexCard()
    }

    private func resolvedSymbol(for row: RemoteProfileLink) -> String {
        let configured = row.appIcon?.trimmingCharacters(in: .whitespacesAndNewlines) ?? ""
        if !configured.isEmpty, UIImage(systemName: configured) != nil { return configured }
        return symbol(for: row.icon)
    }

    private func symbol(for fontAwesomeClass: String) -> String {
        let icon = fontAwesomeClass.lowercased()
        if icon.contains("telegram") || icon.contains("paper-plane") { return "paperplane.fill" }
        if icon.contains("facebook") { return "person.2.fill" }
        if icon.contains("instagram") || icon.contains("camera") { return "camera.fill" }
        if icon.contains("youtube") || icon.contains("play") { return "play.rectangle.fill" }
        if icon.contains("tiktok") || icon.contains("music") { return "music.note" }
        if icon.contains("discord") || icon.contains("comments") { return "bubble.left.and.bubble.right.fill" }
        if icon.contains("twitter") || icon.contains("x-twitter") { return "bubble.left.fill" }
        if icon.contains("whatsapp") || icon.contains("phone") { return "phone.fill" }
        if icon.contains("globe") || icon.contains("website") { return "globe" }
        if icon.contains("envelope") || icon.contains("mail") { return "envelope.fill" }
        if icon.contains("comment") || icon.contains("message") { return "message.fill" }
        if icon.contains("github") || icon.contains("code") { return "chevron.left.forwardslash.chevron.right" }
        if icon.contains("hammer") || icon.contains("tool") { return "hammer.fill" }
        if icon.contains("star") { return "star.fill" }
        if icon.contains("user") || icon.contains("person") { return "person.crop.circle.fill" }
        return "link"
    }

    private func sectionHeader(title: String, systemImage: String) -> some View {
        HStack(spacing: 8) {
            Image(systemName: systemImage)
                .font(.subheadline.weight(.bold))
                .foregroundStyle(accent.color)
            Text(title)
                .font(.subheadline.weight(.bold))
            Spacer()
        }
    }

    private func metricRow(title: String, value: String) -> some View {
        HStack(alignment: .firstTextBaseline) {
            Text(title)
                .foregroundStyle(.secondary)
            Spacer(minLength: 12)
            Text(value)
                .font(.body.weight(.semibold))
                .multilineTextAlignment(.trailing)
        }
    }
}
