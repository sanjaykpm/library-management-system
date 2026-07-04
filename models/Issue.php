<?php
class Issue extends Model {
    public function issueBook($data) {
        $this->db->query('INSERT INTO issues (user_id, book_id, issue_date, return_date) 
                          VALUES (:user_id, :book_id, :issue_date, :return_date)');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':book_id', $data['book_id']);
        $this->db->bind(':issue_date', $data['issue_date']);
        $this->db->bind(':return_date', $data['return_date']);
        
        if ($this->db->execute()) {
            // Update book availability
            $this->db->query('UPDATE books SET available_quantity = available_quantity - 1 WHERE id = :book_id');
            $this->db->bind(':book_id', $data['book_id']);
            return $this->db->execute();
        }
        return false;
    }

    public function getIssuedBooks($limit = 10, $offset = 0, $search = '', $status = '', $overdue_only = false, $from_date = '', $to_date = '', $class = '') {
        $sql = 'SELECT issues.*, users.name as user_name, users.student_id, users.class, books.title as book_title, books.accession_no, authors.name as author 
                FROM issues 
                JOIN users ON issues.user_id = users.id 
                JOIN books ON issues.book_id = books.id 
                LEFT JOIN authors ON books.author_id = authors.id
                WHERE 1=1';
        
        if (!empty($search)) {
            $sql .= ' AND (users.name LIKE :search OR users.student_id LIKE :search OR books.title LIKE :search OR books.accession_no LIKE :search)';
        }

        if (!empty($status)) {
            $sql .= ' AND issues.status = :status';
        }

        if ($overdue_only) {
            $sql .= ' AND issues.return_date < :today AND issues.status = "issued"';
        }

        if (!empty($from_date)) {
            $sql .= ' AND issues.issue_date >= :from_date';
        }

        if (!empty($to_date)) {
            $sql .= ' AND issues.issue_date <= :to_date';
        }

        if (!empty($class)) {
            $sql .= ' AND users.class = :class';
        }
        
        $sql .= ' ORDER BY issues.issue_date DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        if (!empty($status)) {
            $this->db->bind(':status', $status);
        }
        if ($overdue_only) {
            $this->db->bind(':today', date('Y-m-d'));
        }
        if (!empty($from_date)) {
            $this->db->bind(':from_date', $from_date);
        }
        if (!empty($to_date)) {
            $this->db->bind(':to_date', $to_date);
        }
        if (!empty($class)) {
            $this->db->bind(':class', $class);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function getTotalIssuedBooks($search = '', $status = '', $overdue_only = false, $from_date = '', $to_date = '', $class = '') {
        $sql = 'SELECT COUNT(*) as count FROM issues 
                JOIN users ON issues.user_id = users.id 
                JOIN books ON issues.book_id = books.id 
                LEFT JOIN authors ON books.author_id = authors.id
                WHERE 1=1';
        
        if (!empty($search)) {
            $sql .= ' AND (users.name LIKE :search OR users.student_id LIKE :search OR books.title LIKE :search OR books.accession_no LIKE :search)';
        }

        if (!empty($status)) {
            $sql .= ' AND issues.status = :status';
        }

        if ($overdue_only) {
            $sql .= ' AND issues.return_date < :today AND issues.status = "issued"';
        }

        if (!empty($from_date)) {
            $sql .= ' AND issues.issue_date >= :from_date';
        }

        if (!empty($to_date)) {
            $sql .= ' AND issues.issue_date <= :to_date';
        }

        if (!empty($class)) {
            $sql .= ' AND users.class = :class';
        }
        
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        if (!empty($status)) {
            $this->db->bind(':status', $status);
        }
        if ($overdue_only) {
            $this->db->bind(':today', date('Y-m-d'));
        }
        if (!empty($from_date)) {
            $this->db->bind(':from_date', $from_date);
        }
        if (!empty($to_date)) {
            $this->db->bind(':to_date', $to_date);
        }
        if (!empty($class)) {
            $this->db->bind(':class', $class);
        }
        
        $row = $this->db->single();
        return $row->count;
    }

    public function getUserIssuedBooks($user_id) {
        $this->db->query('SELECT issues.*, books.title as book_title, authors.name as author, books.accession_no 
                          FROM issues 
                          JOIN books ON issues.book_id = books.id 
                          LEFT JOIN authors ON books.author_id = authors.id
                          WHERE issues.user_id = :user_id AND issues.status = "issued"
                          ORDER BY issues.issue_date DESC');
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function returnBook($issue_id) {
        // 1. Get the book_id associated with this issue
        $this->db->query('SELECT book_id FROM issues WHERE id = :id');
        $this->db->bind(':id', $issue_id);
        $row = $this->db->single();
        
        if ($row) {
            $book_id = $row->book_id;
            
            // 2. Update Issue Status
            $this->db->query('UPDATE issues SET status = "returned", actual_return_date = :actual_return_date WHERE id = :id');
            $this->db->bind(':actual_return_date', date('Y-m-d'));
            $this->db->bind(':id', $issue_id);
            
            if ($this->db->execute()) {
                // 3. Increase Book Quantity
                $this->db->query('UPDATE books SET available_quantity = available_quantity + 1 WHERE id = :book_id');
                $this->db->bind(':book_id', $book_id);
                return $this->db->execute();
            }
        }
        return false;
    }

    public function getIssuedCount() {
        $this->db->query('SELECT COUNT(*) as count FROM issues WHERE status = "issued"');
        $row = $this->db->single();
        return $row->count;
    }

    public function getUserIssuedCount($user_id) {
        $this->db->query('SELECT COUNT(*) as count FROM issues WHERE user_id = :user_id AND status = "issued"');
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();
        return $row->count;
    }

    public function getUserReturnedCount($user_id) {
        $this->db->query('SELECT COUNT(*) as count FROM issues WHERE user_id = :user_id AND status = "returned"');
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();
        return $row->count;
    }

    public function getBooksDueSoon($user_id, $days = 3) {
        $targetDate = date('Y-m-d', strtotime("+$days days"));
        $today = date('Y-m-d');
        
        $this->db->query('SELECT issues.*, books.title as book_title 
                          FROM issues 
                          JOIN books ON issues.book_id = books.id 
                          WHERE issues.user_id = :user_id 
                          AND issues.status = "issued" 
                          AND issues.return_date <= :target_date 
                          AND issues.return_date >= :today');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':target_date', $targetDate);
        $this->db->bind(':today', $today);
        return $this->db->resultSet();
    }

    public function getOverdueBooksCount($user_id) {
        $today = date('Y-m-d');
        $this->db->query('SELECT COUNT(*) as count FROM issues WHERE user_id = :user_id AND status = "issued" AND return_date < :today');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':today', $today);
        $row = $this->db->single();
        return $row->count;
    }

    public function getUserOverdueBooks($user_id) {
        $today = date('Y-m-d');
        $this->db->query('SELECT issues.*, books.title as book_title 
                          FROM issues 
                          JOIN books ON issues.book_id = books.id 
                          WHERE issues.user_id = :user_id 
                          AND issues.status = "issued" 
                          AND issues.return_date < :today');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':today', $today);
        return $this->db->resultSet();
    }

    public function getMonthlyIssues($months = 6) {
        $this->db->query("SELECT MONTHNAME(issue_date) as month, COUNT(*) as count 
                          FROM issues 
                          WHERE issue_date >= DATE_SUB(NOW(), INTERVAL :months MONTH) 
                          GROUP BY MONTH(issue_date) 
                          ORDER BY issue_date ASC");
        $this->db->bind(':months', $months);
        return $this->db->resultSet();
    }

    public function getTopBooks($limit = 5) {
        $this->db->query("SELECT books.title, COUNT(issues.id) as count 
                          FROM issues 
                          JOIN books ON issues.book_id = books.id 
                          GROUP BY issues.book_id 
                          ORDER BY count DESC 
                          LIMIT :limit");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getIssueById($id) {
        $this->db->query('SELECT * FROM issues WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
