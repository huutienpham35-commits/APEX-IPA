import Foundation

/// Centralized runtime decoding for values that should not appear as readable
/// plaintext in the compiled binary. Each byte uses a position-dependent mask,
/// which also avoids storing a recognizable plain hex/ASCII sequence.
enum ProtectedConfiguration {
    /// Decodes a value protected with a random per-byte mask and a second
    /// position-dependent transform. This avoids embedding the token as a
    /// plaintext string or as a single trivially XORed byte sequence.
    private static func decodeMasked(
        cipher: [UInt8],
        mask: [UInt8],
        checksum: UInt64
    ) -> String {
        guard cipher.count == mask.count else { return "" }
        let bytes = zip(cipher, mask).enumerated().map { index, pair -> UInt8 in
            let position = UInt8(truncatingIfNeeded: (index &* 29) &+ 0x53)
            return (pair.0 &- position) ^ pair.1
        }
        guard let value = String(bytes: bytes, encoding: .utf8) else { return "" }
        var hash: UInt64 = 0xcbf29ce484222325
        for byte in value.utf8 {
            hash ^= UInt64(byte)
            hash = hash &* 0x100000001b3
        }
        return hash == checksum ? value : ""
    }

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
        decodeMasked(
            cipher: [
                54, 75, 87, 178, 192, 120, 169, 30, 197, 22, 110, 211, 5, 214,
                27, 78, 159, 80, 226, 63, 85, 219, 32, 177, 144, 37, 166, 197,
                0, 206, 122, 58, 123, 127, 171, 133
            ],
            mask: [
                147, 176, 173, 87, 138, 162, 219, 68, 228, 252, 161, 22, 18,
                97, 99, 59, 42, 93, 234, 146, 143, 110, 1, 167, 226, 183, 82,
                22, 219, 115, 171, 52, 203, 22, 39, 78
            ],
            checksum: 0x4E392DB80CFDA5CF
        )
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
