<?php
$host = 'shuttle.proxy.rlwy.net';
$port = 18284;
$db   = 'railway';
$user = 'root';
$pass = 'SgGpYCyiCMhVFPugBvFfuiSsWQPnnZbg';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Fout bij verbinden: " . $e->getMessage());
}
?>
