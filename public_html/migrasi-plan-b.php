<?php
/**
 * migrasi-plan-b.php - menjalankan perubahan database Plan B pada database yang DIPAKAI APLIKASI (.env).
 * Aman diulang (idempotent): hanya menambah yang belum ada.
 * Pakai: https://staging.vortexsign.web.id/migrasi-plan-b.php?key=KUNCI-ANDA&superadmin=USERNAME-ANDA
 * HAPUS FILE INI setelah selesai.
 */
$KUNCI = 'coba';
header('Content-Type: text/html; charset=utf-8');
if (!isset($_GET['key']) || !hash_equals($KUNCI, (string) $_GET['key'])) { http_response_code(403); exit('Akses ditolak.'); }

$base = dirname(__DIR__) . '/signage';
$env = [];
foreach (@file($base . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $l) {
    if ($l[0] === '#' || strpos($l, '=') === false) continue;
    [$k, $v] = explode('=', $l, 2); $env[trim($k)] = trim($v, " \t\"'");
}
$db = $env['DB_DATABASE'] ?? '';
echo "<h3>Database yang dipakai aplikasi ini: <code>" . htmlspecialchars($db) . "</code></h3>";

// Pengaman: hanya jalan di database yang namanya mengandung "staging"
if ($db === '' || (stripos($db, 'staging') === false && !isset($_GET['force']))) {
    exit("<p style='color:#c62828'><b>DIBATALKAN.</b> Nama database tidak mengandung kata 'staging', jadi kemungkinan ini database PRODUKSI. "
       . "Perbaiki <code>DB_DATABASE</code> di .env staging agar menunjuk ke database staging. "
       . "(Hanya jika Anda yakin, tambahkan <code>&amp;force=1</code>.)</p>");
}

$pdo = new PDO(sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $env['DB_HOST'] ?? 'localhost', $env['DB_PORT'] ?? '3306', $db),
    $env['DB_USERNAME'] ?? '', $env['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$log = [];
function say($t, $ok = true) { global $log; $log[] = [$t, $ok]; }
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

try {
    // 1) Tabel clients + client default
    $pdo->exec("CREATE TABLE IF NOT EXISTS clients (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(255) NOT NULL, kode VARCHAR(50) NOT NULL,
        aktif TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP NULL DEFAULT NULL, updated_at TIMESTAMP NULL DEFAULT NULL,
        UNIQUE KEY clients_kode_unique (kode)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("INSERT IGNORE INTO clients (id,nama,kode,aktif,created_at,updated_at) VALUES (1,'Vortex Sign (Default)','default',1,NOW(),NOW())");
    say('Tabel clients + client default (id 1)');

    // 2) Kolom client_id + isi data lama
    foreach (['users', 'toko', 'digital_signage', 'file_iklan', 'jadwal_tayang', 'distribusi_jadwal'] as $t) {
        if (!in_array($t, $tables, true)) { say("Tabel $t tidak ditemukan", false); continue; }
        $has = $pdo->query("SHOW COLUMNS FROM `$t` LIKE 'client_id'")->fetch();
        if (!$has) {
            $pdo->exec("ALTER TABLE `$t` ADD COLUMN client_id BIGINT UNSIGNED NULL AFTER id, ADD INDEX {$t}_client_id_index (client_id)");
            say("Kolom $t.client_id DITAMBAHKAN");
        } else { say("Kolom $t.client_id sudah ada"); }
        $n = $pdo->exec("UPDATE `$t` SET client_id = 1 WHERE client_id IS NULL");
        say("$t: $n baris diisi client 1");
    }

    // 3) Role superadmin
    $col = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
    if ($col && stripos($col['Type'], 'enum(') === 0 && stripos($col['Type'], "'superadmin'") === false) {
        $pdo->exec("ALTER TABLE users MODIFY role " . substr($col['Type'], 0, -1) . ",'superadmin') NOT NULL DEFAULT 'admin'");
        say("Kolom users.role (ENUM) ditambah nilai 'superadmin'");
    } else { say('Kolom users.role: ' . ($col['Type'] ?? 'tidak ada') . ' (tidak perlu diubah)'); }
    if (!empty($_GET['superadmin'])) {
        $st = $pdo->prepare("UPDATE users SET role='superadmin', client_id=1 WHERE username = ?");
        $st->execute([$_GET['superadmin']]);
        say("User '" . htmlspecialchars($_GET['superadmin']) . "' dijadikan superadmin: " . $st->rowCount() . ' baris', $st->rowCount() > 0);
    }

    // 4) Catat di migrations
    if (in_array('migrations', $tables, true)) {
        $nm = '2026_10_12_000001_add_clients_multi_tenant';
        $ada = $pdo->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?'); $ada->execute([$nm]);
        if (!$ada->fetchColumn()) {
            $pdo->exec("INSERT INTO migrations (migration, batch) SELECT '$nm', COALESCE(MAX(batch),0)+1 FROM (SELECT batch FROM migrations) m");
            say('Dicatat di tabel migrations');
        }
    }
} catch (Throwable $e) { say('ERROR: ' . $e->getMessage(), false); }

// 5) Verifikasi akhir
echo '<h4>Hasil</h4><ul>';
foreach ($log as [$t, $ok]) echo '<li style="color:' . ($ok ? '#1b8a3a' : '#c62828') . '">' . htmlspecialchars($t) . '</li>';
echo '</ul><h4>Verifikasi: tabel yang sudah punya client_id (harus 6)</h4><ul>';
foreach ($pdo->query("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME='client_id'")->fetchAll(PDO::FETCH_COLUMN) as $t) echo '<li>' . htmlspecialchars($t) . '</li>';
echo '</ul><p>Users:</p><pre>';
foreach ($pdo->query('SELECT id, username, role, client_id FROM users')->fetchAll(PDO::FETCH_ASSOC) as $u) echo htmlspecialchars(json_encode($u)) . "\n";
echo '</pre><p style="color:#c62828"><b>Hapus file ini dari public_html setelah selesai.</b></p>';
