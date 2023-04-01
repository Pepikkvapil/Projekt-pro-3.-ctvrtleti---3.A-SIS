<?php

class Login
{
    public const DB_TABLE = 'employee';

    /**
     * @param string $login
     * @param string $password
     */
    public function login(string $login, string $password): bool
    {
        $pdo = PDOProvider::get();
        $stmt = $pdo->prepare("SELECT * FROM `".self::DB_TABLE."` WHERE `login`= :login");
        $stmt->execute(['login' => $login]);

        if ($stmt->rowCount() === 0){
            return false;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user['password'] !== $password){
            return false;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'isAdmin' => $user['admin']
        ];

        return true;
    }

}