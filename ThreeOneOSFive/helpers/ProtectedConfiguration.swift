import Foundation

/// Centralized runtime decoding for values that should not appear as readable
/// plaintext in the compiled binary. Each byte uses a position-dependent mask,
/// which also avoids storing a recognizable plain hex/ASCII sequence.
enum ProtectedConfiguration {
    private static func decode(_ bytes: [UInt8], seed: UInt8) -> String {
        let decoded = bytes.enumerated().map { index, byte in
            byte ^ (seed &+ UInt8(truncatingIfNeeded: index &* 17))
        }
        return String(bytes: decoded, encoding: .utf8) ?? ""
    }

    private static func verified(
        _ bytes: [UInt8],
        seed: UInt8,
        checksum: UInt64
    ) -> String {
        let value = decode(bytes, seed: seed)
        var hash: UInt64 = 0xcbf29ce484222325
        for byte in value.utf8 {
            hash ^= UInt64(byte)
            hash = hash &* 0x100000001b3
        }
        return hash == checksum ? value : ""
    }

    static var packageToken: String {
        verified([
            73, 33, 60, 51, 54, 221, 206, 197, 236, 148, 212, 129, 68, 83,
            68, 127, 5, 104, 51, 42, 233, 168, 157, 132, 230, 182, 194, 108,
            97, 71, 121, 41, 44, 12, 55, 227
        ], seed: 0x39, checksum: 0xACEA8855C15F0A17)
    }

    static var catalogURL: URL? {
        URL(string: verified([
            207, 204, 189, 170, 152, 198, 34, 49, 70, 48, 48, 76, 18, 244,
            240, 222, 199, 186, 182, 146, 130, 34, 110, 90, 80, 34, 4, 93,
            224, 251, 203, 208, 174, 191, 199, 138, 99, 108
        ], seed: 0xA7, checksum: 0x9ADF2476F4189794))
    }

    static var updateAPIURL: URL {
        URL(string: verified([
            53, 26, 11, 224, 210, 136, 236, 251, 132, 134, 110, 54, 78, 83,
            63, 52, 24, 28, 161, 195, 222, 175, 252, 150, 144, 118, 120, 91,
            22, 19, 58, 2, 26, 196, 246, 217, 168, 187, 204, 199, 52, 38, 18,
            23, 59, 63, 7, 25, 236, 237, 202, 179, 254, 142, 146, 112, 112, 85, 67
        ], seed: 0x5D, checksum: 0x411FC465623DC1A9))!
    }

    static var updateFallbackURL: URL {
        URL(string: verified([
            171, 160, 145, 134, 116, 34, 6, 21, 44, 53, 25, 22, 250, 194,
            159, 161, 188, 137, 218, 95, 118, 70, 94, 0, 50, 5, 20, 231, 176,
            131, 240, 226, 214, 219, 119, 115, 75, 93, 40, 41, 14, 15, 162,
            242, 206, 180, 180, 145, 135
        ], seed: 0xC3, checksum: 0xEAF0544AC60ADCAD))!
    }
}
