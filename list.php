<?php
session_start();
require_once 'db.php';
require_once 'user.php';
require_once 'todoList.php';
require_once 'task.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

try {
    $user = new User($pdo, $_SESSION['user']);
} catch (Exception $e) {
    die($e->getMessage());
}

// Lijst ophalen
$listId = (int)($_GET['id'] ?? 0);
try {
    $list = new TodoList($pdo, $listId);
} catch (Exception $e) {
    die("Lijst niet gevonden of geen toegang.");
}

$success = '';
$error = '';

// ✅ Nieuwe taak toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['priority'])) {
    $title = trim($_POST['title']);
    $priority = $_POST['priority'];

    try {
        $list->addTask($title, $priority);
        $success = "Taak toegevoegd!";
        header("Location: list.php?id=$listId");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// ✅ Taken ophalen met sortering
$sortType = $_GET['type'] ?? 'priority';
$sortOrder = $_GET['sort'] ?? 'asc';
$tasks = $list->getTasks($sortType, $sortOrder);

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
    <h2>🗂️ Lijst: <?= htmlspecialchars($list->getTitle()) ?></h2>

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
        <?php foreach ($tasks as $taskData): 
            $task = new Task($pdo, $taskData['id']); ?>
            <li>
                <span><?= htmlspecialchars($task->getTitle()) ?></span>
                <a href="item.php?id=<?= $task->getId() ?>" class="details-btn">Details</a>
                - <strong class="priority-<?= htmlspecialchars($task->getPriority()) ?>">
                    <?= htmlspecialchars($task->getPriority()) ?>
                </strong>

                <!-- Dropdown status -->
                <select class="status-dropdown" data-id="<?= $task->getId() ?>">
                    <option value="todo" <?= $task->getStatus() === 'todo' ? 'selected' : '' ?>>Todo</option>
                    <option value="in_progress" <?= $task->getStatus() === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="done" <?= $task->getStatus() === 'done' ? 'selected' : '' ?>>Done</option>
                </select>

                <a href="delete_task.php?id=<?= $task->getId() ?>&list_id=<?= $listId ?>"
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
        .then(data => console.log('Status gewijzigd naar:', data.status));
    });
});
</script>

</body>
</html>
