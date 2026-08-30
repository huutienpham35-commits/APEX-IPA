import SwiftUI
import UIKit

@main
struct ThreeOneOSFiveApp: App {
    @StateObject private var appState = AppState()
    @StateObject private var fileOperationCoordinator = FileOperationCoordinator()
    @StateObject private var gameCatalog = GameCatalogStore.shared
    @AppStorage(AppLanguage.storageKey) private var languageCode = AppLanguage.english.rawValue
    @AppStorage(AppThemePreferences.appearanceStorageKey) private var appearanceRaw = AppThemePreferences.defaultAppearance.rawValue
    @AppStorage(AppThemePreferences.accentStorageKey) private var accentRaw = AppThemePreferences.defaultAccent.rawValue
    @State private var showOnboarding = OnboardingStore.shouldShow()
    @State private var showNotice = false
    @State private var isInitialLoading = true
    @State private var initialLoadingProgress = 0.0
    @State private var initialLoadingMessage = "Đang kết nối máy chủ"
    @State private var connectionFailed = false
    @State private var connectionFailureTitle = "LỖI KẾT NỐI"
    @State private var connectionFailureMessage = "Không thể kết nối máy chủ dữ liệu."
    @State private var connectionFailureDetail = ""
    @State private var licenseAuthorized = false
    @State private var licenseAuthorizationStarted = false
    @State private var licenseAuthorizationAttempt = UUID()
    @State private var protectedContentStarted = false
    /// After user taps OK, don't force the notice again until next background→active.
    @State private var noticeDismissedUntilNextEnter = false
    @State private var showAttribution = false
    @Environment(\.scenePhase) private var scenePhase

    init() {
        Self.configureTransparentContainers()
        setupLogCapture()
        log("app: APEX IPA launching — iOS \(AppInfo.osVersion) (\(AppInfo.osBuild)) \(AppInfo.machineName)")
    }

    private static func configureTransparentContainers() {
        UITableView.appearance().backgroundColor = .clear
        UICollectionView.appearance().backgroundColor = .clear
        UIScrollView.appearance().backgroundColor = .clear

        // Keep chrome translucent so remote wallpaper is visible behind content.
        let navigationAppearance = UINavigationBarAppearance()
        navigationAppearance.configureWithTransparentBackground()
        navigationAppearance.backgroundColor = .clear
        navigationAppearance.shadowColor = .clear
        UINavigationBar.appearance().standardAppearance = navigationAppearance
        UINavigationBar.appearance().scrollEdgeAppearance = navigationAppearance
        UINavigationBar.appearance().compactAppearance = navigationAppearance
        UINavigationBar.appearance().isTranslucent = true

        let tabAppearance = UITabBarAppearance()
        tabAppearance.configureWithTransparentBackground()
        // Slight blur only on the tab bar strip — not a full-screen opaque fill.
        tabAppearance.backgroundEffect = UIBlurEffect(style: .systemUltraThinMaterialDark)
        tabAppearance.backgroundColor = UIColor.black.withAlphaComponent(0.18)
        tabAppearance.shadowColor = .clear
        UITabBar.appearance().standardAppearance = tabAppearance
        UITabBar.appearance().isTranslucent = true
        if #available(iOS 15.0, *) {
            UITabBar.appearance().scrollEdgeAppearance = tabAppearance
        }
    }

    private var language: AppLanguage {
        AppLanguage(rawValue: languageCode) ?? .english
    }

    private var preferredScheme: ColorScheme? {
        (AppAppearanceMode(rawValue: appearanceRaw) ?? .system).colorScheme
    }

    private var accentColor: Color {
        (AppAccentSpectrum(rawValue: accentRaw) ?? .azure).color
    }

    /// Pull live website config (no cache). Optionally present notice after fetch.
    private func refreshRemoteContent(presentNotice: Bool) {
        Task {
            let connected = await GameCatalogStore.shared.refresh(prefetch: true)
            await MainActor.run { connectionFailed = !connected }
            guard connected else { return }
            guard presentNotice else { return }
            await MainActor.run {
                evaluateNoticePresentation()
            }
        }
    }

    private var mandatoryUpdate: MandatoryUpdateRequirement? {
        let catalog = gameCatalog.catalog
        guard catalog.forceUpdate == true else { return nil }
        let minimum = (catalog.minimumVersion ?? "").trimmingCharacters(in: .whitespacesAndNewlines)
        guard !minimum.isEmpty,
              AppUpdateChecker.isNewer(minimum, than: AppUpdateChecker.currentVersion) else { return nil }
        let rawURL = (catalog.updateURL?.isEmpty == false ? catalog.updateURL : catalog.ipaDownloadURL) ?? ""
        guard let url = URL(string: rawURL), !rawURL.isEmpty else { return nil }
        let message = catalog.updateMessage?.trimmingCharacters(in: .whitespacesAndNewlines)
        return MandatoryUpdateRequirement(
            version: catalog.latestVersion?.isEmpty == false ? catalog.latestVersion! : minimum,
            message: message?.isEmpty == false ? message! : "Vui lòng cập nhật phiên bản mới nhất để tiếp tục sử dụng.",
            url: url
        )
    }

    private func startLicenseAuthorization() {
        guard !licenseAuthorizationStarted else { return }
        licenseAuthorizationStarted = true
        connectionFailed = false
        connectionFailureDetail = ""
        isInitialLoading = true
        initialLoadingProgress = 0
        initialLoadingMessage = "Đang kết nối máy chủ"

        let attempt = UUID()
        licenseAuthorizationAttempt = attempt

        let packageToken = ProtectedConfiguration.packageToken
            .trimmingCharacters(in: .whitespacesAndNewlines)
        guard packageToken.hasPrefix("pkg_"), packageToken.count >= 24 else {
            failLicenseAuthorization(
                attempt: attempt,
                detail: "Package token giải mã không hợp lệ (length: \(packageToken.count))."
            )
            return
        }

        log("license: configuring SDK with validated package token (length: \(packageToken.count))")
        APIClientConfigure(packageToken)
        // Use the SDK compatibility entry point that performs authorization and
        // the paid-capability check as one operation. Some SDK builds leave the
        // split StartAuthorization -> PerformAuthorized flow pending forever.
        log("license: starting legacy combined authorization flow")
        APIClientStart({
            DispatchQueue.main.async {
                guard licenseAuthorizationAttempt == attempt else { return }
                licenseAuthorized = true
                startProtectedContentIfNeeded()
            }
        })

        DispatchQueue.main.asyncAfter(deadline: .now() + 20) {
            guard licenseAuthorizationAttempt == attempt,
                  !licenseAuthorized else { return }
            log("license: authorization timed out after 20 seconds")
            failLicenseAuthorization(
                attempt: attempt,
                detail: "Legacy APIClientStart timeout: SDK không trả callback paid sau 20 giây."
            )
        }
    }

    private func startProtectedContentIfNeeded() {
        guard !protectedContentStarted else { return }
        protectedContentStarted = true
        loadInitialContent()
        if !showOnboarding {
            appState.detectSupport()
        }
    }

    private func revokeLicenseAccess() {
        DispatchQueue.main.async {
            licenseAuthorized = false
        }
    }

    private func failLicenseAuthorization(attempt: UUID, detail: String) {
        guard licenseAuthorizationAttempt == attempt,
              !licenseAuthorized else { return }
        licenseAuthorizationStarted = false
        connectionFailureTitle = "LỖI XÁC THỰC"
        connectionFailureMessage = "Máy chủ cấp quyền không phản hồi hoặc đã từ chối yêu cầu."
        connectionFailureDetail = detail
        connectionFailed = true
        isInitialLoading = false
    }

    private func retryConnection() {
        connectionFailed = false
        connectionFailureDetail = ""
        if licenseAuthorized {
            initialLoadingProgress = 0
            isInitialLoading = true
            loadInitialContent()
        } else {
            startLicenseAuthorization()
        }
    }

    private func loadInitialContent() {
        Task {
            await MainActor.run {
                AmbientMediaController.shared.setPlaybackAllowed(false)
                initialLoadingProgress = 0.04
                initialLoadingMessage = "Đang tải cấu hình"
            }
            let connected = await GameCatalogStore.shared.refresh(prefetch: false)
            connectionFailed = !connected
            guard connected else {
                connectionFailureTitle = "LỖI TẢI CẤU HÌNH"
                connectionFailureMessage = "Không thể tải dữ liệu từ máy chủ catalog."
                connectionFailureDetail = GameCatalogStore.shared.lastError ?? "Không có chi tiết lỗi."
                isInitialLoading = false
                return
            }
            await MainActor.run {
                initialLoadingProgress = 0.20
                initialLoadingMessage = "Đang kiểm tra package"
            }
            await GameCatalogStore.shared.prefetchPackages { completed, total in
                let fraction = total == 0 ? 1.0 : Double(completed) / Double(total)
                initialLoadingProgress = 0.20 + (fraction * 0.60)
                initialLoadingMessage = total == 0
                    ? "Không có package cần tải"
                    : "Đang kiểm tra package \(completed)/\(total)"
            }
            let mediaReady = await AmbientMediaController.shared.prepareForPlayback(
                catalog: GameCatalogStore.shared.catalog
            ) { progress, message in
                initialLoadingProgress = progress
                initialLoadingMessage = message
            }
            guard mediaReady else {
                connectionFailureTitle = "LỖI TẢI MEDIA"
                connectionFailureMessage = "Không thể chuẩn bị hình nền, video hoặc âm thanh từ máy chủ."
                connectionFailureDetail = "AmbientMediaController.prepareForPlayback trả về false."
                connectionFailed = true
                isInitialLoading = false
                return
            }
            await MainActor.run {
                initialLoadingProgress = 1.0
                initialLoadingMessage = "Hoàn tất"
            }
            try? await Task.sleep(nanoseconds: 180_000_000)
            await MainActor.run {
                isInitialLoading = false
                if !showOnboarding { evaluateNoticePresentation() }
            }
            // Give SwiftUI one render pass to remove the 100% overlay first.
            await Task.yield()
            await MainActor.run {
                AmbientMediaController.shared.setPlaybackAllowed(true)
            }
        }
    }

    private func evaluateNoticePresentation() {
        guard !showOnboarding else {
            showNotice = false
            return
        }
        if NoticeStore.isSnoozed {
            showNotice = false
            return
        }
        if noticeDismissedUntilNextEnter {
            // User already closed with OK in this foreground session.
            return
        }
        let message = GameCatalogStore.shared.catalog.noticeMessage
            .trimmingCharacters(in: .whitespacesAndNewlines)
        showNotice = !message.isEmpty
    }

    private func finishLanguageOnboarding() {
        OnboardingStore.markCompleted()
        withAnimation(.spring(response: 0.42, dampingFraction: 0.86)) {
            showOnboarding = false
        }
        appState.detectSupport()
        noticeDismissedUntilNextEnter = false
        refreshRemoteContent(presentNotice: true)
    }

    var body: some Scene {
        WindowGroup {
            ZStack {
                // ContentView owns the app's single persistent wallpaper layer.
                ContentView()
                    .environmentObject(appState)
                    .environmentObject(fileOperationCoordinator)
                    .environmentObject(AmbientMediaController.shared)
                    .clearChromeForWallpaper()
                    .environment(\.appLanguage, language)
                    .environment(\.locale, language.locale)
                    .preferredColorScheme(preferredScheme)
                    .tint(accentColor)
                    .opacity(showOnboarding || !licenseAuthorized ? 0 : 1)
                    .allowsHitTesting(licenseAuthorized && !showOnboarding && !showNotice && !connectionFailed && !gameCatalog.catalog.resolvedMaintenanceEnabled)

                if showOnboarding {
                    OnboardingView {
                        finishLanguageOnboarding()
                    }
                    .environment(\.appLanguage, language)
                    .environment(\.locale, language.locale)
                    .preferredColorScheme(preferredScheme)
                    .tint(accentColor)
                    .transition(.opacity.combined(with: .scale(scale: 0.98)))
                    .zIndex(2)
                }

                if showNotice && !showOnboarding {
                    NoticeBannerView(
                        onDismiss: {
                            // Close now; show again next time user enters the app.
                            noticeDismissedUntilNextEnter = true
                            withAnimation(.easeOut(duration: 0.2)) {
                                showNotice = false
                            }
                        },
                        onSnoozeOneHour: {
                            NoticeStore.hideForOneHour()
                            noticeDismissedUntilNextEnter = true
                            withAnimation(.easeOut(duration: 0.2)) {
                                showNotice = false
                            }
                        }
                    )
                    .environment(\.appLanguage, language)
                    .environment(\.locale, language.locale)
                    .transition(.opacity)
                    .zIndex(3)
                }

                if isInitialLoading {
                    InitialLoadingView(
                        progress: initialLoadingProgress,
                        message: initialLoadingMessage
                    )
                        .transition(.opacity)
                        .zIndex(10)
                }

                if !isInitialLoading && connectionFailed {
                    BlockingStatusView(
                        title: connectionFailureTitle,
                        message: connectionFailureMessage,
                        detail: connectionFailureDetail,
                        symbol: "wifi.slash",
                        retryAction: retryConnection
                    )
                    .zIndex(11)
                } else if !isInitialLoading && gameCatalog.catalog.resolvedMaintenanceEnabled {
                    BlockingStatusView(
                        title: gameCatalog.catalog.resolvedMaintenanceTitle,
                        message: gameCatalog.catalog.resolvedMaintenanceMessage,
                        symbol: "wrench.and.screwdriver.fill",
                        retryAction: nil
                    )
                    .zIndex(11)
                }

                if !isInitialLoading, let update = mandatoryUpdate {
                    MandatoryUpdateView(requirement: update)
                        .zIndex(20)
                }
            }
            .preferredColorScheme(preferredScheme)
            .tint(accentColor)
            .displayIdentityAttribution(isPresented: $showAttribution, enabled: licenseAuthorized && !showOnboarding && !showNotice)
            .sheet(isPresented: $showAttribution) {
                DisplayAttributionSheet()
            }
            .onAppear {
                startLicenseAuthorization()
            }
            .onOpenURL { url in
                _ = APIClientHandleOpenURL(url)
            }
            .onChange(of: scenePhase) { phase in
                AmbientMediaController.shared.handleScenePhase(phase)
                if phase == .background || phase == .inactive {
                    // Next active count as a new "enter app" → notice again (unless snoozed 1h).
                    if phase == .background {
                        noticeDismissedUntilNextEnter = false
                    }
                    return
                }
                guard phase == .active else { return }
                guard licenseAuthorized else { return }
                // Re-fetch config so website edits appear without rebuilding IPA.
                refreshRemoteContent(presentNotice: !showOnboarding)
                if !showOnboarding {
                    appState.detectSupport()
                }
            }
        }
    }
}

private struct InitialLoadingView: View {
    let progress: Double
    let message: String

    var body: some View {
        ZStack {
            AppTheme.canvas.ignoresSafeArea()
            VStack(spacing: 16) {
                Text("\(Int((min(max(progress, 0), 1) * 100).rounded()))%")
                    .font(.system(size: 42, weight: .black, design: .rounded))
                ProgressView(value: min(max(progress, 0), 1))
                    .progressViewStyle(.linear)
                    .tint(AppTheme.accent)
                    .frame(width: min(UIScreen.main.bounds.width - 64, 320))
                    .animation(.easeOut(duration: 0.24), value: progress)
                Text(message.uppercased())
                    .font(.caption.weight(.bold))
                    .tracking(2.4)
                    .foregroundStyle(.secondary)
            }
        }
    }
}

private struct BlockingStatusView: View {
    let title: String
    let message: String
    var detail: String = ""
    let symbol: String
    let retryAction: (() -> Void)?

    var body: some View {
        ZStack {
            AppTheme.canvas.ignoresSafeArea()
            VStack(spacing: 18) {
                Image(systemName: symbol)
                    .font(.system(size: 42, weight: .semibold))
                    .foregroundStyle(AppTheme.accent)
                Text(title).font(.title2.weight(.black)).multilineTextAlignment(.center)
                Text(message).foregroundStyle(.secondary).multilineTextAlignment(.center)
                if !detail.isEmpty {
                    ScrollView {
                        Text(detail)
                            .font(.caption.monospaced())
                            .foregroundStyle(.secondary)
                            .textSelection(.enabled)
                            .frame(maxWidth: .infinity, alignment: .leading)
                    }
                    .frame(maxHeight: 150)
                    .padding(12)
                    .background(.white.opacity(0.05), in: RoundedRectangle(cornerRadius: 10))
                }
                if let retryAction {
                    Button(action: retryAction) {
                        Label("RETRY", systemImage: "arrow.clockwise")
                            .font(.headline.weight(.bold))
                            .tracking(1.2)
                    }
                    .buttonStyle(RetryButtonStyle())
                    .hoverEffect(.highlight)
                }
            }
            .padding(28)
            .frame(maxWidth: 440)
        }
    }
}

private struct RetryButtonStyle: ButtonStyle {
    func makeBody(configuration: Configuration) -> some View {
        configuration.label
            .foregroundStyle(.white)
            .padding(.horizontal, 24)
            .padding(.vertical, 12)
            .background(
                Capsule()
                    .fill(AppTheme.accent)
                    .brightness(configuration.isPressed ? -0.14 : 0)
            )
            .overlay {
                Capsule()
                    .stroke(.white.opacity(configuration.isPressed ? 0.42 : 0.16), lineWidth: 1)
            }
            .shadow(
                color: AppTheme.accent.opacity(configuration.isPressed ? 0.18 : 0.42),
                radius: configuration.isPressed ? 4 : 12,
                y: configuration.isPressed ? 2 : 7
            )
            .scaleEffect(configuration.isPressed ? 0.92 : 1)
            .opacity(configuration.isPressed ? 0.82 : 1)
            .animation(
                .spring(response: 0.24, dampingFraction: 0.62),
                value: configuration.isPressed
            )
    }
}

private struct MandatoryUpdateRequirement {
    let version: String
    let message: String
    let url: URL
}

private struct MandatoryUpdateView: View {
    let requirement: MandatoryUpdateRequirement

    var body: some View {
        ZStack {
            AppTheme.canvas.ignoresSafeArea()
            VStack(spacing: 18) {
                Image(systemName: "arrow.down.app.fill")
                    .font(.system(size: 48, weight: .semibold))
                    .foregroundStyle(AppTheme.accent)
                Text("CẬP NHẬT BẮT BUỘC")
                    .font(.title2.weight(.black))
                    .multilineTextAlignment(.center)
                Text("APEX IPA [\(requirement.version)]")
                    .font(.headline.monospaced())
                Text(requirement.message)
                    .foregroundStyle(.secondary)
                    .multilineTextAlignment(.center)
                Button("CẬP NHẬT NGAY") {
                    UIApplication.shared.open(requirement.url)
                }
                .buttonStyle(.borderedProminent)
                .controlSize(.large)
                .tint(AppTheme.accent)
            }
            .padding(28)
            .frame(maxWidth: 440)
        }
    }
}

class AppState: ObservableObject {
    @Published var exploitStatus: ExploitStatus = .notStarted
    @Published var unsupportedMessage: String?
    @Published var kernelExploitRunning = false

    private var autoRunAttempted = false

    var kernelExploitApplicable: Bool {
        KernelExploit.isApplicable(
            major: AppInfo.versionTuple.major,
            minor: AppInfo.versionTuple.minor,
            patch: AppInfo.versionTuple.patch,
            build: AppInfo.osBuild
        )
    }

    var isSupported: Bool { unsupportedMessage == nil }

    func detectSupport() {
        let v = AppInfo.versionTuple
        let supported = ExploitSupportPolicy.isSupported(
            major: v.major,
            minor: v.minor,
            patch: v.patch,
            build: AppInfo.osBuild
        )
#if targetEnvironment(simulator)
        if ProcessInfo.processInfo.arguments.contains("--simulate-access") {
            exploitStatus = .success(method: "Simulator preview")
        }
#endif

        unsupportedMessage = supported ? nil : "iOS \(AppInfo.osVersion) (\(AppInfo.osBuild))"
        if let unsupportedMessage {
            exploitStatus = .unsupported(unsupportedMessage)
            return
        }

        let applicable = KernelExploit.isApplicable(
            major: v.major,
            minor: v.minor,
            patch: v.patch,
            build: AppInfo.osBuild
        )
        guard applicable else { return }

        refreshKernelExploitStatus()
        maybeAutoRunKernelExploit()
    }

    private func maybeAutoRunKernelExploit() {
        guard !kernelExploitRunning,
              !exploitStatus.isSuccess,
              !exploitStatus.isFailed,
              !autoRunAttempted else { return }
        autoRunAttempted = true
        log("app: starting kernel exploit automatically")
        runKernelExploitIfNeeded()
    }

    private func refreshKernelExploitStatus() {
        guard !kernelExploitRunning else { return }

        // iOS < 26: kernel R/W success persists (no sandbox probe)
        // iOS >= 26: verify full sandbox escape is still active
        if KernelExploit.requiresSandboxEscape {
            if KernelExploit.hasSandboxAccess() {
                if !exploitStatus.isSuccess {
                    exploitStatus = .success(method: "kexploit")
                    log("app: existing sandbox access is still active; skipping kernel exploit")
                }
            } else if exploitStatus.isSuccess {
                exploitStatus = .notStarted
                log("app: sandbox access is no longer active")
            }
        }
    }

    func runKernelExploitIfNeeded() {
        refreshKernelExploitStatus()
        guard !kernelExploitRunning,
              !exploitStatus.isSuccess,
              !exploitStatus.isFailed else { return }
        kernelExploitRunning = true
        exploitStatus = .notStarted
        log("app: running kernel exploit on background...")
        DispatchQueue.global(qos: .userInitiated).async {
            let ok = KernelExploit.run()
            DispatchQueue.main.async {
                self.kernelExploitRunning = false
                if ok {
                    self.exploitStatus = .success(method: "kexploit")
                    if KernelExploit.requiresSandboxEscape {
                        log("app: kernel exploit success — sandbox access verified")
                    } else {
                        log("app: kernel exploit success — kernel access active")
                    }
                } else {
                    self.exploitStatus = .failed(method: "kexploit", code: -1)
                    log("app: kernel exploit failed — relaunch the app before retrying")
                }
            }
        }
    }
}
