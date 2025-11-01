<?php
// ======================
// File & Directory Manager with Selective Operations
// ======================

$baseDir = __DIR__;
$dir = isset($_GET['dir']) ? realpath($baseDir . '/' . $_GET['dir']) : $baseDir;

// Cegah keluar dari baseDir
if (strpos($dir, $baseDir) !== 0) {
    die("Access denied!");
}

// ========================
// Fungsi List Items
// ========================
function listItems($path)
{
    $items = scandir($path);
    $result = [];
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $fullPath = $path . '/' . $item;
        $result[] = [
            'name' => $item,
            'path' => $fullPath,
            'is_dir' => is_dir($fullPath)
        ];
    }
    return $result;
}

// ========================
// Fungsi Operasi Selektif
// ========================

// Rename file terpilih (nama baru, ekstensi tetap)
function renameSelectedFiles($dir, $files, $newName)
{
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_file($path)) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $newFile = $dir . '/' . $newName . '.' . $ext;
            rename($path, $newFile);
        }
    }
}

// Ubah ekstensi file terpilih (nama tetap)
function changeSelectedFileExtensions($dir, $files, $newExt)
{
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_file($path)) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $newFile = $dir . '/' . $name . '.' . ltrim($newExt, '.');
            rename($path, $newFile);
        }
    }
}

// Rename folder terpilih
function renameSelectedDirs($dir, $dirs, $prefix)
{
    foreach ($dirs as $index => $folder) {
        $path = $dir . '/' . $folder;
        if (is_dir($path)) {
            $newName = $prefix . '_dir_' . ($index + 1);
            rename($path, $dir . '/' . $newName);
        }
    }
}

// ========================
// Handle POST
// ========================
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'rename_files' && isset($_POST['selected_files'])) {
        renameSelectedFiles($dir, $_POST['selected_files'], $_POST['newname']);
    } elseif ($_POST['action'] === 'change_extensions' && isset($_POST['selected_files'])) {
        changeSelectedFileExtensions($dir, $_POST['selected_files'], $_POST['newext']);
    } elseif ($_POST['action'] === 'rename_dirs' && isset($_POST['selected_dirs'])) {
        renameSelectedDirs($dir, $_POST['selected_dirs'], $_POST['prefix']);
    }
    header("Location: ?dir=" . str_replace($baseDir, '', $dir));
    exit;
}

// ========================
// List current items
// ========================
$items = listItems($dir);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>File & Directory Manager</title>
<style>
body { font-family: Arial, sans-serif; background: #f3f3f3; padding: 20px; }
a { text-decoration: none; color: blue; }
table { border-collapse: collapse; width: 100%; background: white; margin-bottom: 15px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background: #ddd; }
h2, h3 { margin-top: 0; }
form { margin: 15px 0; background: #fff; padding: 10px; border: 1px solid #ccc; }
input[type=text] { padding: 5px; margin-right: 5px; }
button { padding: 5px 10px; }
</style>
</head>
<body>

<h2>📁 File & Directory Manager</h2>
<p><b>Current Dir:</b> <?= htmlspecialchars(str_replace($baseDir, '', $dir)) ?: '/' ?></p>
<p><a href="?dir=">🏠 Root</a></p>

<!-- ========================
     Rename File Terpilih
======================== -->
<form method="post">
<h3>🔤 Rename File Terpilih (Nama Baru)</h3>
<input type="hidden" name="action" value="rename_files">
<table>
<tr><th>Pilih</th><th>Nama File</th></tr>
<?php foreach ($items as $item): ?>
<?php if (!$item['is_dir']): ?>
<tr>
<td><input type="checkbox" name="selected_files[]" value="<?= htmlspecialchars($item['name']) ?>"></td>
<td><?= htmlspecialchars($item['name']) ?></td>
</tr>
<?php endif; ?>
<?php endforeach; ?>
</table>
Nama Baru: <input type="text" name="newname" placeholder="contoh: file" required>
<button type="submit">Rename File</button>
</form>

<!-- ========================
     Ubah Ekstensi File Terpilih
======================== -->
<form method="post">
<h3>🔄 Ubah Ekstensi File Terpilih</h3>
<input type="hidden" name="action" value="change_extensions">
<table>
<tr><th>Pilih</th><th>Nama File</th></tr>
<?php foreach ($items as $item): ?>
<?php if (!$item['is_dir']): ?>
<tr>
<td><input type="checkbox" name="selected_files[]" value="<?= htmlspecialchars($item['name']) ?>"></td>
<td><?= htmlspecialchars($item['name']) ?></td>
</tr>
<?php endif; ?>
<?php endforeach; ?>
</table>
Ekstensi Baru: <input type="text" name="newext" placeholder="contoh: pdf" required>
<button type="submit">Change Extensions</button>
</form>

<!-- ========================
     Rename Folder Terpilih
======================== -->
<form method="post">
<h3>📂 Rename Folder Terpilih</h3>
<input type="hidden" name="action" value="rename_dirs">
<table>
<tr><th>Pilih</th><th>Nama Folder</th></tr>
<?php foreach ($items as $item): ?>
<?php if ($item['is_dir']): ?>
<tr>
<td><input type="checkbox" name="selected_dirs[]" value="<?= htmlspecialchars($item['name']) ?>"></td>
<td><?= htmlspecialchars($item['name']) ?></td>
</tr>
<?php endif; ?>
<?php endforeach; ?>
</table>
Prefix: <input type="text" name="prefix" placeholder="contoh: folder" required>
<button type="submit">Rename Folders</button>
</form>

</body>
</html>
