<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Configure this secret on the host; never commit the real password.
$adminPassword = trim((string)getenv('APEX_ADMIN_PASSWORD'));
$configFile = __DIR__ . '/config.json';
$uploadDir = __DIR__ . '/uploads';
require_once __DIR__ . '/database.php';

function fail(string $message, int $status = 400): never {
    http_response_code($status);
    echo json_encode(['ok' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? 'read';

// Public read kept for older clients; app should use config.php (no-cache).
if ($action === 'read') {
    $payload = apexReadConfig($configFile);
    if ($payload === null) fail('Không tìm thấy cấu hình', 404);
    echo $payload;
    exit;
}

$password = $_SERVER['HTTP_X_ADMIN_PASSWORD'] ?? ($_POST['password'] ?? '');
if ($adminPassword === '') fail('Máy chủ chưa cấu hình APEX_ADMIN_PASSWORD', 503);
if (!hash_equals($adminPassword, (string)$password)) fail('Sai mật khẩu', 401);

// Admin login check (no side effects).
if ($action === 'verify') {
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'database_status') {
    $db = apexDatabase();
    echo json_encode([
        'ok' => $db instanceof PDO,
        'configured' => apexDatabaseConfigured(),
        'driver' => 'mysql',
        'message' => $db instanceof PDO ? 'MySQL đang kết nối' : (apexDatabaseError() ?? 'Chưa có database.config.php'),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'admin_stats') {
    echo json_encode(['ok' => true, 'requests' => apexRequestStats()], JSON_UNESCAPED_UNICODE);
    exit;
}

// Authenticated config load for admin panel.
if ($action === 'admin_read') {
    $payload = apexReadConfig($configFile);
    if ($payload === null) fail(apexDatabaseError() ?? 'Không đọc được cấu hình', 500);
    echo $payload;
    exit;
}

if ($action === 'save') {
    $raw = file_get_contents('php://input');
    if (isset($_POST['payload_base64'])) {
        $decoded = base64_decode((string)$_POST['payload_base64'], true);
        if ($decoded === false) fail('Dữ liệu mã hóa không hợp lệ');
        $raw = $decoded;
    }
    $data = json_decode($raw ?: '', true);
    if (!is_array($data) || !isset($data['noticeTitle'], $data['noticeMessage'], $data['games']) || !is_array($data['games'])) {
        fail('JSON không hợp lệ');
    }
    $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!apexWriteConfig($configFile, $encoded)) fail(apexDatabaseError() ?? 'Không thể ghi dữ liệu MySQL', 500);
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'upload') {
    if (!isset($_FILES['file'])) {
        $postMax = ini_get('post_max_size') ?: 'không xác định';
        fail('Server không nhận được file. Hãy tăng post_max_size (hiện tại: ' . $postMax . ').');
    }
    $uploadError = (int)($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'File vượt upload_max_filesize của PHP (' . (ini_get('upload_max_filesize') ?: 'không xác định') . ').',
            UPLOAD_ERR_FORM_SIZE => 'File vượt giới hạn của biểu mẫu.',
            UPLOAD_ERR_PARTIAL => 'File chỉ được tải lên một phần. Vui lòng thử lại.',
            UPLOAD_ERR_NO_FILE => 'Chưa chọn file để tải lên.',
            UPLOAD_ERR_NO_TMP_DIR => 'Hosting thiếu thư mục tạm để nhận upload.',
            UPLOAD_ERR_CANT_WRITE => 'Hosting không thể ghi file vào ổ đĩa.',
            UPLOAD_ERR_EXTENSION => 'Extension PHP đã chặn file upload.',
        ];
        fail($messages[$uploadError] ?? ('Tải file thất bại, mã lỗi PHP: ' . $uploadError));
    }
    // Images / short videos / audio / .3105 packages
    if ($_FILES['file']['size'] > 500 * 1024 * 1024) fail('File vượt quá 500 MB');
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) fail('Không thể tạo thư mục uploads', 500);
    $orig = basename((string)$_FILES['file']['name']);
    $name = preg_replace('/[^A-Za-z0-9._\-\[\]]/', '_', $orig);
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = [
        '3105', 'bin', 'ipa',
        'mp3', 'm4a', 'aac', 'wav', 'ogg', 'caf',
        'jpg', 'jpeg', 'jfif', 'png', 'webp', 'gif', 'heic', 'heif', 'ico',
        'bmp', 'tif', 'tiff', 'avif', 'jxl',
        'mp4', 'mov', 'm4v', '3gp', '3g2', 'mpg', 'mpeg', 'ts',
    ];
    if ($ext === '' || !in_array($ext, $allowed, true)) {
        fail('Định dạng không hỗ trợ (nhạc/ảnh/video/.3105/.ipa)');
    }
    if ($ext === 'ipa' && preg_match('/(\d+\.\d+\.\d+)/', $name, $versionMatch)) {
        // Normalize both APEX-IPA-1.0.1.ipa and APEX-IPA-[1.0.1].ipa
        // to the required downloadable filename with square brackets.
        $name = 'APEX-IPA-[' . $versionMatch[1] . '].ipa';
    } else {
        $name = time() . '-' . ($name ?: ('file.' . $ext));
    }
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . '/' . $name)) fail('Không thể lưu file', 500);

    // Absolute URL so the iOS app loads wallpaper/music without relative-path bugs
    // (relative /APEX_IPA/uploads/... was a common cause of black backgrounds).
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || ((string)($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = (string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost');
    $dir = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '')));
    $dir = rtrim($dir, '/');
    if ($dir === '.') { $dir = ''; }
    $path = ($dir === '' ? '' : $dir) . '/uploads/' . $name;
    $absolute = $scheme . '://' . $host . $path;

    echo json_encode([
        'ok' => true,
        'url' => $absolute,
        'path' => $path,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

fail('Thao tác không tồn tại', 404);
