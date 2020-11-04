<?php

namespace App\Model;

/**
 *
 */
class UserManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'user';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function search(string $email)
    {
        // prepared request
        $statement = $this->pdo->prepare("SELECT * FROM " . self::TABLE . " WHERE email=:email");
        $statement->bindValue('email', $email, \PDO::PARAM_STR);
        $statement->execute();
        $user = $statement->fetchObject();
        if ($user) {
            return $user;
        }
        return false;
    }

    /**
     * @param array $user
     * @return int
     */
    public function insert(array $user): int
    {
        // prepared request
        $statement = $this->pdo->prepare("INSERT INTO " . self::TABLE .
        " (email, username, password, role_id) VALUES (:email, :username, :password, :role_id)");
        $statement->bindValue('email', $user['email'], \PDO::PARAM_STR);
        $statement->bindValue('username', $user['username'], \PDO::PARAM_STR);
        $statement->bindValue('password', $user['password'], \PDO::PARAM_STR);
        $statement->bindValue('role_id', $user['role_id'], \PDO::PARAM_INT);

        if ($statement->execute()) {
            return (int)$this->pdo->lastInsertId();
        }
    }


    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        // prepared request
        $statement = $this->pdo->prepare("DELETE FROM " . self::TABLE . " WHERE id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
    }


    /**
     * @param array $user
     * @return bool
     */
    public function update(array $user):bool
    {

        // prepared request
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE .
        " SET email=:email, username=:username, password=:password, role_id=:role_id WHERE id=:id");
        $statement->bindValue('id', $user['id'], \PDO::PARAM_INT);
        $statement->bindValue('email', $user['email'], \PDO::PARAM_STR);
        $statement->bindValue('username', $user['username'], \PDO::PARAM_STR);
        $statement->bindValue('password', $user['password'], \PDO::PARAM_STR);
        $statement->bindValue('role_id', $user['role_id'], \PDO::PARAM_INT);

        return $statement->execute();
    }

    public function getRole(int $id)
    {
        $statement = $this->pdo->prepare("SELECT
        role.name as role_name 
        FROM " . self::TABLE ." 
        JOIN role ON role.id=user.role_id 
        WHERE user.id=:id"
        );
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
        $role = $statement->fetch();
        if ($role) {
            return $role;
        }
        return false;
    }
}
