<?php
// debug_list_files.php
// Place this in your web root (public_html(2)) and access via browser

function listFiles($dir, $base = '') {
    $result = [];
    if (!is_dir($dir)) {
        return ["ERROR: $dir is not a directory or does not exist."];
    }
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $relPath = ltrim($base . '/' . $item, '/');
        $info = $relPath;
        if (is_dir($path)) {
            $info .= '/';
            $result[] = $info;
            $result = array_merge($result, listFiles($path, $relPath));
        } else {
            $info .= is_readable($path) ? '' : ' [NOT READABLE]';
            $result[] = $info;
        }
    }
    return $result;
}

$root = __DIR__;
$uploads = $root . '/Uploads/credentials';

header('Content-Type: text/plain');
echo "Listing all files and folders under: $root\n\n";

$list = listFiles($root);
foreach ($list as $line) {
    echo $line . "\n";
}

echo "\n---\n\n";
echo "Checking Uploads/credentials existence and readability...\n";
if (is_dir($uploads)) {
    echo "Uploads/credentials exists.\n";
    if (is_readable($uploads)) {
        echo "Uploads/credentials is readable.\n";
    } else {
        echo "Uploads/credentials is NOT readable!\n";
    }
} else {
    echo "Uploads/credentials does NOT exist!\n";
}
?>
