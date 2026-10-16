<?php
$target = '/home/novasgn/domains/vortexsign.web.id/signage/storage/app/public';
$shortcut = '/home/novasgn/domains/vortexsign.web.id/public_html/storage';

if (file_exists($shortcut)) {
    echo "Symlink storage sudah ada.";
} else {
    symlink($target, $shortcut);
    echo "✅ Symlink storage berhasil dibuat!";
}
?>