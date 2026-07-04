<?php
class Book extends Model {
    public function getBooks($limit = 10, $offset = 0, $search = '') {
        $sql = 'SELECT books.*, categories.name as category_name, authors.name as author 
                FROM books 
                LEFT JOIN categories ON books.category_id = categories.id
                LEFT JOIN authors ON books.author_id = authors.id';
        
        if (!empty($search)) {
            $sql .= ' WHERE books.title LIKE :search OR authors.name LIKE :search OR books.isbn LIKE :search OR books.accession_no LIKE :search';
        }
        
        $sql .= ' ORDER BY books.created_at DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function getTotalBooks($search = '') {
        $sql = 'SELECT COUNT(*) as count FROM books 
                LEFT JOIN authors ON books.author_id = authors.id';
        
        if (!empty($search)) {
            $sql .= ' WHERE books.title LIKE :search OR authors.name LIKE :search OR books.isbn LIKE :search OR books.accession_no LIKE :search';
        }
        
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        
        $row = $this->db->single();
        return $row->count;
    }

    public function getTotalAvailableQuantity() {
        $this->db->query('SELECT SUM(available_quantity) as total FROM books');
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function getCategories() {
        $this->db->query('SELECT * FROM categories ORDER BY name ASC');
        return $this->db->resultSet();
    }

    public function getBookById($id) {
        $this->db->query('SELECT * FROM books WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function findBookByAccession($accession_no) {
        $this->db->query('SELECT * FROM books WHERE accession_no = :accession_no');
        $this->db->bind(':accession_no', $accession_no);
        return $this->db->single();
    }

    public function generateAccessionNumber() {
        $this->db->query('SELECT accession_no FROM books ORDER BY id DESC LIMIT 1');
        $row = $this->db->single();
        
        if ($row) {
            // Extract number from ACC0001
            $lastNum = intval(substr($row->accession_no, 3));
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }
        
        return 'ACC' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function add($data) {
        // Auto-generate Accession No if not provided or to ensure uniqueness
        if (empty($data['accession_no'])) {
            $data['accession_no'] = $this->generateAccessionNumber();
        }

        $this->db->query('INSERT INTO books (accession_no, title, author_id, category_id, isbn, quantity, available_quantity, image) 
                          VALUES (:accession_no, :title, :author_id, :category_id, :isbn, :quantity, :available_quantity, :image)');
        $this->db->bind(':accession_no', $data['accession_no']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':author_id', $data['author_id']);
        $this->db->bind(':category_id', $data['category_id']);
        $this->db->bind(':isbn', $data['isbn']);
        $this->db->bind(':quantity', $data['quantity']);
        $this->db->bind(':available_quantity', $data['quantity']);
        $this->db->bind(':image', $data['image']);
        return $this->db->execute();
    }

    public function update($data) {
        $this->db->query('UPDATE books SET title = :title, author_id = :author_id, category_id = :category_id, isbn = :isbn, quantity = :quantity WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':author_id', $data['author_id']);
        $this->db->bind(':category_id', $data['category_id']);
        $this->db->bind(':isbn', $data['isbn']);
        $this->db->bind(':quantity', $data['quantity']);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM books WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getBooksByCategory() {
        $this->db->query("SELECT categories.name, COUNT(books.id) as count 
                          FROM books 
                          JOIN categories ON books.category_id = categories.id 
                          GROUP BY books.category_id");
        return $this->db->resultSet();
    }
}
