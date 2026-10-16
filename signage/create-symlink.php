<?php
// File ini harus diakses via CLI atau browser
$target = '/home/novasgn/domains/vortexsign.web.id/signage/storage/app/public';
$shortcut = '/home/novasgn/domains/vortexsign.web.id/public_html/storage';

echo "Creating symlink...\n";
echo "Target: $target\n";
echo "Shortcut: $shortcut\n\n";

// Hapus yang lama jika ada
if (is_link($shortcut)) {
    unlink($shortcut);
    echo "Old symlink removed.\n";
} elseif (is_dir($shortcut)) {
    // Backup folder jika ada isi
    rename($shortcut, $shortcut . '_backup_' . time());
    echo "Old folder backed up.\n";
}

// Buat symlink baru
$result = @symlink($target, $shortcut);

if ($result) {
    echo "✅ Symlink created successfully!\n";
    echo "You can now access files via: https://vortexsign.web.id/storage/...\n";
} else {
    echo "❌ Failed to create symlink.\n";
    echo "Error: " . print_r(error_get_last(), true) . "\n";
    echo "\nAlternative: Copy files manually to /public_html/storage/\n";
}
?>