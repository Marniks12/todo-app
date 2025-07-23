<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Zoek gebruiker op in database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Controleer wachtwoord
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $email;
        header('Location: dashboard.php'); // nog te maken
        exit;
    } else {
        $error = "Ongeldige login.";
    }
}
?>

<h2>Inloggen</h2>
<form method="post">
    <input type="email" name="email" placeholder="E-mail" required><br><br>
    <input type="password" name="password" placeholder="Wachtwoord" required><br><br>
    <button type="submit">Inloggen</button>
</form>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
