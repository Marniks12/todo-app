<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // E-mail validatie en wachtwoord lengte check
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 3) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashedPassword]);
            $_SESSION['user'] = $email;
            header('Location: dashboard.php'); 
            exit;
        } catch (PDOException $e) {
            $error = "E-mail is al geregistreerd.";
        }
    } else {
        $error = "Ongeldig e-mailadres of wachtwoord te kort (min 3 tekens).";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Registreren</title>
    <link rel="stylesheet" href="inlog.css">
</head>
<body class="login-body">
    <div class="login-card">
        <h2>Registreren</h2>
        <form method="post">
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="password" placeholder="Wachtwoord (min. 3 tekens)" required>
            <button type="submit">Registreren</button>
        </form>

        <?php if ($error): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="register-link">
            <a href="login.php" class="btn-secondary">Al een account? Inloggen</a>
        </div>
    </div>
</body>
</html>
