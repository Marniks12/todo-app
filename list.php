<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Haal user info op
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch();

$userId = $user['id'];
$listId = (int)($_GET['id'] ?? 0);

// Sortering ophalen
$sortType = $_GET['type'] ?? 'priority';
$sortOrder = $_GET['sort'] ?? 'asc';
$allowedTypes = ['title', 'priority'];
$allowedOrders = ['asc', 'desc'];

if (!in_array($sortType, $allowedTypes)) $sortType = 'priority';
if (!in_array($sortOrder, $allowedOrders)) $sortOrder = 'asc';

// Haal lijst op (controleer of deze van de ingelogde user is)
$stmt = $pdo->prepare("SELECT * FROM todo_lists WHERE id = ? AND user_id = ?");
$stmt->execute([$listId, $userId]);
$list = $stmt->fetch();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $priority = $_POST['priority'] ?? 'low';

    if ($title === '') {
        $error = "Titel mag niet leeg zijn.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE list_id = ? AND title = ?");
            $stmt->execute([$listId, $title]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                throw new Exception("Er bestaat al een taak met die naam in deze lijst.");
            }

            $stmt = $pdo->prepare("INSERT INTO tasks (list_id, title, priority) VALUES (?, ?, ?)");
            $stmt->execute([$listId, $title, $priority]);
            $success = "Taak toegevoegd!";
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

if (!$list) {
    die("Lijst niet gevonden of geen toegang.");
}
?>

<h2>Lijst: <?= htmlspecialchars($list['title']) ?></h2>

<h3>Nieuwe taak toevoegen</h3>
<form method="post">
    <input type="text" name="title" placeholder="Taaknaam" required>
    <select name="priority">
        <option value="low">Laag</option>
        <option value="medium">Middel</option>
        <option value="high">Hoog</option>
    </select>
    <button type="submit">Toevoegen</button>
</form>

<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<h3>Sorteer taken</h3>
<p>
    <a href="list.php?id=<?= $listId ?>&type=title&sort=asc">Titel ⬆️</a> |
    <a href="list.php?id=<?= $listId ?>&type=title&sort=desc">Titel ⬇️</a> |
    <a href="list.php?id=<?= $listId ?>&type=priority&sort=asc">Prioriteit ⬆️</a> |
    <a href="list.php?id=<?= $listId ?>&type=priority&sort=desc">Prioriteit ⬇️</a>
</p>

<h3>Taken in deze lijst</h3>
<ul>
<?php
$orderClause = "";
if ($sortType === 'priority') {
    $orderClause = "ORDER BY 
        CASE priority 
            WHEN 'high' THEN 1 
            WHEN 'medium' THEN 2 
            WHEN 'low' THEN 3 
        END " . strtoupper($sortOrder) . ", created_at DESC";
} else {
    $orderClause = "ORDER BY $sortType " . strtoupper($sortOrder);
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE list_id = ? $orderClause");
$stmt->execute([$listId]);
$tasks = $stmt->fetchAll();

foreach ($tasks as $task):
?>
    <li>
        <input type="checkbox" class="toggle-status" data-id="<?= $task['id'] ?>" <?= $task['status'] === 'done' ? 'checked' : '' ?>>
       <a href="item.php?id=<?= $task['id'] ?>">
    <?= htmlspecialchars($task['title']) ?>
</a> - 
<strong><?= $task['priority'] ?></strong>
        <a href="delete_task.php?id=<?= $task['id'] ?>&list_id=<?= $listId ?>" onclick="return confirm('Taak verwijderen?')">❌</a>
    </li>
<?php endforeach; ?>
</ul>

<a href="dashboard.php">⬅️ Terug naar dashboard</a>

<script>
document.querySelectorAll('.toggle-status').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        const taskId = checkbox.dataset.id;

        fetch('toggle_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(taskId)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Status gewijzigd naar:', data.status);
        });
    });
});
</script>
