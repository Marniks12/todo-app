<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['id'] ?? null;
    $newStatus = $_POST['status'] ?? null;

    $allowed = ['todo', 'in_progress', 'done'];

    if ($taskId && in_array($newStatus, $allowed)) {
        $update = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $update->execute([$newStatus, $taskId]);

        echo json_encode(['status' => $newStatus]);
        exit;
    }
}
http_response_code(400);
echo json_encode(['error' => 'Ongeldige aanvraag']);
