<?php
namespace App;

use App\Database1;

class StudentAccount {
    private $conn;

    public function __construct() {
        $database = new Database1();
        $this->conn = $database->getConnection();
    }

    public function isFirstLogin($matricule, $password) {
        $query = "SELECT * FROM student_compte WHERE matricule = :matricule AND password = :password";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->bindParam(':password', $matricule);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function authenticate($matricule, $password) {
        $query = "SELECT * FROM student_compte WHERE matricule = :matricule AND password = :password";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':matricule', $matricule);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function changePassword($matricule, $newPassword) {
        $query = "UPDATE student_compte SET password = :password WHERE matricule = :matricule";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $newPassword);
        $stmt->bindParam(':matricule', $matricule);

        return $stmt->execute();
    }
}
?>