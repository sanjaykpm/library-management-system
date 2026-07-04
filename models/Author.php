<?php
class Author extends Model {
    public function getAuthors($limit = 10, $offset = 0, $search = '') {
        $sql = 'SELECT * FROM authors';
        if (!empty($search)) {
            $sql .= ' WHERE name LIKE :search OR bio LIKE :search';
        }
        $sql .= ' ORDER BY name ASC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalAuthors($search = '') {
        $sql = 'SELECT COUNT(*) as count FROM authors';
        if (!empty($search)) {
            $sql .= ' WHERE name LIKE :search OR bio LIKE :search';
        }
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        $row = $this->db->single();
        return $row->count;
    }

    public function getAuthorById($id) {
        $this->db->query('SELECT * FROM authors WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function add($data) {
        $this->db->query('INSERT INTO authors (name, bio) VALUES (:name, :bio)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':bio', $data['bio']);
        return $this->db->execute();
    }

    public function update($data) {
        $this->db->query('UPDATE authors SET name = :name, bio = :bio WHERE id = :id');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':bio', $data['bio']);
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM authors WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
