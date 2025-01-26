<?php
class Database {
    private $host = "localhost";
    private $db_name = "student_portal";
    private $username = "root"; // MySQL username
    private $password = ""; // MySQL password
    public $con;

    // Method to create a connection to the database
    public function getConnection() {
        $this->con = null;

        try {
            $this->con = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->con;
    }

    // Method to execute a SELECT query
    public function executeSelectQuery($con, $sql) {
        try {
            $stmt = $con->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            echo "Error: " . $exception->getMessage();
        }
    }

    // Method to execute an INSERT, UPDATE, or DELETE query
    public function executeQuery($con, $sql) {
        try {
            $stmt = $con->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $exception) {
            echo "Error: " . $exception->getMessage();
        }
    }
}
