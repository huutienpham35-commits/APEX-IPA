<?php
declare(strict_types=1);

$GLOBALS['apex_database_error'] = null;

function apexDatabaseConfigured(): bool {
    return is_file(__DIR__ . '/database.config.php');
}
/** Local PHP development server uses config.json because hosting MySQL's
 * "localhost" address is only reachable from the hosting machine itself. */
function apexJsonFallbackAllowed(): bool {
    if (PHP_SAPI === 'cli-server') return true;
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    $host = explode(':', $host, 2)[0];
    return in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
}

function apexDatabaseError(): ?string {
    $error = $GLOBALS['apex_database_error'] ?? null;
    return is_string($error) && $error !== '' ? $error : null;
}

function apexDatabase(): ?PDO {
    static $connection = false;
    if ($connection instanceof PDO) return $connection;
    $configPath = __DIR__ . '/database.config.php';
    if (!is_file($configPath)) return null;
    if (!class_exists('PDO') || !in_array('mysql', PDO::getAvailableDrivers(), true)) {
        $GLOBALS['apex_database_error'] = 'Hosting chưa bật extension pdo_mysql.';
        return null;
    }
    $config = require $configPath;
    if (!is_array($config)) {
        $GLOBALS['apex_database_error'] = 'database.config.php không hợp lệ.';
        return null;
    }
    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'] ?? 'localhost',
            (int)($config['port'] ?? 3306),
            $config['database'] ?? ''
        );
        $connection = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $connection->exec('CREATE TABLE IF NOT EXISTS app_config (id TINYINT UNSIGNED NOT NULL PRIMARY KEY, payload LONGTEXT NOT NULL, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        return $connection;
    } catch (Throwable $error) {
        $GLOBALS['apex_database_error'] = 'Không kết nối được MySQL: ' . $error->getMessage();
        $connection = false;
        return null;
    }
}

function apexReadConfig(string $jsonFallback): ?string {
    $db = apexDatabase();
    if ($db) {
        $value = $db->query('SELECT payload FROM app_config WHERE id = 1')->fetchColumn();
        if (is_string($value)) return $value;
        if (is_file($jsonFallback)) {
            $payload = file_get_contents($jsonFallback) ?: '{}';
            $stmt = $db->prepare('INSERT INTO app_config(id,payload) VALUES(1,:payload)');
            $stmt->execute([':payload' => $payload]);
            return $payload;
        }
    }
    // Once MySQL is configured, never silently show the bundled config.json.
    // Doing so makes a connection problem look like all admin data was reset.
    if (apexDatabaseConfigured() && !apexJsonFallbackAllowed()) return null;
    return is_file($jsonFallback) ? file_get_contents($jsonFallback) ?: null : null;
}

function apexWriteConfig(string $jsonFallback, string $payload): bool {
    $db = apexDatabase();
    if ($db) {
        $stmt = $db->prepare('INSERT INTO app_config(id,payload) VALUES(1,:payload) ON DUPLICATE KEY UPDATE payload=VALUES(payload),updated_at=CURRENT_TIMESTAMP');
        $ok = $stmt->execute([':payload' => $payload]);
        if ($ok) @file_put_contents($jsonFallback, $payload . PHP_EOL, LOCK_EX);
        return $ok;
    }
    // Do not overwrite the JSON backup when a configured database is offline.
    if (apexDatabaseConfigured() && !apexJsonFallbackAllowed()) return false;
    return file_put_contents($jsonFallback, $payload . PHP_EOL, LOCK_EX) !== false;
}

function apexRecordAppRequest(): void {
    $db = apexDatabase();
    if ($db) {
        $db->exec('CREATE TABLE IF NOT EXISTS app_request_stats (stat_date DATE NOT NULL PRIMARY KEY, request_count BIGINT UNSIGNED NOT NULL DEFAULT 0, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $stmt = $db->prepare('INSERT INTO app_request_stats(stat_date,request_count) VALUES(CURRENT_DATE,1) ON DUPLICATE KEY UPDATE request_count=request_count+1,updated_at=CURRENT_TIMESTAMP');
        $stmt->execute();
        return;
    }
    if (!apexJsonFallbackAllowed()) return;
    $path = __DIR__ . '/request-stats.json';
    $stats = is_file($path) ? json_decode((string)file_get_contents($path), true) : [];
    if (!is_array($stats)) $stats = [];
    $day = date('Y-m-d');
    $stats[$day] = (int)($stats[$day] ?? 0) + 1;
    file_put_contents($path, json_encode($stats, JSON_PRETTY_PRINT), LOCK_EX);
}

function apexRequestStats(): array {
    $db = apexDatabase();
    if ($db) {
        $db->exec('CREATE TABLE IF NOT EXISTS app_request_stats (stat_date DATE NOT NULL PRIMARY KEY, request_count BIGINT UNSIGNED NOT NULL DEFAULT 0, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $row = $db->query("SELECT COALESCE(SUM(request_count),0) total, COALESCE(SUM(CASE WHEN stat_date=CURRENT_DATE THEN request_count ELSE 0 END),0) today, COALESCE(SUM(CASE WHEN stat_date>=CURRENT_DATE-INTERVAL 6 DAY THEN request_count ELSE 0 END),0) last7 FROM app_request_stats")->fetch();
        return ['total'=>(int)$row['total'], 'today'=>(int)$row['today'], 'last7'=>(int)$row['last7']];
    }
    $path = __DIR__ . '/request-stats.json';
    $stats = is_file($path) ? json_decode((string)file_get_contents($path), true) : [];
    if (!is_array($stats)) $stats = [];
    $today = date('Y-m-d');
    $cutoff = date('Y-m-d', strtotime('-6 days'));
    $total = $last7 = 0;
    foreach ($stats as $day => $count) {
        $total += (int)$count;
        if ($day >= $cutoff) $last7 += (int)$count;
    }
    return ['total'=>$total, 'today'=>(int)($stats[$today] ?? 0), 'last7'=>$last7];
}
