<?php
class Logger extends Model {
    public function log($action, $details = '') {
        $userId = $_SESSION['user_id'] ?? null;
        
        $this->db->query('INSERT INTO activity_logs (user_id, action, details) VALUES (:user_id, :action, :details)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':action', $action);
        $this->db->bind(':details', $details);
        
        return $this->db->execute();
    }

    public function getRecentLogs($limit = 10) {
        $this->db->query('SELECT activity_logs.*, users.name as user_name 
                          FROM activity_logs 
                          LEFT JOIN users ON activity_logs.user_id = users.id 
                          ORDER BY activity_logs.created_at DESC 
                          LIMIT :limit');
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }
}
