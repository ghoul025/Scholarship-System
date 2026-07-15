<?php
require 'config.php';
$rows = $conn->query('SELECT file_path FROM documents')->fetchAll(PDO::FETCH_COLUMN);
print_r($rows);
