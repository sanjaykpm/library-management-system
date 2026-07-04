<?php
class Category extends Model {
    public function getCategories($limit = 10, $offset = 0, $search = '') {
        $sql = 'SELECT * FROM categories';
        if (!empty($search)) {
            $sql .= ' WHERE name LIKE :search';
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

    public function getTotalCategories($search = '') {
        $sql = 'SELECT COUNT(*) as count FROM categories';
        if (!empty($search)) {
            $sql .= ' WHERE name LIKE :search';
        }
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        $row = $this->db->single();
        return $row->count;
    }

    public function getCategoryById($id) {
        $this->db->query('SELECT * FROM categories WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function add($name) {
        $this->db->query('INSERT INTO categories (name) VALUES (:name)');
        $this->db->bind(':name', $name);
        return $this->db->execute();
    }

    public function update($id, $name) {
        $this->db->query('UPDATE categories SET name = :name WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $name);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query('DELETE FROM categories WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
