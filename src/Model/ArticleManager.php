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
        $models = $this->pdo->query('SELECT DISTINCT model FROM article')->fetchAll();
        $result = [];
        foreach ($models as $model) {
            //var_dump('model :', $model);
            $article = $this->selectOneByModel($model['model']);
            //var_dump('article from select one by model :', $article);
            $declinaisons = $this->searchByModel($article[0]['model']);
            //var_dump('declinaisons :', $declinaisons); die;
            for ($i = 0; $i < count($declinaisons); $i++) {
                if (!isset($article[0]['sizes'][$declinaisons[$i]['size_id']])) {
                    $article[0]['sizes'][$declinaisons[$i]['size_id']] = [
                        'size' => $declinaisons[$i]['size'],
                        'article_id' => $declinaisons[$i]['id']
                    ];
                }
                if (!isset($article['colors'][$declinaisons[$i]['color_id']])) {
                    $article[0]['colors'][$declinaisons[$i]['id']] = [
                        'color' => $declinaisons[$i]['color_name'],
                        'article_id' => $declinaisons[$i]['id']
                    ];
                }
                if (!isset($article['quantity'][$declinaisons[$i]['color_name']])) {
                    $article[0]['quantity'][$declinaisons[$i]['color_name']] = $declinaisons[$i]['qty'];
                }
            }
            $result[] = $article[0];
        }
        // foreach ($result as $key => $value) {
         //var_dump($result[0]);die;
        // }
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

        $declinaisons = $this->searchByModel($article['model']);
        for ($i = 0; $i < count($declinaisons); $i++) {
            if (!isset($article['sizes'][$declinaisons[$i]['size_id']])) {
                $article['sizes'][$declinaisons[$i]['size_id']] = [
                    'size' => $declinaisons[$i]['size'],
                    'article_id' => $declinaisons[$i]['id']
                ];
            }
            if (!isset($article['colors'][$declinaisons[$i]['color_id']])) {
                $article['colors'][$declinaisons[$i]['color_id']] = [
                    'color' => $declinaisons[$i]['color_name'],
                    'article_id' => $declinaisons[$i]['id']
                ];
            }
            if (!isset($article['quantity'][$declinaisons[$i]['color_name']])) {
                $article['quantity'][$declinaisons[$i]['color_name']] = $declinaisons[$i]['qty'];
            }
        }

        $statementImg = $this->pdo->prepare('SELECT id, url FROM image WHERE article_id=:article_id');
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
            return (int) $this->pdo->lastInsertId();
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare("DELETE FROM " . self::TABLE . " WHERE id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
    }

    public function update(array $article): bool
    {
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE . " 
        SET brand_id=:brand_id, 
        qty=:qty, 
        model=:model, 
        price=:price, 
        size_id=:size_id, 
        color_id=:color_id 
        WHERE id=:id");
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
        $statement->bindValue('search', $term . '%', \PDO::PARAM_STR);
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

    public function selectOneByModel(string $term): array
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
        $statement->bindValue('search', $term . '%', \PDO::PARAM_STR);
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

    public function selectNineLast(): array
    {
        $statement = $this->pdo->query("SELECT
        art.id, art.brand_id, art.model, art.qty, art.model, art.price, art.size_id, art.color_id, 
        brand.name as brand_name,
        color.name as color_name,
        size.size as size 
        FROM article as art 
        JOIN brand ON art.brand_id=brand.id
        JOIN color ON art.color_id=color.id
        JOIN size ON art.size_id=size.id
        ORDER BY art.id DESC LIMIT 9");

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
