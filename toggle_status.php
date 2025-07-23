<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskId = $_POST['id'] ?? null;

    if ($taskId) {
        $stmt = $pdo->prepare("SELECT status FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();

        if ($task) {
            $newStatus = $task['status'] === 'done' ? 'todo' : 'done';

            $update = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
            $update->execute([$newStatus, $taskId]);

            echo json_encode(['status' => $newStatus]);
            exit;
        }
    }
}
http_response_code(400);
echo json_encode(['error' => 'Ongeldige aanvraag']);
