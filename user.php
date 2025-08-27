<?php
class User {
    private $pdo;
    private $email;
    private $id;

    public function __construct($pdo, $email) {
        $this->pdo = $pdo;
        $this->email = $email;

        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception("Gebruiker niet gevonden");
        }

        $this->id = $user['id'];
    }

    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }
}
