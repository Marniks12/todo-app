<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/TodoList.php';


// Check of gebruiker is ingelogd
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// User object aanmaken
try {
    $user = new User($pdo, $_SESSION['user']);
} catch (Exception $e) {
    die("Gebruiker niet gevonden");
}

$error = '';
$success = '';

// Lijst toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    if ($title !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO todo_lists (user_id, title) VALUES (?, ?)");
            $stmt->execute([$user->getId(), $title]);
            $success = "Lijst toegevoegd!";
        } catch (PDOException $e) {
            $error = "Kon lijst niet toevoegen.";
        }
    } else {
        $error = "Titel mag niet leeg zijn.";
    }
}

// Lijsten ophalen
$stmt = $pdo->prepare("SELECT * FROM todo_lists WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user->getId()]);
$lists = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <h2 class="welcome">Welkom, <?= htmlspecialchars($user->getEmail()) ?></h2>
    <a class="logout" href="logout.php">Uitloggen</a>

    <div class="list-form">
        <form method="post">
            <input type="text" name="title" placeholder="Bijv. Portugal Trip" required>
            <button type="submit" class="add-task-btn">+ Add List</button>
        </form>
    </div>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <div class="list-container">
        <h3 class="list-title">Mijn lijsten</h3>
        <ul class="list-group">
            <?php foreach ($lists as $listRow): 
                try {
                    $list = new TodoList($pdo, $listRow['id']);
                } catch (Exception $e) {
                    continue;
                }
            ?>
                <li class="list-item">
                    <div class="list-info">
                        <span class="list-name"><?= htmlspecialchars($list->getTitle()) ?></span>
                    </div>
                    <div class="list-actions">
                        <a class="edit-btn" href="list.php?id=<?= $list->getId() ?>">✏️</a>
                        <a class="delete-btn" href="delete_list.php?id=<?= $list->getId() ?>" onclick="return confirm('Weet je zeker dat je deze lijst wilt verwijderen?');">🗑️</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

</body>
</html>
