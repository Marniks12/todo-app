<?php
class Comment {
    private $id;
    private $taskId;
    private $userId;
    private $comment;
    private $createdAt;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->taskId = $data['task_id'];
        $this->userId = $data['user_id'];
        $this->comment = $data['comment'];
        $this->createdAt = $data['created_at'];
    }

    public function getComment() { return $this->comment; }
    public function getCreatedAt() { return $this->createdAt; }
}
?>
