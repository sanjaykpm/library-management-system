<?php
class ReturnRequest extends Model {
    public function createRequest($issue_id, $user_id) {
        // Check if request already exists
        $this->db->query('SELECT * FROM return_requests WHERE issue_id = :issue_id AND status = "pending"');
        $this->db->bind(':issue_id', $issue_id);
        if ($this->db->single()) {
            return false; // Request already exists
        }

        $this->db->query('INSERT INTO return_requests (issue_id, user_id) VALUES (:issue_id, :user_id)');
        $this->db->bind(':issue_id', $issue_id);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    public function getPendingRequests($limit = 10, $offset = 0, $search = '') {
        $sql = 'SELECT return_requests.*, users.name as user_name, users.student_id, 
                       books.title as book_title, books.accession_no, issues.return_date, authors.name as author
                FROM return_requests 
                JOIN users ON return_requests.user_id = users.id 
                JOIN issues ON return_requests.issue_id = issues.id 
                JOIN books ON issues.book_id = books.id 
                LEFT JOIN authors ON books.author_id = authors.id
                WHERE return_requests.status = "pending"';
        
        if (!empty($search)) {
            $sql .= ' AND (users.name LIKE :search OR users.student_id LIKE :search OR books.title LIKE :search OR books.accession_no LIKE :search)';
        }
        
        $sql .= ' ORDER BY return_requests.request_date DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function getTotalPendingRequests($search = '') {
        $sql = 'SELECT COUNT(*) as count FROM return_requests 
                JOIN users ON return_requests.user_id = users.id 
                JOIN issues ON return_requests.issue_id = issues.id 
                JOIN books ON issues.book_id = books.id 
                LEFT JOIN authors ON books.author_id = authors.id
                WHERE return_requests.status = "pending"';
        
        if (!empty($search)) {
            $sql .= ' AND (users.name LIKE :search OR users.student_id LIKE :search OR books.title LIKE :search OR books.accession_no LIKE :search)';
        }
        
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        $row = $this->db->single();
        return $row->count;
    }

    public function getUserPendingCount($user_id) {
        $this->db->query('SELECT COUNT(*) as count FROM return_requests WHERE user_id = :user_id AND status = "pending"');
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();
        return $row->count;
    }

    public function getPendingCount() {
        $this->db->query('SELECT COUNT(*) as count FROM return_requests WHERE status = "pending"');
        $row = $this->db->single();
        return $row->count;
    }

    public function updateStatus($id, $status) {
        $this->db->query('UPDATE return_requests SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getRequestById($id) {
        $this->db->query('SELECT return_requests.*, users.name as user_name, users.student_id, 
                       books.title as book_title, books.accession_no, issues.return_date, authors.name as author
                FROM return_requests 
                JOIN users ON return_requests.user_id = users.id 
                JOIN issues ON return_requests.issue_id = issues.id 
                JOIN books ON issues.book_id = books.id 
                LEFT JOIN authors ON books.author_id = authors.id
                WHERE return_requests.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
