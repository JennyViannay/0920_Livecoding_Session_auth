<?php

namespace App\Controller;

use App\Model\ArticleManager;
use App\Model\CommandManager;
use App\Model\BrandManager;
use App\Model\ColorManager;
use App\Model\ImageManager;
use App\Model\SizeManager;

class AdminController extends AbstractController
{
    public function index()
    {
        if (isset($_SESSION['role']) && $_SESSION['role'] === "admin") {
            $commandManager = new CommandManager();
            $articleManager = new ArticleManager();
            $articles = $articleManager->selectAll();
            $commands = $commandManager->selectAll();
            return $this->twig->render('Admin/index.html.twig', [
                'articles' => $articles,
                'commands' => $commands
            ]);
        }
        header('Location: /');
    }

    public function editArticle($id = null)
    {
        if (isset($_SESSION['role']) && $_SESSION['role'] === "admin") {
            $brandManager = new BrandManager();
            $brands = $brandManager->selectAll();
            $sizeManager = new SizeManager();
            $sizes = $sizeManager->selectAll();
            $colorManager = new ColorManager();
            $colors = $colorManager->selectAll();

            $articleManager = new ArticleManager();
            $errorForm = null;
            $article = null;
            if ($id != null) {
                $article = $articleManager->selectOneById($id);
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!empty($_POST['model']) && !empty($_POST['price']) && !empty($_POST['brand_id'])
                    && !empty($_POST['size_id']) && !empty($_POST['color_id']) && !empty($_POST['qty'])
                ) {
                    $this->manageArticle($_POST, $id);
                } else {
                    $errorForm = 'Tous les champs sont obligatoires.';
                }
            }
            return $this->twig->render('Admin/edit_article.html.twig', [
                'article' => $article ? $article : null,
                'brands' => $brands,
                'colors' => $colors,
                'sizes' => $sizes,
                'errorForm' => $errorForm
            ]);
        }
        header('Location: /');
    }

    private function manageArticle($data, $id)
    {
        $articleManager = new ArticleManager();
        $article = [
            'id' => $id ? $id : null,
            'model' => $data['model'],
            'price' => $data['price'],
            'qty' => $data['qty'],
            'brand_id' => $data['brand_id'],
            'color_id' => $data['color_id'],
            'size_id' => $data['size_id']
        ];
        if (isset($article['id']) && $article['id'] != null) {
            $articleManager->update($data);
            $images = $data['images'];
            $idImages = $data['image_id'];
            $images = $this->manageUpdateImages($images, $idImages);
            $this->manageImages($images, $id);
            header('Location:/admin/index');
        } else {
            $id = $articleManager->insert($article);
            $images = $data['images'];
            $this->manageImages($images, $id);
            header('Location:/admin/index');
        }
    }

    public function showArticle($id)
    {
        if (isset($_SESSION['role']) && $_SESSION['role'] === "admin") {
            $articleManager = new ArticleManager();
            $article = $articleManager->selectOneById($id);
            return $this->twig->render('Admin/show_article.html.twig', ['article' => $article]);
        }
        header('Location:/');
    }

    public function deleteArticle($id)
    {
        $articleManager = new ArticleManager();
        $articleManager->delete($id);
        header('Location:/admin/index');
    }

    public function deleteImage($id, $idArticle)
    {
        $imageManager = new ImageManager();
        $imageManager->delete($id);
        header('Location:/admin/editArticle/' . $idArticle);
    }

    public function deleteCommand($id)
    {
        $commandManager = new CommandManager();
        $commandManager->delete($id);
        header('Location:/admin/index');
    }

    private function manageImages($images, $id):void
    {
        $imageManager = new ImageManager();
        if (!empty($images)) {
            foreach ($images as $url) {
                $image = [
                    'url' => $url,
                    'article_id' => $id
                ];
                $imageManager->insert($image);
            }
        }
    }

    private function manageUpdateImages($images, $idImages):array
    {
        $imageManager = new ImageManager();
        $arrayImages = [];
        for ($i = 0; $i < count($idImages); $i++) {
            $arrayImages[] = [
                'id' => $idImages[$i],
                'url' => $images[$i]
            ];
            unset($images[$i]);
        }
        foreach ($arrayImages as $image) {
            $imageManager->update($image);
        }
        return $images;
    }
}
