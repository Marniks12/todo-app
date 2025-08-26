<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// User ophalen
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch();
$userId = $user['id'];

// Taak ophalen
$taskId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$taskId]);
$task = $stmt->fetch();

if (!$task) {
    die("Taak niet gevonden.");
}

// ✅ Commentaar toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if ($comment !== '') {
        $stmt = $pdo->prepare("INSERT INTO comments (task_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$taskId, $userId, $comment]);
    }
    header("Location: item.php?id=$taskId");
    exit;
}

// ✅ Bestand uploaden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadDir = 'uploads/';
    $filename = basename($_FILES['file']['name']);
    $targetPath = $uploadDir . time() . '_' . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO files (task_id, file_name, file_path) VALUES (?, ?, ?)");
        $stmt->execute([$taskId, $filename, $targetPath]);
    }
    header("Location: item.php?id=$taskId");
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Taakdetails</title>
    <link rel="stylesheet" href="stylesitem.css">
</head>
<body>

<div class="task-card">
    <h2>📝 <?= htmlspecialchars($task['title']) ?></h2>
    <p><strong>Prioriteit:</strong> <?= htmlspecialchars($task['priority']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($task['status']) ?></p>
</div>

<div class="task-card">
    <h3>📌 Commentaren</h3>
    <form method="post" class="comment-form">
        <textarea name="comment" required placeholder="Voeg commentaar toe..."></textarea><br>
        <button type="submit">Toevoegen</button>
    </form>

    <ul class="comments">
        <?php
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE task_id = ? ORDER BY created_at DESC");
        $stmt->execute([$taskId]);
        $comments = $stmt->fetchAll();

        foreach ($comments as $cmt): ?>
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
        <?php
        $stmt = $pdo->prepare("SELECT * FROM files WHERE task_id = ?");
        $stmt->execute([$taskId]);
        $files = $stmt->fetchAll();

        foreach ($files as $file): ?>
            <li><a href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank"><?= htmlspecialchars($file['file_name']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>

<a class="back-link" href="javascript:history.back();">⬅️ Terug</a>

</body>
</html>
