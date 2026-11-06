<?php
# create a class database that show the interface between the database and the application
class Database {
# The properties and methods are only accessed within the class.
    private $host = "localhost"; #Declares the host 
    private $db_name = "prop1db";# Declares the database name 
    private $username = "root"; # Declares the username for sql database
    private $password = ""; # Declares the password for sql database
    public $conn;

    public function getConnection() { # It handles and establishes database connection.
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
        }
        return $this->conn;
    }
}
?>