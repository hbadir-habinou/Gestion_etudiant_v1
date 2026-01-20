<?php
namespace App;

class Login {
    private $username;
    private $password;

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    public function validate() {
        if ($this->username === "admin" && $this->password === "admin1234") {
            return true;
        } else {
            return "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
}
?>