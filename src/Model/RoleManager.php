<?php

namespace App\Model;

/**
 *
 */
class RoleManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'role';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function selectOneById(int $id)
    {
        // prepared request
        $statement = $this->pdo->prepare("SELECT name FROM $this->table WHERE id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetch();
    }

    public function getRoleUser()
    {
        return $this->pdo->query("SELECT id, name FROM $this->table WHERE name='user'")->fetch();
    }
}
