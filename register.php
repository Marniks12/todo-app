<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // E-mail validatie en wachtwoord lengte check
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 6) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$email, $hashedPassword]);
            $_SESSION['user'] = $email;
            header('Location: dashboard.php'); // later maken we deze pagina
            exit;
        } catch (PDOException $e) {
            $error = "E-mail is al geregistreerd.";
        }
    } else {
        $error = "Ongeldig e-mailadres of wachtwoord te kort (min 6 tekens).";
    }
}
?>

<h2>Registreren</h2>
<form method="post">
    <input type="email" name="email" placeholder="E-mail" required><br><br>
    <input type="password" name="password" placeholder="Wachtwoord (min 6 tekens)" required><br><br>
    <button type="submit">Registreren</button>
</form>

<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
