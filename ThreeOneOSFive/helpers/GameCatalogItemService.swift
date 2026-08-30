import Foundation

/// Downloads remote `.3105` packages for catalog items and applies / restores them
/// through the same path as the Patches tab.
enum GameCatalogItemService {
    private static let packagesFolderName = "CatalogPackages"
    private static let stateFileName = "catalog-item-state.json"
    private static let appliedKey = "appliedPackageIDs"

    // MARK: - Local state (which items are currently applied)

    static func loadAppliedItemIDs() -> Set<String> {
        guard let url = try? stateURL(),
              let data = try? Data(contentsOf: url),
              let object = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
              let ids = object[appliedKey] as? [String]
        else {
            return []
        }
        return Set(ids)
    }

    static func isApplied(itemID: String) -> Bool {
        loadAppliedItemIDs().contains(itemID)
    }

    private static func setApplied(_ applied: Bool, itemID: String) throws {
        var ids = loadAppliedItemIDs()
        if applied {
            ids.insert(itemID)
        } else {
            ids.remove(itemID)
        }
        let payload: [String: Any] = [appliedKey: Array(ids).sorted()]
        let data = try JSONSerialization.data(withJSONObject: payload, options: [.prettyPrinted, .sortedKeys])
        let url = try stateURL()
        try FileManager.default.createDirectory(
            at: url.deletingLastPathComponent(),
            withIntermediateDirectories: true
        )
        try data.write(to: url, options: .atomic)
    }

    // MARK: - Cache / download

    /// Prefetch every item that has a `fileURL` so toggles work offline after first open.
    static func prefetchAll(from catalog: GameCatalog) async {
        let urls: [(String, URL)] = catalog.games.flatMap { game in
            game.items.compactMap { item in
                guard let raw = item.fileURL?.trimmingCharacters(in: .whitespacesAndNewlines),
                      !raw.isEmpty,
                      let url = URL(string: raw)
                else { return nil }
                return (item.id, url)
            }
        }
        await withTaskGroup(of: Void.self) { group in
            for (itemID, remoteURL) in urls {
                group.addTask {
                    _ = try? await ensureLocalPackage(itemID: itemID, remoteURL: remoteURL)
                }
            }
        }
    }

    /// Reads package metadata without applying anything and returns replacement-rule counts.
    @MainActor
    static func replacementRuleCounts(
        from catalog: GameCatalog,
        progress: ((Int, Int) -> Void)? = nil
    ) async -> [String: Int] {
        let items = catalog.games.flatMap(\.items)
        return await withTaskGroup(of: (String, Int)?.self, returning: [String: Int].self) { group in
            for item in items {
                group.addTask {
                    guard let raw = item.fileURL?.trimmingCharacters(in: .whitespacesAndNewlines),
                          !raw.isEmpty,
                          let remoteURL = URL(string: raw),
                          let localURL = try? await ensureLocalPackage(itemID: item.id, remoteURL: remoteURL),
                          let data = try? Data(contentsOf: localURL),
                          let decoded = try? PatchPackageCodec.decode(data, password: item.resolvedPackagePassword)
                    else { return nil }
                    return (item.id, decoded.project.rules.count)
                }
            }
            var result: [String: Int] = [:]
            var completed = 0
            for await entry in group {
                if let entry { result[entry.0] = entry.1 }
                completed += 1
                progress?(completed, items.count)
            }
            return result
        }
    }

    static func ensureLocalPackage(itemID: String, remoteURL: URL) async throws -> URL {
        let localURL = try localPackageURL(itemID: itemID)
        if FileManager.default.fileExists(atPath: localURL.path),
           (try? localURL.resourceValues(forKeys: [.fileSizeKey]).fileSize) ?? 0 > 64 {
            return localURL
        }
        let (temporaryURL, response) = try await URLSession.shared.download(from: remoteURL)
        defer { try? FileManager.default.removeItem(at: temporaryURL) }
        if let http = response as? HTTPURLResponse, !(200...299).contains(http.statusCode) {
            throw PatchPackageError.remoteImportFailed
        }
        try FileManager.default.createDirectory(
            at: localURL.deletingLastPathComponent(),
            withIntermediateDirectories: true
        )
        if FileManager.default.fileExists(atPath: localURL.path) {
            try FileManager.default.removeItem(at: localURL)
        }
        try FileManager.default.copyItem(at: temporaryURL, to: localURL)
        return localURL
    }

    // MARK: - Apply / restore (same as Patches)

    /// ON: download (if needed) → decode `.3105` → DevicePatchService.apply
    static func enable(item: GameCatalogItem) async throws {
        guard let raw = item.fileURL?.trimmingCharacters(in: .whitespacesAndNewlines),
              !raw.isEmpty,
              let remoteURL = URL(string: raw)
        else {
            throw PatchPackageError.invalidImportLink
        }

        let localURL = try await ensureLocalPackage(itemID: item.id, remoteURL: remoteURL)
        let password = item.resolvedPackagePassword
        let packageID = try await Task.detached(priority: .userInitiated) {
            let data = try Data(contentsOf: localURL)
            let summary = try PatchPackageCodec.inspect(data)
            if summary.isPasswordProtected {
                guard let password, !password.isEmpty else {
                    throw PatchPackageError.invalidPasswordOrCorruptedPackage
                }
            }
            // Unlock with website-provided password when package is protected.
            let decoded = try PatchPackageCodec.decode(data, password: password)
            // Same path as Patches → "Áp dụng": backup originals + write package files in.
            _ = try DevicePatchService.apply(project: decoded.project)
            return decoded.project.id
        }.value
        try setApplied(true, itemID: item.id)
        try rememberPackageID(itemID: item.id, packageID: packageID)
    }

    /// OFF: DevicePatchService.restore like Patches → "Khôi phục"
    static func disable(item: GameCatalogItem) async throws {
        let packageID: UUID? = {
            if let remembered = try? rememberedPackageID(itemID: item.id) {
                return remembered
            }
            if let local = try? localPackageURL(itemID: item.id),
               FileManager.default.fileExists(atPath: local.path),
               let data = try? Data(contentsOf: local),
               let summary = try? PatchPackageCodec.inspect(data) {
                return summary.packageID
            }
            return nil
        }()

        guard let packageID else {
            try setApplied(false, itemID: item.id)
            return
        }

        try await Task.detached(priority: .userInitiated) {
            guard let receipt = DevicePatchService.latestReceipt(projectID: packageID) else {
                // Do not pretend the item was restored when its verified backup is
                // unavailable. Keeping the switch on makes the real state visible.
                throw PatchPackageError.restoreFailed
            }
            try DevicePatchService.restore(receipt: receipt)
        }.value
        try setApplied(false, itemID: item.id)
    }

    // MARK: - Paths / bookkeeping

    private static func packagesRootURL() throws -> URL {
        let base = try FileManager.default.url(
            for: .applicationSupportDirectory,
            in: .userDomainMask,
            appropriateFor: nil,
            create: true
        )
        let root = base
            .appendingPathComponent("APEX", isDirectory: true)
            .appendingPathComponent(packagesFolderName, isDirectory: true)
        try FileManager.default.createDirectory(at: root, withIntermediateDirectories: true)
        return root
    }

    private static func localPackageURL(itemID: String) throws -> URL {
        let safe = itemID
            .replacingOccurrences(of: "/", with: "_")
            .replacingOccurrences(of: ":", with: "_")
        return try packagesRootURL().appendingPathComponent("\(safe).3105", isDirectory: false)
    }

    private static func stateURL() throws -> URL {
        try packagesRootURL().appendingPathComponent(stateFileName, isDirectory: false)
    }

    private static let mapFileName = "item-package-map.json"

    private static func mapURL() throws -> URL {
        try packagesRootURL().appendingPathComponent(mapFileName, isDirectory: false)
    }

    private static func rememberPackageID(itemID: String, packageID: UUID) throws {
        var map = loadPackageMap()
        map[itemID] = packageID.uuidString
        let data = try JSONSerialization.data(withJSONObject: map, options: [.prettyPrinted, .sortedKeys])
        try data.write(to: mapURL(), options: .atomic)
    }

    private static func rememberedPackageID(itemID: String) throws -> UUID? {
        guard let raw = loadPackageMap()[itemID] else { return nil }
        return UUID(uuidString: raw)
    }

    private static func loadPackageMap() -> [String: String] {
        guard let url = try? mapURL(),
              let data = try? Data(contentsOf: url),
              let object = try? JSONSerialization.jsonObject(with: data) as? [String: String]
        else {
            return [:]
        }
        return object
    }
}
