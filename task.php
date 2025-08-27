<?php
class Task {
    private $pdo;
    private $id;
    private $title;
    private $priority;
    private $status;

    public function __construct($pdo, $id) {
        $this->pdo = $pdo;
        $this->id = $id;

        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $task = $stmt->fetch();

        if (!$task) {
            throw new Exception("Taak niet gevonden");
        }

        $this->title = $task['title'];
        $this->priority = $task['priority'];
        $this->status = $task['status'];
    }

    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getPriority() { return $this->priority; }
    public function getStatus() { return $this->status; }

    // ✅ Status wijzigen
    public function setStatus($newStatus) {
        $allowed = ['todo', 'in_progress', 'done'];
        if (!in_array($newStatus, $allowed)) {
            throw new Exception("Ongeldige status");
        }

        $stmt = $this->pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $this->id]);
        $this->status = $newStatus;
    }

    // ✅ Commentaar toevoegen
    public function addComment($userId, $comment) {
        if (trim($comment) === '') throw new Exception("Commentaar mag niet leeg zijn");
        $stmt = $this->pdo->prepare("INSERT INTO comments (task_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->execute([$this->id, $userId, $comment]);
    }

    // ✅ Commentaren ophalen
    public function getComments() {
        $stmt = $this->pdo->prepare("SELECT * FROM comments WHERE task_id = ? ORDER BY created_at DESC");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    // ✅ Bestand uploaden
    public function addFile($file) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $filename = basename($file['name']);
        $targetPath = $uploadDir . time() . '_' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $relativePath = 'uploads/' . time() . '_' . $filename;
            $stmt = $this->pdo->prepare("INSERT INTO files (task_id, file_name, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$this->id, $filename, $relativePath]);
        } else {
            throw new Exception("Bestand uploaden mislukt");
        }
    }

    // ✅ Bestanden ophalen
    public function getFiles() {
        $stmt = $this->pdo->prepare("SELECT * FROM files WHERE task_id = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
}
?>
