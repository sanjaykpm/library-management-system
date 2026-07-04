<?php
class Fine extends Model {
    const FINE_PER_DAY = 10; // ₹10 per day

    public function calculateFine($issue_id) {
        $this->db->query('SELECT * FROM issues WHERE id = :id');
        $this->db->bind(':id', $issue_id);
        $issue = $this->db->single();

        if (!$issue) {
            return 0;
        }

        $returnDate = new DateTime($issue->return_date);
        
        if ($issue->status == 'issued') {
            $toDate = new DateTime();
        } elseif ($issue->status == 'returned' && $issue->actual_return_date) {
            $toDate = new DateTime($issue->actual_return_date);
        } else {
            return 0;
        }

        if ($toDate <= $returnDate) {
            return 0; 
        }

        $interval = $toDate->diff($returnDate);
        return $interval->days * self::FINE_PER_DAY;
    }

    public function createFine($user_id, $issue_id, $amount) {
        $this->db->query('INSERT INTO fines (user_id, issue_id, amount, status) VALUES (:user_id, :issue_id, :amount, "unpaid")');
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':issue_id', $issue_id);
        $this->db->bind(':amount', $amount);
        return $this->db->execute();
    }

    public function getUserTotalFine($user_id) {
        $this->db->query('SELECT SUM(amount) as total FROM fines WHERE user_id = :user_id AND status = "unpaid"');
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function getUserFines($user_id) {
        $this->db->query('SELECT fines.*, books.title as book_title, issues.return_date 
                          FROM fines 
                          JOIN issues ON fines.issue_id = issues.id 
                          JOIN books ON issues.book_id = books.id 
                          WHERE fines.user_id = :user_id 
                          ORDER BY fines.created_at DESC');
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getAllFines() {
        $this->db->query('SELECT fines.*, users.name as user_name, users.student_id, books.title as book_title 
                          FROM fines 
                          JOIN users ON fines.user_id = users.id 
                          JOIN issues ON fines.issue_id = issues.id 
                          JOIN books ON issues.book_id = books.id 
                          ORDER BY fines.created_at DESC');
        return $this->db->resultSet();
    }

    public function updateFineStatus($fine_id, $status) {
        $this->db->query('UPDATE fines SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $fine_id);
        return $this->db->execute();
    }

    public function getFineByIssueId($issue_id) {
        $this->db->query('SELECT * FROM fines WHERE issue_id = :issue_id');
        $this->db->bind(':issue_id', $issue_id);
        return $this->db->single();
    }

    public function getTotalFinesAmount($status = '') {
        $total = 0;
        
        // Persisted fines
        $sql = 'SELECT SUM(amount) as total FROM fines';
        if ($status) {
            $sql .= ' WHERE status = :status';
        }
        $this->db->query($sql);
        if ($status) {
            $this->db->bind(':status', $status);
        }
        $row = $this->db->single();
        $total += $row->total ?? 0;

        // If status is empty (Total) or 'unpaid', add accrued fines from overdue issued books
        if ($status == '' || $status == 'unpaid') {
            $today = date('Y-m-d');
            $this->db->query('SELECT return_date FROM issues WHERE status = "issued" AND return_date < :today');
            $this->db->bind(':today', $today);
            $overdueIssues = $this->db->resultSet();
            
            foreach ($overdueIssues as $issue) {
                $returnDate = new DateTime($issue->return_date);
                $currDate = new DateTime($today);
                $interval = $currDate->diff($returnDate);
                $total += $interval->days * self::FINE_PER_DAY;
            }
        }

        return $total;
    }

    public function getClassWiseFines() {
        // Get persisted fines by class
        $this->db->query('SELECT users.class, SUM(fines.amount) as total_fine 
                          FROM fines 
                          JOIN users ON fines.user_id = users.id 
                          GROUP BY users.class');
        $persisted = $this->db->resultSet();
        
        $classFines = [];
        foreach ($persisted as $p) {
            $class = $p->class ?: 'N/A';
            $classFines[$class] = $p->total_fine;
        }

        // Add accrued fines
        $today = date('Y-m-d');
        $this->db->query('SELECT users.class, issues.return_date 
                          FROM issues 
                          JOIN users ON issues.user_id = users.id 
                          WHERE issues.status = "issued" AND issues.return_date < :today');
        $this->db->bind(':today', $today);
        $overdue = $this->db->resultSet();

        foreach ($overdue as $issue) {
            $class = $issue->class ?: 'N/A';
            $returnDate = new DateTime($issue->return_date);
            $currDate = new DateTime($today);
            $interval = $currDate->diff($returnDate);
            $accrued = $interval->days * self::FINE_PER_DAY;
            
            if (isset($classFines[$class])) {
                $classFines[$class] += $accrued;
            } else {
                $classFines[$class] = $accrued;
            }
        }

        // Convert back to object format for the view
        $result = [];
        foreach ($classFines as $class => $total) {
            $result[] = (object)['class' => $class, 'total_fine' => $total];
        }

        // Sort by total fine DESC
        usort($result, function($a, $b) {
            return $b->total_fine <=> $a->total_fine;
        });

        return $result;
    }

    public function getFinesWithFilters($limit = 10, $offset = 0, $search = '', $status = '', $from_date = '', $to_date = '', $class = '') {
        $sqlPersisted = 'SELECT fines.id, fines.user_id, fines.issue_id, fines.amount, fines.status, fines.created_at, 
                                users.name as user_name, users.student_id, users.class, books.title as book_title, "persisted" as source, NULL as return_date
                         FROM fines 
                         JOIN users ON fines.user_id = users.id 
                         JOIN issues ON fines.issue_id = issues.id 
                         JOIN books ON issues.book_id = books.id 
                         WHERE 1=1';

        $sqlAccrued = 'SELECT NULL as id, issues.user_id, issues.id as issue_id, 0 as amount, "accrued" as status, CURRENT_TIMESTAMP as created_at,
                               users.name as user_name, users.student_id, users.class, books.title as book_title, "accrued" as source, issues.return_date
                        FROM issues
                        JOIN users ON issues.user_id = users.id
                        JOIN books ON issues.book_id = books.id
                        WHERE issues.status = "issued" AND issues.return_date < CURRENT_DATE';

        $filters = '';
        if ($search) {
            $filters .= ' AND (user_name LIKE :search OR student_id LIKE :search OR book_title LIKE :search)';
        }
        if ($class) {
            $filters .= ' AND class = :class';
        }

        $sql = "SELECT * FROM (
                    ($sqlPersisted)
                    UNION ALL
                    ($sqlAccrued)
                ) as combined WHERE 1=1";
        
        if ($status) {
            if ($status == 'unpaid') {
                $sql .= ' AND (status = "unpaid" OR status = "accrued")';
            } else {
                $sql .= ' AND status = :status';
            }
        }

        if ($from_date) {
            $sql .= ' AND DATE(created_at) >= :from_date';
        }
        if ($to_date) {
            $sql .= ' AND DATE(created_at) <= :to_date';
        }
        
        $sql .= $filters;
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

        $this->db->query($sql);

        if ($search) {
            $this->db->bind(':search', "%$search%");
        }
        if ($status && $status != 'unpaid') {
            $this->db->bind(':status', $status);
        }
        if ($from_date) {
            $this->db->bind(':from_date', $from_date);
        }
        if ($to_date) {
            $this->db->bind(':to_date', $to_date);
        }
        if ($class) {
            $this->db->bind(':class', $class);
        }

        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);

        $results = $this->db->resultSet();
        $today = new DateTime();
        
        foreach ($results as &$row) {
            if ($row->source == 'accrued') {
                $returnDate = new DateTime($row->return_date);
                $interval = $today->diff($returnDate);
                $row->amount = $interval->days * self::FINE_PER_DAY;
            }
        }
        
        return $results;
    }

    public function getTotalFinesCount($search = '', $status = '', $from_date = '', $to_date = '', $class = '') {
        $sqlPersisted = 'SELECT fines.status, fines.created_at, users.name as user_name, users.student_id, users.class, books.title as book_title
                         FROM fines 
                         JOIN users ON fines.user_id = users.id 
                         JOIN issues ON fines.issue_id = issues.id 
                         JOIN books ON issues.book_id = books.id 
                         WHERE 1=1';

        $sqlAccrued = 'SELECT "accrued" as status, CURRENT_TIMESTAMP as created_at, users.name as user_name, users.student_id, users.class, books.title as book_title
                        FROM issues
                        JOIN users ON issues.user_id = users.id
                        JOIN books ON issues.book_id = books.id
                        WHERE issues.status = "issued" AND issues.return_date < CURRENT_DATE';

        $filters = '';
        if ($search) {
            $filters .= ' AND (user_name LIKE :search OR student_id LIKE :search OR book_title LIKE :search)';
        }
        if ($class) {
            $filters .= ' AND class = :class';
        }

        $sql = "SELECT COUNT(*) as count FROM (
                    ($sqlPersisted)
                    UNION ALL
                    ($sqlAccrued)
                ) as combined WHERE 1=1";
        
        if ($status) {
            if ($status == 'unpaid') {
                $sql .= ' AND (status = "unpaid" OR status = "accrued")';
            } else {
                $sql .= ' AND status = :status';
            }
        }

        if ($from_date) {
            $sql .= ' AND DATE(created_at) >= :from_date';
        }
        if ($to_date) {
            $sql .= ' AND DATE(created_at) <= :to_date';
        }
        
        $sql .= $filters;

        $this->db->query($sql);

        if ($search) {
            $this->db->bind(':search', "%$search%");
        }
        if ($status && $status != 'unpaid') {
            $this->db->bind(':status', $status);
        }
        if ($from_date) {
            $this->db->bind(':from_date', $from_date);
        }
        if ($to_date) {
            $this->db->bind(':to_date', $to_date);
        }
        if ($class) {
            $this->db->bind(':class', $class);
        }

        $row = $this->db->single();
        return $row->count;
    }
}
