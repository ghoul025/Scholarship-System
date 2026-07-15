<?php
require 'config.php';
$file = 'Uploads/credentials/doc_15_1756824187_functional_programming_examples_EAlday_PPL.pdf';
echo 'Exists: ' . (file_exists($file) ? 'yes' : 'no') . "\n";
echo 'Realpath: ' . realpath($file) . "\n";
