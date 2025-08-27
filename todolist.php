<?php
class TodoList {
    private $pdo;
    private $id;
    private $title;
    private $userId;

    public function __construct($pdo, $id) {
        $this->pdo = $pdo;
        $this->id = $id;

        $stmt = $this->pdo->prepare("SELECT * FROM todo_lists WHERE id = ?");
        $stmt->execute([$id]);
        $list = $stmt->fetch();

        if (!$list) {
            throw new Exception("Lijst niet gevonden");
        }

        $this->title = $list['title'];
        $this->userId = $list['user_id'];
    }

    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getUserId() { return $this->userId; }

    public function getTasks($sort = 'priority', $order = 'asc') {
        $allowedSort = ['title', 'priority', 'status'];
        $allowedOrder = ['asc', 'desc'];
        if (!in_array($sort, $allowedSort)) $sort = 'priority';
        if (!in_array($order, $allowedOrder)) $order = 'asc';

        if ($sort === 'priority') {
            $orderClause = "ORDER BY CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END " . strtoupper($order);
        } else {
            $orderClause = "ORDER BY $sort " . strtoupper($order);
        }

        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE list_id = ? $orderClause");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    public function addTask($title, $priority) {
        // dubbele taak check
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tasks WHERE list_id = ? AND title = ?");
        $stmt->execute([$this->id, $title]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Taak bestaat al in deze lijst");
        }

        $stmt = $this->pdo->prepare("INSERT INTO tasks (list_id, title, priority, status, created_at) VALUES (?, ?, ?, 'todo', NOW())");
        $stmt->execute([$this->id, $title, $priority]);
    }
}
