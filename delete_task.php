<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Check of taak-id en list-id zijn meegegeven
$taskId = (int)($_GET['id'] ?? 0);
$listId = (int)($_GET['list_id'] ?? 0);

// Controleer of de taak tot de gebruiker behoort
$stmt = $pdo->prepare("
    SELECT t.id FROM tasks t
    JOIN todo_lists l ON t.list_id = l.id
    JOIN users u ON l.user_id = u.id
    WHERE t.id = ? AND l.id = ? AND u.email = ?
");
$stmt->execute([$taskId, $listId, $_SESSION['user']]);

$task = $stmt->fetch();

if ($task) {
    $delete = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $delete->execute([$taskId]);
}

header("Location: list.php?id=$listId");
exit;
