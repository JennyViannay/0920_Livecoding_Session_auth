<?php

namespace App\Model;

/**
 *
 */
class ArticleManager extends AbstractManager
{
    /**
     *
     */
    const TABLE = 'article';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function selectAll(): array
    {
        $articles = $this->pdo->query('SELECT
        art.id, art.brand_id, art.model, art.qty, art.model, art.price, art.size_id, art.color_id, 
        brand.name as brand_name,
        color.name as color_name,
        size.size as size 
        FROM article as art 
        JOIN brand ON art.brand_id=brand.id
        JOIN color ON art.color_id=color.id
        JOIN size ON art.size_id=size.id
        ')->fetchAll();

        $images = $this->pdo->query('SELECT url, article_id FROM image')->fetchAll();
        $result = [];
        foreach ($articles as $article) {
            $statementImg = $this->pdo->prepare('SELECT url FROM image WHERE article_id=:article_id');
            $statementImg->bindValue('article_id', $article['id'], \PDO::PARAM_INT);
            $statementImg->execute();
            $images = $statementImg->fetchAll();
            $article['images'] = $images;
            array_push($result, $article);
        }
        
        return $result;
    }

    public function selectOneById(int $id)
    {
        // prepared request
        $statement = $this->pdo->prepare("SELECT
        art.id, art.brand_id, art.model, art.qty, art.model, art.price, art.size_id, art.color_id, 
        brand.name as brand_name,
        color.name as color_name,
        size.size as size 
        FROM article as art 
        JOIN brand ON art.brand_id=brand.id
        JOIN color ON art.color_id=color.id
        JOIN size ON art.size_id=size.id
        WHERE art.id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
        $article = $statement->fetch();

        $statementImg = $this->pdo->prepare('SELECT url FROM image WHERE article_id=:article_id');
        $statementImg->bindValue('article_id', $id, \PDO::PARAM_INT);
        $statementImg->execute();
        $images = $statementImg->fetchAll();
        $article['images'] = $images;

        return $article;
    }

    /**
     * @param array $article
     * @return int
     */
    public function insert(array $article): int
    {
        // prepared request
        $statement = $this->pdo->prepare("INSERT INTO " . self::TABLE . " (brand_id, qty, model, price, size_id, color_id) VALUES (:brand_id, :qty, :model, :price, :size_id, :color_id)");
        $statement->bindValue('brand_id', $article['brand_id'], \PDO::PARAM_INT);
        $statement->bindValue('qty', $article['qty'], \PDO::PARAM_INT);
        $statement->bindValue('model', $article['model'], \PDO::PARAM_STR);
        $statement->bindValue('price', $article['price'], \PDO::PARAM_INT);
        $statement->bindValue('size_id', $article['size_id'], \PDO::PARAM_INT);
        $statement->bindValue('color_id', $article['color_id'], \PDO::PARAM_INT);

        if ($statement->execute()) {
            return (int)$this->pdo->lastInsertId();
        }
    }

    public function delete(int $id): void
    {
        // prepared request
        $statement = $this->pdo->prepare("DELETE FROM " . self::TABLE . " WHERE id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
    }

    public function update(array $article):bool
    {

        // prepared request
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE . " SET brand_id=:brand_id, qty=:qty, model=:model, price:price, size_id:size_id, color_id:color_id WHERE id=:id");
        $statement->bindValue('id', $article['id'], \PDO::PARAM_INT);
        $statement->bindValue('brand_id', $article['brand_id'], \PDO::PARAM_INT);
        $statement->bindValue('qty', $article['qty'], \PDO::PARAM_INT);
        $statement->bindValue('model', $article['model'], \PDO::PARAM_STR);
        $statement->bindValue('price', $article['price'], \PDO::PARAM_INT);
        $statement->bindValue('size_id', $article['size_id'], \PDO::PARAM_INT);
        $statement->bindValue('color_id', $article['color_id'], \PDO::PARAM_INT);

        return $statement->execute();
    }

    public function searchByModel(string $term): array
    {
        $statement = $this->pdo->prepare("SELECT
        art.id, art.brand_id, art.model, art.qty, art.model, art.price, art.size_id, art.color_id, 
        brand.name as brand_name,
        color.name as color_name,
        size.size as size 
        FROM article as art 
        JOIN brand ON art.brand_id=brand.id
        JOIN color ON art.color_id=color.id
        JOIN size ON art.size_id=size.id
        WHERE model LIKE :search ORDER BY model ASC");
        $statement->bindValue('search', $term.'%', \PDO::PARAM_STR);
        $statement->execute();
        $articles = $statement->fetchAll();

        $images = $this->pdo->query('SELECT url, article_id FROM image')->fetchAll();
        $result = [];
        foreach ($articles as $article) {
            $statementImg = $this->pdo->prepare('SELECT url FROM image WHERE article_id=:article_id');
            $statementImg->bindValue('article_id', $article['id'], \PDO::PARAM_INT);
            $statementImg->execute();
            $images = $statementImg->fetchAll();
            $article['images'] = $images;
            array_push($result, $article);
        }
        
        return $result;
    }

    public function searchByBrand(int $brand_id): array
    {
        $statement = $this->pdo->prepare("SELECT
        art.id, art.brand_id, art.model, art.qty, art.model, art.price, art.size_id, art.color_id, 
        brand.name as brand_name,
        color.name as color_name,
        size.size as size 
        FROM article as art 
        JOIN brand ON art.brand_id=brand.id
        JOIN color ON art.color_id=color.id
        JOIN size ON art.size_id=size.id
        WHERE brand_id = :brand_id ORDER BY model ASC");
        $statement->bindValue('brand_id', $brand_id, \PDO::PARAM_INT);
        $statement->execute();
        $articles = $statement->fetchAll();
        
        $images = $this->pdo->query('SELECT url, article_id FROM image')->fetchAll();
        $result = [];
        foreach ($articles as $article) {
            $statementImg = $this->pdo->prepare('SELECT url FROM image WHERE article_id=:article_id');
            $statementImg->bindValue('article_id', $article['id'], \PDO::PARAM_INT);
            $statementImg->execute();
            $images = $statementImg->fetchAll();
            $article['images'] = $images;
            array_push($result, $article);
        }
        
        return $result;
    }

    public function searchByColor(int $color_id): array
    {
        $statement = $this->pdo->prepare("SELECT
        art.id, art.brand_id, art.model, art.qty, art.model, art.price, art.size_id, art.color_id, 
        brand.name as brand_name,
        color.name as color_name,
        size.size as size 
        FROM article as art 
        JOIN brand ON art.brand_id=brand.id
        JOIN color ON art.color_id=color.id
        JOIN size ON art.size_id=size.id
        WHERE color_id = :color_id ORDER BY model ASC");
        $statement->bindValue('color_id', $color_id, \PDO::PARAM_INT);
        $statement->execute();
        $articles = $statement->fetchAll();
        
        $images = $this->pdo->query('SELECT url, article_id FROM image')->fetchAll();
        $result = [];
        foreach ($articles as $article) {
            $statementImg = $this->pdo->prepare('SELECT url FROM image WHERE article_id=:article_id');
            $statementImg->bindValue('article_id', $article['id'], \PDO::PARAM_INT);
            $statementImg->execute();
            $images = $statementImg->fetchAll();
            $article['images'] = $images;
            array_push($result, $article);
        }
        
        return $result;
    }

    public function searchBySize(int $size_id): array
    {
        $statement = $this->pdo->prepare("SELECT 
        art.id, art.brand_id, art.model, art.qty, art.model, art.price, art.size_id, art.color_id, 
        brand.name as brand_name,
        color.name as color_name,
        size.size as size 
        FROM article as art 
        JOIN brand ON art.brand_id=brand.id
        JOIN color ON art.color_id=color.id
        JOIN size ON art.size_id=size.id
        WHERE size_id = :size_id ORDER BY model ASC");
        $statement->bindValue('size_id', $size_id, \PDO::PARAM_INT);
        $statement->execute();
        $articles = $statement->fetchAll();
        
        $images = $this->pdo->query('SELECT url, article_id FROM image')->fetchAll();
        $result = [];
        foreach ($articles as $article) {
            $statementImg = $this->pdo->prepare('SELECT url FROM image WHERE article_id=:article_id');
            $statementImg->bindValue('article_id', $article['id'], \PDO::PARAM_INT);
            $statementImg->execute();
            $images = $statementImg->fetchAll();
            $article['images'] = $images;
            array_push($result, $article);
        }
        
        return $result;
    }

    public function searchFull(int $color_id, int $size_id, int $brand_id): array
    {
        $statement = $this->pdo->prepare("SELECT
        art.id, art.brand_id, art.model, art.qty, art.model, art.price, art.size_id, art.color_id, 
        brand.name as brand_name,
        color.name as color_name,
        size.size as size 
        FROM article as art 
        JOIN brand ON art.brand_id=brand.id
        JOIN color ON art.color_id=color.id
        JOIN size ON art.size_id=size.id 
        WHERE size_id = :size_id
        AND brand_id = :brand_id
        AND color_id = :color_id
        ORDER BY model ASC
        ");
        $statement->bindValue('size_id', $size_id, \PDO::PARAM_INT);
        $statement->bindValue('color_id', $color_id, \PDO::PARAM_INT);
        $statement->bindValue('brand_id', $brand_id, \PDO::PARAM_INT);
        $statement->execute();
        $articles = $statement->fetchAll();
        
        $images = $this->pdo->query('SELECT url, article_id FROM image')->fetchAll();
        $result = [];
        foreach ($articles as $article) {
            $statementImg = $this->pdo->prepare('SELECT url FROM image WHERE article_id=:article_id');
            $statementImg->bindValue('article_id', $article['id'], \PDO::PARAM_INT);
            $statementImg->execute();
            $images = $statementImg->fetchAll();
            $article['images'] = $images;
            array_push($result, $article);
        }
        
        return $result;
    }
}
