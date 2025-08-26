<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Eenvoudige e-mail validatie
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Ongeldig e-mailadres.";
    } elseif (empty($password)) {
        $error = "Vul je wachtwoord in.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); // voorkomt session fixation
                $_SESSION['user'] = $email;
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Ongeldige gebruikersnaam of wachtwoord.";
            }
        } catch (PDOException $e) {
            $error = "Er is iets misgegaan, probeer opnieuw.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <title>Inloggen</title>
    <link rel="stylesheet" href="inlog.css">
</head>
<body class="login-body">
    <div class="login-card">
        <h2>Inloggen</h2>
        <form method="post">
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="password" placeholder="Wachtwoord" required>
            <button type="submit">Inloggen</button>
        </form>

        <?php if ($error): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="register-link">
            <a href="register.php" class="btn-secondary">Nog geen account? Registreer</a>
        </div>
    </div>
</body>
</html>
