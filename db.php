<?php
$host = 'localhost';
$db   = 'todo';
$user = 'root';
$pass = ''; // vul hier je wachtwoord in als je die hebt

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // Zet de error mode op exception zodat fouten duidelijk zijn
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Fout met verbinden: " . $e->getMessage());
}
?>
