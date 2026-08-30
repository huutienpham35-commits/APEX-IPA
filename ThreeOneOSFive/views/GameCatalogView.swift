import SwiftUI
import UIKit

private enum CyberPalette {
    static let ink = Color(red: 0.015, green: 0.02, blue: 0.075)
    static let panel = Color(red: 0.055, green: 0.065, blue: 0.145)
    static let violet = Color(red: 0.64, green: 0.20, blue: 1.0)
    static let blue = Color(red: 0.12, green: 0.48, blue: 1.0)
    static let cyan = Color(red: 0.08, green: 0.86, blue: 1.0)
    static let gradient = LinearGradient(colors: [violet, blue], startPoint: .leading, endPoint: .trailing)
}

private struct CyberBackground: View {
    @ObservedObject private var media = AmbientMediaController.shared
    var body: some View {
        Group {
            if media.hasRemoteWallpaper {
                // Render above TabView's opaque UIKit backing view. The shared
                // controller keeps the player and playback position persistent.
                RemoteAppBackground()
            } else {
                AppTheme.canvas
            }
        }
        .ignoresSafeArea()
        .allowsHitTesting(false)
    }
}

private struct CyberPanel<Content: View>: View {
    @ViewBuilder let content: Content
    @ObservedObject private var store = GameCatalogStore.shared
    var body: some View {
        content.padding(16)
            .background(AppTheme.elevated.opacity(store.catalog.resolvedCardOpacity), in: RoundedRectangle(cornerRadius: 22, style: .continuous))
            .overlay(RoundedRectangle(cornerRadius: 22, style: .continuous).stroke(Color.primary.opacity(0.09), lineWidth: 1))
    }
}

struct GameCatalogView: View {
    @StateObject private var store = GameCatalogStore.shared
    @State private var searchText = ""
    private let columns = [GridItem(.flexible(), spacing: 12), GridItem(.flexible(), spacing: 12)]

    private var filteredGames: [GameCatalogGame] {
        let games: [GameCatalogGame] = store.catalog.orderedGames
        let query = searchText.trimmingCharacters(in: .whitespacesAndNewlines)
        guard !query.isEmpty else { return games }
        return games.filter { game in
            game.name.localizedCaseInsensitiveContains(query)
                || game.bundleID.localizedCaseInsensitiveContains(query)
        }
    }

    var body: some View {
        NavigationStack {
            ZStack {
                CyberBackground()
                ScrollView(showsIndicators: false) {
                    VStack(spacing: 20) {
                        sectionTitle
                        TextField("Tìm ứng dụng", text: $searchText)
                            .textFieldStyle(.plain).padding(.horizontal, 14).frame(height: 44)
                            .background(AppTheme.elevated.opacity(store.catalog.resolvedCardOpacity), in: RoundedRectangle(cornerRadius: 14))
                            .overlay(alignment: .trailing) { Image(systemName: "magnifyingglass").foregroundStyle(.secondary).padding(.trailing, 14) }
                        LazyVGrid(columns: columns, spacing: 12) {
                            ForEach(filteredGames) { game in
                                NavigationLink { GameDetailView(gameID: game.id) } label: {
                                    GameCard(game: game)
                                }
                                    .buttonStyle(CyberPressStyle())
                            }
                        }
                    }.padding(.horizontal, 16).padding(.top, 12).padding(.bottom, 34)
                }.refreshable { await store.refresh() }
            }
            .toolbar(.hidden, for: .navigationBar)
            .task { await store.refresh() }
        }
    }

    private var dashboardHeader: some View {
        HStack(spacing: 12) {
            ZStack {
                RoundedRectangle(cornerRadius: 15).fill(CyberPalette.gradient).frame(width: 50, height: 50)
                Image(systemName: "gamecontroller.fill").font(.title2.bold()).foregroundStyle(.white)
            }.shadow(color: CyberPalette.violet.opacity(0.55), radius: 14)
            VStack(alignment: .leading, spacing: 2) {
                Text("GAME CENTER").font(.system(size: 22, weight: .black, design: .rounded)).tracking(1.2)
                Text("Trợ thủ game · An toàn · Ổn định").font(.caption).foregroundStyle(.white.opacity(0.55))
            }
            Spacer()
            if store.isLoading || store.isPrefetching { ProgressView().tint(CyberPalette.cyan) }
            else { Image(systemName: "sparkles").font(.title3.bold()).foregroundStyle(CyberPalette.violet).padding(12).background(CyberPalette.panel, in: RoundedRectangle(cornerRadius: 14)) }
        }
    }

    private var devicePanel: some View {
        CyberPanel {
            VStack(spacing: 13) {
                infoRow(icon: "apple.logo", title: "Phiên bản iOS", value: UIDevice.current.systemVersion, color: CyberPalette.violet)
                Divider().overlay(.white.opacity(0.08))
                infoRow(icon: "iphone", title: "Thiết bị", value: UIDevice.current.model, color: CyberPalette.blue)
                Divider().overlay(.white.opacity(0.08))
                infoRow(icon: "checkmark.seal.fill", title: "Trạng thái", value: "Được hỗ trợ", color: .green)
            }
        }
    }

    private var sectionTitle: some View {
        HStack(spacing: 9) {
            Image(systemName: "square.grid.2x2.fill").foregroundStyle(AppTheme.accent)
            Text("ỨNG DỤNG").font(.caption.bold()).tracking(2.4).foregroundStyle(.secondary)
            Rectangle().fill(AppTheme.accent).frame(height: 1).opacity(0.4)
        }
    }

    private func infoRow(icon: String, title: String, value: String, color: Color) -> some View {
        HStack { Image(systemName: icon).frame(width: 28).foregroundStyle(color); Text(title).foregroundStyle(.white.opacity(0.55)); Spacer(); Text(value).fontWeight(.semibold).foregroundStyle(value == "Được hỗ trợ" ? color : .white) }
    }

    private func feedback(_ text: String, color: Color) -> some View {
        Text(text).font(.footnote.weight(.semibold)).foregroundStyle(color).frame(maxWidth: .infinity, alignment: .leading).padding(12).background(color.opacity(0.1), in: RoundedRectangle(cornerRadius: 12))
    }
}

private struct GameCard: View {
    let game: GameCatalogGame
    var body: some View {
        CyberPanel {
            VStack(alignment: .center, spacing: 12) {
                    CachedRemoteImage(url: game.iconURL.flatMap(URL.init(string:))) {
                        Image(systemName: "app.fill").font(.title).foregroundStyle(AppTheme.accent)
                    }.scaledToFill().frame(width: 72, height: 72).background(Color.primary.opacity(0.05), in: RoundedRectangle(cornerRadius: 17)).clipShape(RoundedRectangle(cornerRadius: 17))
                Text(game.name).font(.headline.bold()).foregroundStyle(.primary).lineLimit(2)
                Text(game.bundleID).font(.system(size: 9, weight: .medium, design: .monospaced)).foregroundStyle(.secondary).lineLimit(1)
            }
            .frame(maxWidth: .infinity)
        }
    }
}

private struct GameDetailView: View {
    let gameID: String
    @StateObject private var store = GameCatalogStore.shared
    @State private var selectedTabID = "aim"
    @State private var previewImageURL: URL?
    @Environment(\.dismiss) private var dismiss

    private var catalog: GameCatalog { store.catalog }
    private var game: GameCatalogGame? {
        let games: [GameCatalogGame] = catalog.games
        return games.first(where: { game in game.id == gameID })
    }
    private var items: [GameCatalogItem] {
        (game?.items ?? [])
            .filter { $0.category.rawValue == selectedTabID && store.isCatalogItemVisible($0) }
            .sorted { ($0.order ?? Int.max) < ($1.order ?? Int.max) }
    }
    private var availableTabs: [GameCatalogTab] {
        let fallbackTabs: [GameCatalogTab] = catalog.resolvedTabs
        guard let game else { return fallbackTabs }
        let ownTabs = game.resolvedTabs(fallback: fallbackTabs)
        guard let selected = game.tabIDs else { return ownTabs }
        return ownTabs.filter { selected.contains($0.id) }
    }
    private var selectedTab: GameCatalogTab {
        availableTabs.first(where: { tab in tab.id == selectedTabID })
            ?? availableTabs.first
            ?? catalog.resolvedTabs[0]
    }

    var body: some View {
        ZStack {
            CyberBackground()
            if let game {
                ScrollView(showsIndicators: false) {
                    VStack(spacing: 20) {
                        hero(game); categoryBar; itemPanel; launchButton(game)
                    }.padding(.horizontal, 16).padding(.bottom, 36)
                }
            }
        }
        .toolbar(.hidden, for: .navigationBar)
        .onAppear {
            if !availableTabs.contains(where: { $0.id == selectedTabID }) {
                selectedTabID = availableTabs.first?.id ?? "aim"
            }
        }
        .overlay(alignment: .topLeading) {
            LiquidGlassBackButton { dismiss() }
                .padding(.leading, 16).padding(.top, 8)
        }
        .sheet(isPresented: Binding(
            get: { previewImageURL != nil },
            set: { if !$0 { previewImageURL = nil } }
        )) {
            PatchImagePreview(url: previewImageURL) { previewImageURL = nil }
        }
        .alert("Không thể thay đổi patch", isPresented: Binding(
            get: { store.lastError != nil },
            set: { if !$0 { store.lastError = nil } }
        )) {
            Button("OK", role: .cancel) { store.lastError = nil }
        } message: {
            Text(store.lastError ?? "")
        }
    }

    private func hero(_ game: GameCatalogGame) -> some View {
        VStack(spacing: 12) {
            Text(game.name).font(.headline.bold()).padding(.top, 18)
            CachedRemoteImage(url: game.iconURL.flatMap(URL.init(string:))) {
                Image(systemName: "gamecontroller.fill").font(.system(size: 46)).foregroundStyle(CyberPalette.violet)
            }.scaledToFill().frame(width: 118, height: 118).background(Color.primary.opacity(0.06), in: RoundedRectangle(cornerRadius: 26)).clipShape(RoundedRectangle(cornerRadius: 26))
            Text(game.name).font(.title2.weight(.black))
            Text(game.bundleID).font(.caption.monospaced()).foregroundStyle(.secondary)
        }.frame(maxWidth: .infinity).padding(.top, 6)
    }

    private var categoryBar: some View {
        HStack(spacing: 3) {
            ForEach(availableTabs) { mode in
                Button { withAnimation(.spring(response: 0.3, dampingFraction: 0.78)) { selectedTabID = mode.id } } label: {
                    Label(mode.title, systemImage: mode.icon).font(.system(size: 14, weight: .bold)).lineLimit(1).minimumScaleFactor(0.78)
                        .foregroundStyle(selectedTabID == mode.id ? Color.white : Color.primary.opacity(0.7)).frame(maxWidth: .infinity).frame(height: 50)
                        .background(selectedTabID == mode.id ? AppTheme.accent : Color.primary.opacity(0.055), in: RoundedRectangle(cornerRadius: 13))
                        .overlay(RoundedRectangle(cornerRadius: 13).stroke(selectedTabID == mode.id ? AppTheme.accent : Color.primary.opacity(0.09)))
                }.buttonStyle(.plain)
            }
        }.padding(4).background(AppTheme.elevated.opacity(store.catalog.resolvedCardOpacity), in: RoundedRectangle(cornerRadius: 17)).overlay(RoundedRectangle(cornerRadius: 17).stroke(Color.primary.opacity(0.08)))
    }

    private var itemPanel: some View {
        VStack(spacing: 0) {
            HStack {
                Rectangle().fill(AppTheme.accent).frame(width: 3, height: 26)
                Label("MENU PATCH", systemImage: selectedTab.icon).font(.headline.weight(.black)).tracking(1.2)
                Spacer()
                Text("AUTO").font(.caption2.weight(.black)).tracking(1)
                    .foregroundStyle(AppTheme.accent)
                    .padding(.horizontal, 11).padding(.vertical, 6)
                    .overlay(Capsule().stroke(AppTheme.accent.opacity(0.7), lineWidth: 1))
            }.padding(16)
            Divider().overlay(.white.opacity(0.08))
            if items.isEmpty {
                VStack(spacing: 9) {
                    if store.isPrefetching { ProgressView().tint(CyberPalette.cyan) }
                    else { Image(systemName: selectedTab.icon).font(.title2).foregroundStyle(CyberPalette.violet) }
                    Text(store.isPrefetching ? "Đang kiểm tra package…" : "Chưa có mục \(selectedTab.title)").font(.subheadline.bold())
                    Text(store.isPrefetching ? "Mục chỉ hiện sau khi file và mật khẩu hợp lệ" : "Thêm package hợp lệ từ website quản trị").font(.caption).foregroundStyle(.secondary).multilineTextAlignment(.center)
                }.frame(maxWidth: .infinity).padding(32)
            }
            else {
                ScrollView(.vertical, showsIndicators: true) {
                    LazyVStack(spacing: 0) {
                        ForEach(items) { item in
                            catalogRow(item)
                            if item.id != items.last?.id {
                                Divider().padding(.leading, 72)
                            }
                        }
                    }
                }
                // Show at most four rows. Additional patch entries stay
                // inside this panel and are reached by swiping vertically.
                .frame(height: min(CGFloat(items.count) * 79, 316))
            }
        }.background(AppTheme.elevated.opacity(store.catalog.resolvedCardOpacity), in: RoundedRectangle(cornerRadius: 22)).overlay(RoundedRectangle(cornerRadius: 22).stroke(Color.primary.opacity(0.1)))
    }

    private func catalogRow(_ item: GameCatalogItem) -> some View {
        let hasLink = !(item.fileURL ?? "").trimmingCharacters(in: .whitespacesAndNewlines).isEmpty
        let isOn = store.isOn(itemID: item.id), busy = store.isBusy(itemID: item.id)
        return HStack(spacing: 13) {
            Button {
                previewImageURL = item.imageURL.flatMap(URL.init(string:))
            } label: {
                ZStack {
                    RoundedRectangle(cornerRadius: 13).fill((isOn ? CyberPalette.violet : CyberPalette.blue).opacity(0.12))
                    if let url = item.imageURL.flatMap(URL.init(string:)) {
                        CachedRemoteImage(url: url) {
                            Image(systemName: "photo.fill").foregroundStyle(isOn ? CyberPalette.violet : CyberPalette.blue)
                        }
                        // Preserve the complete uploaded image inside the square:
                        // portrait and landscape assets are scaled down, never cropped.
                        .scaledToFit()
                        .padding(3)
                    } else {
                        Image(systemName: "bolt.fill").foregroundStyle(isOn ? CyberPalette.violet : CyberPalette.blue)
                    }
                    RoundedRectangle(cornerRadius: 13).stroke((isOn ? CyberPalette.violet : CyberPalette.blue).opacity(0.42))
                }
                .frame(width: 52, height: 52)
            }
            .buttonStyle(.plain)
            .disabled(item.imageURL.flatMap(URL.init(string:)) == nil)
            VStack(alignment: .leading, spacing: 4) {
                Text(item.name).font(.subheadline.bold()).foregroundStyle(.primary)
                if let count = store.replacementRuleCount(itemID: item.id) {
                    Text("\(count) quy tắc thay thế").font(.caption2).foregroundStyle(.secondary)
                } else if hasLink && store.isPrefetching {
                    Text("Đang đọc file .3105…").font(.caption2).foregroundStyle(.secondary)
                } else if !hasLink {
                    Text("Chưa gắn file").font(.caption2.bold()).foregroundStyle(.orange)
                } else {
                    Text("Không đọc được số quy tắc").font(.caption2).foregroundStyle(.orange)
                }
            }
            Spacer(minLength: 4)
            if busy { ProgressView().tint(CyberPalette.cyan) }
            else { Toggle("", isOn: Binding(get: { store.isOn(itemID: item.id) }, set: { value in Task { await store.setEnabled(value, item: item) } })).labelsHidden().tint(CyberPalette.blue).disabled(!hasLink) }
        }.padding(.horizontal, 16).padding(.vertical, 13).opacity(hasLink ? 1 : 0.5)
    }

    private func launchButton(_ game: GameCatalogGame) -> some View {
        Button { if let raw = game.launchURL, let url = URL(string: raw) { UIApplication.shared.open(url) } } label: {
            Label("Mở ứng dụng", systemImage: "play.fill").font(.headline.bold()).foregroundStyle(.white).frame(maxWidth: .infinity).frame(height: 58).background(AppTheme.accent, in: RoundedRectangle(cornerRadius: 17))
        }.buttonStyle(CyberPressStyle())
    }
}

private struct PatchImagePreview: View {
    let url: URL?
    let close: () -> Void

    var body: some View {
        NavigationStack {
            ZStack {
                Color.black.ignoresSafeArea()
                if let url {
                    CachedRemoteImage(url: url) {
                        ProgressView().tint(.white)
                    }
                    .scaledToFit()
                    .padding(12)
                }
            }
            .toolbar {
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button("Đóng", action: close).foregroundStyle(.white)
                }
            }
        }
        .preferredColorScheme(.dark)
    }
}

private struct LiquidGlassBackButton: View {
    let action: () -> Void

    var body: some View {
        Button(action: action) {
            Image(systemName: "chevron.left")
                .font(.headline.weight(.bold))
                .foregroundStyle(.primary)
                .frame(width: 48, height: 48)
                .background(.ultraThinMaterial, in: Circle())
                .background(Circle().fill(Color.white.opacity(0.08)))
                .overlay(Circle().stroke(Color.white.opacity(0.44), lineWidth: 0.8))
                .overlay(alignment: .topLeading) {
                    Circle().trim(from: 0.56, to: 0.83)
                        .stroke(Color.white.opacity(0.9), style: StrokeStyle(lineWidth: 2, lineCap: .round))
                        .padding(4)
                }
                .shadow(color: Color.black.opacity(0.18), radius: 14, y: 7)
        }
        .buttonStyle(CyberPressStyle())
        .accessibilityLabel("Quay lại")
    }
}

private struct CyberPressStyle: ButtonStyle {
    func makeBody(configuration: Configuration) -> some View {
        configuration.label.scaleEffect(configuration.isPressed ? 0.965 : 1).opacity(configuration.isPressed ? 0.82 : 1)
            .animation(.spring(response: 0.24, dampingFraction: 0.7), value: configuration.isPressed)
    }
}
