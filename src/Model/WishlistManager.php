<?php

namespace App\Model;

/**
 *
 */
class WishlistManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'wishlist';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function insert(array $wish): int
    {
        // prepared request
        $statement = $this->pdo->prepare("INSERT INTO " . self::TABLE . " (article_id, user_id) VALUES (:article_id, :user_id)");
        $statement->bindValue('article_id', $wish['article_id'], \PDO::PARAM_INT);
        $statement->bindValue('user_id', $wish['user_id'], \PDO::PARAM_INT);

        if ($statement->execute()) {
            return (int)$this->pdo->lastInsertId();
        }
    }

    public function delete(int $id, int $userId): void
    {
        // prepared request
        $statement = $this->pdo->prepare("DELETE FROM " . self::TABLE . " WHERE article_id=:id AND user_id=:user_id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $statement->execute();
    }

    public function getWishlistByUser(int $id)
    {
        $statement = $this->pdo->prepare("SELECT id, article_id FROM " . self::TABLE ." WHERE user_id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function isLikedByUser(int $id, int $userId)
    {
        $statement = $this->pdo->prepare("SELECT * FROM " . self::TABLE . " WHERE article_id=:id AND user_id=:user_id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $statement->execute();    
        return $statement->fetch();
    }

}