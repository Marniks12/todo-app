<?php
session_start();
require_once 'db.php';
require_once 'task.php';
require_once 'user.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = new User($pdo, $_SESSION['user']);
$userId = $user->getId();

$taskId = (int)($_GET['id'] ?? 0);
try {
    $task = new Task($pdo, $taskId);
} catch (Exception $e) {
    die($e->getMessage());
}

// ✅ Commentaar toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    try {
        $task->addComment($userId, $_POST['comment']);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    header("Location: item.php?id=$taskId");
    exit;
}

// ✅ Bestand uploaden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $task->addFile($_FILES['file']);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    header("Location: item.php?id=$taskId");
    exit;
}

// Commentaren en bestanden ophalen
$comments = $task->getComments();
$files = $task->getFiles();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Taakdetails</title>
    <link rel="stylesheet" href="stylesitem.css">
</head>
<body>

<div class="taskcard">
    <h2>📝 <?= htmlspecialchars($task->getTitle()) ?></h2>
    <p><strong>Prioriteit:</strong> <?= htmlspecialchars($task->getPriority()) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($task->getStatus()) ?></p>
</div>

<div class="task-card">
    <h3>📌 Commentaren</h3>
    <form method="post">
        <textarea name="comment" required placeholder="Voeg commentaar toe..."></textarea><br>
        <button type="submit">Toevoegen</button>
    </form>

    <ul class="comments">
        <?php foreach ($comments as $cmt): ?>
            <li><?= htmlspecialchars($cmt['comment']) ?> <em>(<?= $cmt['created_at'] ?>)</em></li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="task-card">
    <h3>📎 Bestanden uploaden</h3>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Uploaden</button>
    </form>

    <ul class="files">
        <?php foreach ($files as $file): ?>
            <li><a href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank"><?= htmlspecialchars($file['file_name']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>

<a class="back-link" href="javascript:history.back();">⬅️ Terug</a>

</body>
</html>

