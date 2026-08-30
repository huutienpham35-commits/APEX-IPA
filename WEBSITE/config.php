<?php
declare(strict_types=1);

// Public live config for the app (no browser/CDN cache).
// Point Info.plist GameCatalogURL here, e.g. https://huutien.store/APEX_IPA/config.php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');

$path = __DIR__ . DIRECTORY_SEPARATOR . 'config.json';
require_once __DIR__ . '/database.php';
if (($_GET['client'] ?? '') === 'app') apexRecordAppRequest();
$payload = apexReadConfig($path);
if ($payload === null) {
    http_response_code(apexDatabaseConfigured() ? 503 : 404);
    echo json_encode([
        'noticeTitle' => 'Thông báo',
        'noticeMessage' => apexDatabaseConfigured()
            ? 'Máy chủ dữ liệu đang tạm thời mất kết nối.'
            : 'Thiếu config.json trên server.',
        'games' => [],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo $payload;
