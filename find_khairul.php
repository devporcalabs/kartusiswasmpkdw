<?php
require_once 'config.php';
$stmt = $pdo->prepare("SELECT * FROM students WHERE nama_lengkap LIKE '%KHAIRUL%' OR nama_lengkap LIKE '%NIZAM%' OR nama_lengkap LIKE '%IZAM%'");
$stmt->execute();
$results = $stmt->fetchAll();
print_r($results);
?>
