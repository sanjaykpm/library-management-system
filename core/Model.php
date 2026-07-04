<?php
/**
 * Base Model
 */
class Model {
    protected $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    public function commit() {
        return $this->db->commit();
    }

    public function rollBack() {
        return $this->db->rollBack();
    }
}
