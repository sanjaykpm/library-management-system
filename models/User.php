<?php
class User extends Model {
    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    public function findUserByStudentId($student_id) {
        $this->db->query('SELECT * FROM users WHERE student_id = :student_id');
        $this->db->bind(':student_id', $student_id);
        return $this->db->single();
    }

    public function generateStudentId() {
        $this->db->query('SELECT student_id FROM users WHERE student_id LIKE "STD%" ORDER BY id DESC LIMIT 1');
        $row = $this->db->single();

        if ($row) {
            $num = intval(substr($row->student_id, 3));
            $nextNum = $num + 1;
        } else {
            $nextNum = 1;
        }

        return 'STD' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    public function login($email, $password) {
        $row = $this->findUserByEmail($email);
        if ($row) {
            $hashed_password = $row->password;
            if (password_verify($password, $hashed_password)) {
                return $row;
            }
        }
        return false;
    }

    public function register($data) {
        $this->db->query('INSERT INTO users (student_id, reg_no, class, mobile_no, name, email, password, role_id) VALUES (:student_id, :reg_no, :class, :mobile_no, :name, :email, :password, :role_id)');
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':reg_no', $data['reg_no']);
        $this->db->bind(':class', $data['class']);
        $this->db->bind(':mobile_no', $data['mobile_no']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role_id', $data['role_id']);
        return $this->db->execute();
    }

    public function getUsers($limit = 10, $offset = 0, $search = '') {
        $sql = 'SELECT * FROM users WHERE role_id != 1';
        if (!empty($search)) {
            $sql .= ' AND (name LIKE :search OR email LIKE :search OR student_id LIKE :search OR reg_no LIKE :search)';
        }
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalUsers($search = '') {
        $sql = 'SELECT COUNT(*) as count FROM users WHERE role_id != 1';
        if (!empty($search)) {
            $sql .= ' AND (name LIKE :search OR email LIKE :search OR student_id LIKE :search OR reg_no LIKE :search)';
        }
        $this->db->query($sql);
        if (!empty($search)) {
            $this->db->bind(':search', "%$search%");
        }
        $row = $this->db->single();
        return $row->count;
    }

    public function getUserById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function updateProfile($data) {
        $this->db->query('UPDATE users SET name = :name, email = :email, class = :class, mobile_no = :mobile_no WHERE id = :id');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':class', $data['class']);
        $this->db->bind(':mobile_no', $data['mobile_no']);
        $this->db->bind(':id', $data['id']);
        return $this->db->execute();
    }

    public function updatePassword($id, $password) {
        $this->db->query('UPDATE users SET password = :password WHERE id = :id');
        $this->db->bind(':password', $password);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function deleteUser($id) {
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
