<?php
// index.php
session_start();

// Als gebruiker is ingelogd → naar takenlijst
if (isset($_SESSION['user_id'])) {
    header("Location: tasks.php");
    exit;
}

// Anders → naar login
header("Location: login.php");
exit;
?>
