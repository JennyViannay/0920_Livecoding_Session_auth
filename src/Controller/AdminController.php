<?php

namespace App\Controller;

use App\Model\ArticleManager;
use App\Model\CommandManager;
use App\Model\BrandManager;
use App\Model\ColorManager;
use App\Model\SizeManager;

class AdminController extends AbstractController
{
    public function index()
    {
        if (isset($_SESSION['username'])) {
            // if (isset($_SESSION['role'] && $_SESSION['role'] === "ADMIN"){}
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
        if (isset($_SESSION['username'])) {
            $brandManager = new BrandManager();
            $brands = $brandManager->selectAll();
            $sizeManager = new SizeManager();
            $sizes = $sizeManager->selectAll();
            $colorManager = new ColorManager();
            $colors = $colorManager->selectAll();

            if (!isset($_SESSION['username'])) {
                header('Location:/home/index');
            }
            $articleManager = new ArticleManager();
            $errorForm = null;
            $article = null;
            if ($id != null) {
                $article = $articleManager->selectOneById($id);
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (
                    !empty($_POST['model']) && !empty($_POST['price']) && !empty($_POST['brand_id'])
                    && !empty($_POST['size_id']) && !empty($_POST['color_id']) && !empty($_POST['qty'])
                ) {
                    $this->sendArticle($_POST, $id);
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
            header('Location: /');
        }
    }

    public function sendArticle($data, $id)
    {
        $articleManager = new ArticleManager();
        $article = [
            'id' => $id ? $id : '',
            'model' => $data['model'],
            'price' => $data['price'],
            'qty' => $data['qty'],
            'brand_id' => $data['brand_id'],
            'color_id' => $data['color_id'],
            'size_id' => $data['size_id']
        ];
        if (isset($article['id']) && !empty($article['id'])) {
            $articleManager->update($data);
            header('Location:/admin/index');
        } else {
            $id = $articleManager->insert($article);
            // TODO :: CREATE IMAGE 
            header('Location:/admin/index');
        }
    }

    public function deleteArticle($id)
    {
        $articleManager = new ArticleManager();
        $articleManager->delete($id);
        header('Location:/admin/index');
    }

    public function deleteCommand($id)
    {
        $commandManager = new CommandManager();
        $commandManager->delete($id);
        header('Location:/admin/index');
    }
}
