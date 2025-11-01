<?php
// ======================
// Simple File & Directory Manager
// ======================

$baseDir = __DIR__;
$dir = isset($_GET['dir']) ? realpath($baseDir . '/' . $_GET['dir']) : $baseDir;

// Cegah keluar dari baseDir
if (strpos($dir, $baseDir) !== 0) {
    die("Access denied!");
}

// Fungsi list file & folder
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
// Fitur Rename & Change Extension
// ========================

// 1️⃣ Rename semua file (nama baru, ekstensi tetap)
function renameAllFiles($dir, $newName)
{
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        $path = $dir . '/' . $file;
        if (is_file($path)) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $newFile = $dir . '/' . $newName . '.' . $ext;
            rename($path, $newFile); // langsung overwrite
        }
    }
}

// 2️⃣ Ubah ekstensi semua file (nama tetap)
function changeAllFileExtensions($dir, $newExt)
{
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        $path = $dir . '/' . $file;
        if (is_file($path)) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $newFile = $dir . '/' . $name . '.' . ltrim($newExt, '.');
            rename($path, $newFile); // langsung overwrite
        }
    }
}

// 3️⃣ Rename semua direktori dengan prefix baru
function renameAllDirs($dir, $prefix)
{
    $items = scandir($dir);
    $count = 1;
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $newName = $prefix . '_dir_' . $count;
            rename($path, $dir . '/' . $newName);
            $count++;
        }
    }
}

// ========================
// Handle POST action
// ========================
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'rename_files') {
        renameAllFiles($dir, $_POST['newname']);
    } elseif ($_POST['action'] === 'change_extensions') {
        changeAllFileExtensions($dir, $_POST['newext']);
    } elseif ($_POST['action'] === 'rename_dirs') {
        renameAllDirs($dir, $_POST['prefix']);
    }
    header("Location: ?dir=" . str_replace($baseDir, '', $dir));
    exit;
}

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
table { border-collapse: collapse; width: 100%; background: white; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background: #ddd; }
h2 { margin-top: 0; }
form { margin: 15px 0; background: #fff; padding: 10px; border: 1px solid #ccc; }
input[type=text] { padding: 5px; margin-right: 5px; }
button { padding: 5px 10px; }
</style>
</head>
<body>

<h2>📁 File & Directory Manager</h2>
<p><b>Current Dir:</b> <?= htmlspecialchars(str_replace($baseDir, '', $dir)) ?: '/' ?></p>
<p><a href="?dir=">🏠 Root</a></p>

<table>
<tr><th>Nama</th><th>Tipe</th></tr>
<?php foreach ($items as $item): ?>
<tr>
<td>
    <?php if ($item['is_dir']): ?>
        <a href="?dir=<?= urlencode(str_replace($baseDir.'/', '', $item['path'])) ?>">📂 <?= htmlspecialchars($item['name']) ?></a>
    <?php else: ?>
        📄 <?= htmlspecialchars($item['name']) ?>
    <?php endif; ?>
</td>
<td><?= $item['is_dir'] ? 'Folder' : 'File' ?></td>
</tr>
<?php endforeach; ?>
</table>

<hr>

<!-- 1️⃣ Rename semua file -->
<form method="post">
    <h3>🔤 Rename Semua File (Nama Baru)</h3>
    <input type="hidden" name="action" value="rename_files">
    Nama Baru: <input type="text" name="newname" placeholder="contoh: file" required>
    <button type="submit">Rename Files</button>
</form>

<!-- 2️⃣ Ubah ekstensi semua file -->
<form method="post">
    <h3>🔄 Ubah Ekstensi Semua File</h3>
    <input type="hidden" name="action" value="change_extensions">
    Ekstensi Baru: <input type="text" name="newext" placeholder="contoh: pdf" required>
    <button type="submit">Change Extensions</button>
</form>

<!-- 3️⃣ Rename semua folder -->
<form method="post">
    <h3>📂 Rename Semua Folder</h3>
    <input type="hidden" name="action" value="rename_dirs">
    Prefix: <input type="text" name="prefix" placeholder="contoh: folder" required>
    <button type="submit">Rename Folders</button>
</form>

</body>
</html>
