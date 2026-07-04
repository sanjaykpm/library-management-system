<?php
class Notification {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Create a new notification
    public function create($user_id, $message, $type = 'info') {
        $this->db->query('INSERT INTO notifications (user_id, message, type, status) VALUES (:user_id, :message, :type, "pending")');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':message', $message);
        $this->db->bind(':type', $type);
        return $this->db->execute();
    }

    // Get all pending notifications for a user
    public function getPendingNotifications($user_id) {
        $this->db->query('SELECT * FROM notifications WHERE user_id = :user_id AND status = "pending" ORDER BY created_at DESC');
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    // Get unread count
    public function getUnreadCount($user_id) {
        $this->db->query('SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND status = "pending"');
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();
        return $row->count;
    }

    // Mark a notification as read
    public function markAsRead($id, $user_id) {
        $this->db->query('UPDATE notifications SET status = "read" WHERE id = :id AND user_id = :user_id');
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }
    
    // Mark ALL notifications as read for a user
    public function markAllAsRead($user_id) {
        $this->db->query('UPDATE notifications SET status = "read" WHERE user_id = :user_id AND status = "pending"');
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }
}
