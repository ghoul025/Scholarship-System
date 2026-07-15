<?php
require 'config.php';
$count = $conn->query('SELECT COUNT(*) FROM documents')->fetchColumn();
echo 'Documents count: ' . $count;
