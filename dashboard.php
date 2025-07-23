<?php
session_start();
require_once 'db.php';

// Check of gebruiker is ingelogd
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Haal gebruikersinfo op
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch();

$userId = $user['id'];
$error = '';
$success = '';

// Lijst toevoegen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');

    if ($title !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO todo_lists (user_id, title) VALUES (?, ?)");
            $stmt->execute([$userId, $title]);
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
$stmt->execute([$userId]);
$lists = $stmt->fetchAll();
?>

<h2>Welkom, <?= htmlspecialchars($_SESSION['user']) ?></h2>

<a href="logout.php">Uitloggen</a>

<h3>Nieuwe lijst toevoegen</h3>
<form method="post">
    <input type="text" name="title" placeholder="Bijv. Portugal Trip" required>
    <button type="submit">Toevoegen</button>
</form>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>

<h3>Mijn lijsten</h3>
<ul>
    <?php foreach ($lists as $list): ?>
        <li>
            <?= htmlspecialchars($list['title']) ?>
            <a href="delete_list.php?id=<?= $list['id'] ?>" onclick="return confirm('Weet je zeker dat je deze lijst wilt verwijderen?');">❌</a>
            <a href="list.php?id=<?= $list['id'] ?>">📝</a>
        </li>
    <?php endforeach; ?>
</ul>
