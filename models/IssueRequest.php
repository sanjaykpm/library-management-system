<?php
class IssueRequest extends Model {
    public function createRequest($user_id, $book_id) {
        // Check if request already exists
        $this->db->query('SELECT * FROM issue_requests WHERE user_id = :user_id AND book_id = :book_id AND status = "pending"');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':book_id', $book_id);
        
        if ($this->db->single()) {
            return false; // Request already exists
        }

        $this->db->query('INSERT INTO issue_requests (user_id, book_id) VALUES (:user_id, :book_id)');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':book_id', $book_id);
        
        return $this->db->execute();
    }

    public function getRequestsByUserId($user_id) {
        $this->db->query('SELECT issue_requests.*, books.title, authors.name as author, books.accession_no 
                          FROM issue_requests 
                          JOIN books ON issue_requests.book_id = books.id 
                          LEFT JOIN authors ON books.author_id = authors.id
                          WHERE issue_requests.user_id = :user_id 
                          ORDER BY issue_requests.request_date DESC');
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getPendingRequests($limit = 10, $offset = 0, $search = '') {
        $sql = 'SELECT issue_requests.*, users.name as user_name, users.student_id, books.title as book_title, books.accession_no, books.available_quantity, authors.name as author
                FROM issue_requests 
                JOIN users ON issue_requests.user_id = users.id 
                JOIN books ON issue_requests.book_id = books.id 
                LEFT JOIN authors ON books.author_id = authors.id
                WHERE issue_requests.status = "pending"';
        
        if (!empty($search)) {
            $sql .= ' AND (users.name LIKE :search OR users.student_id LIKE :search OR books.title LIKE :search OR books.accession_no LIKE :search)';
        }
        
        $sql .= ' ORDER BY issue_requests.request_date ASC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function getTotalPendingRequests($search = '') {
        $sql = 'SELECT COUNT(*) as count FROM issue_requests 
                JOIN users ON issue_requests.user_id = users.id 
                JOIN books ON issue_requests.book_id = books.id 
                LEFT JOIN authors ON books.author_id = authors.id
                WHERE issue_requests.status = "pending"';
        
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

    public function getRequestById($id) {
        $this->db->query('SELECT * FROM issue_requests WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateStatus($id, $status) {
        $this->db->query('UPDATE issue_requests SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
