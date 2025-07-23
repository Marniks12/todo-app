<?php
session_start();
require_once 'db.php';

// Check of gebruiker is ingelogd
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Haal gebruiker ID op
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch();

$userId = $user['id'];

// Check of ID is meegegeven via GET
if (isset($_GET['id'])) {
    $listId = (int) $_GET['id'];

    // Beveiliging: alleen eigen lijsten mogen verwijderd worden
    $stmt = $pdo->prepare("DELETE FROM todo_lists WHERE id = ? AND user_id = ?");
    $stmt->execute([$listId, $userId]);
}

header('Location: dashboard.php');
exit;
