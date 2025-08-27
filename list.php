<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// User ophalen
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch();
$userId = $user['id'];

// Ophalen lijst
$listId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM todo_lists WHERE id = ? AND user_id = ?");
$stmt->execute([$listId, $userId]);
$list = $stmt->fetch();

if (!$list) {
    die("Lijst niet gevonden of geen toegang.");
}

$success = '';
$error = '';

// ✅ POST verwerken: nieuwe taak toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['priority'])) {
    $title = trim($_POST['title']);
    $priority = $_POST['priority'];

    if ($title === '') {
        $error = "Titel mag niet leeg zijn.";
    } else {
        try {
            // Controle op dubbele taak
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE list_id = ? AND title = ?");
            $stmt->execute([$listId, $title]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Er bestaat al een taak met die naam in deze lijst.");
            }

            // Taak toevoegen
            $stmt = $pdo->prepare("INSERT INTO tasks (list_id, title, priority, status, created_at) VALUES (?, ?, ?, 'todo', NOW())");
            $stmt->execute([$listId, $title, $priority]);

            $success = "Taak toegevoegd!";
            header("Location: list.php?id=$listId"); // Pagina herladen
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// ✅ Taken ophalen met sortering
$sortType = $_GET['type'] ?? 'priority';
$sortOrder = $_GET['sort'] ?? 'asc';
$allowedTypes = ['title', 'priority'];
$allowedOrders = ['asc', 'desc'];

if (!in_array($sortType, $allowedTypes)) $sortType = 'priority';
if (!in_array($sortOrder, $allowedOrders)) $sortOrder = 'asc';

$orderClause = '';
if ($sortType === 'priority') {
    $orderClause = "ORDER BY CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END " . strtoupper($sortOrder) . ", created_at DESC";
} else {
    $orderClause = "ORDER BY $sortType " . strtoupper($sortOrder);
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ? $orderClause");
$stmt->execute([$listId]);
$tasks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Taaklijst</title>
    <link rel="stylesheet" href="stylesitem.css">
</head>
<body>
<main class="container">

    <div class="task-card">
        <h2>🗂️ Lijst: <?= htmlspecialchars($list['title']) ?></h2>

        <?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
        <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

        <h3>➕ Nieuwe taak</h3>
        <form method="post">
            <input type="text" name="title" placeholder="Taaknaam" required>
            <select name="priority">
                <option value="low">Laag</option>
                <option value="medium">Middel</option>
                <option value="high">Hoog</option>
            </select>
            <button type="submit">Toevoegen</button>
        </form>
    </div>

    <div class="task-card">
        <h3>🔀 Sorteer taken</h3>
        <a href="list.php?id=<?= $listId ?>&type=title&sort=asc">Titel A-Z</a> |
        <a href="list.php?id=<?= $listId ?>&type=title&sort=desc">Titel Z-A</a> |
        <a href="list.php?id=<?= $listId ?>&type=priority&sort=asc">Prioriteit ↑</a> |
        <a href="list.php?id=<?= $listId ?>&type=priority&sort=desc">Prioriteit ↓</a>
    </div>

    <div class="task-card">
        <h3>📋 Taken</h3>
        <ul>
            <?php foreach ($tasks as $task): ?>
            <li>
    <span><?= htmlspecialchars($task['title']) ?></span>
    <a href="item.php?id=<?= $task['id'] ?>" class="details-btn">Details</a>
    - <strong class="priority-<?= htmlspecialchars($task['priority']) ?>">
        <?= htmlspecialchars($task['priority']) ?>
    </strong>

    <!-- Dropdown status -->
    <select class="status-dropdown" data-id="<?= $task['id'] ?>">
        <option value="todo" <?= $task['status'] === 'todo' ? 'selected' : '' ?>>Todo</option>
        <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
        <option value="done" <?= $task['status'] === 'done' ? 'selected' : '' ?>>Done</option>
    </select>

    <a href="delete_task.php?id=<?= $task['id'] ?>&list_id=<?= $listId ?>"
       onclick="return confirm('Taak verwijderen?')">❌</a>
</li>

            <?php endforeach; ?>
        </ul>
    </div>

    <a class="back-link" href="dashboard.php">⬅️ Terug naar dashboard</a>

</main>

<script>
document.querySelectorAll('.status-dropdown').forEach(dropdown => {
    dropdown.addEventListener('change', () => {
        const taskId = dropdown.dataset.id;
        const newStatus = dropdown.value;

        fetch('toggle_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(taskId) + '&status=' + encodeURIComponent(newStatus)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Status gewijzigd naar:', data.status);
        });
    });
});
</script>

</body>
</html>
