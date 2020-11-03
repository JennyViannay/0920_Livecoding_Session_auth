<?php

namespace App\Controller;

use App\Model\ArticleManager;
use App\Service\CartService;
use App\Model\BrandManager;
use App\Model\ColorManager;
use App\Model\SizeManager;

class HomeController extends AbstractController
{

    /**
     * Display home page
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function index()
    {
        //$cartService = new CartService();

        $brandManager = new BrandManager();
        $brands = $brandManager->selectAll();

        $sizeManager = new SizeManager();
        $sizes = $sizeManager->selectAll();

        $colorManager = new ColorManager();
        $colors = $colorManager->selectAll();

        $articleManager = new ArticleManager();
        $articles = $articleManager->selectAll();

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if (!empty($_POST['add_article'])) {
                $article = $_POST['add_article'];
                //$cartService->add($article);
            }
            // search
            if (!empty($_POST['search'])) {
                $articles = $articleManager->searchByModel($_POST['search']);
            }
            // brand_id
            if (!empty($_POST['brand_id'])) {
                $articles = $articleManager->searchByBrand($_POST['brand_id']);
            }
            // color_id
            if (!empty($_POST['color_id'])) {
                $articles = $articleManager->searchByColor($_POST['color_id']);
            }
            // size_id
            if (!empty($_POST['size_id'])) {
                $articles = $articleManager->searchBySize($_POST['size_id']);
            }
            // brand_id + size_id + color_id 
            if (!empty($_POST['brand_id']) && !empty($_POST['size_id']) && !empty($_POST['color_id'])) {
                $articles = $articleManager->searchFull($_POST['color_id'], $_POST['size_id'], $_POST['brand_id']);
            }
        }
        return $this->twig->render('Home/index.html.twig', [
            'articles' => $articles,
            'brands' => $brands,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

    public function showArticle($id)
    {
        //$cartService = new CartService();
        $articleManager = new ArticleManager();
        $article = $articleManager->selectOneById($id);
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if (!empty($_POST['add_article'])) {
                $article = $_POST['add_article'];
                //$cartService->add($article);
            }
        }
        return $this->twig->render('Home/show_article.html.twig', ['article' => $article]);
    }

    public function cart()
    {
        //$cartService = new CartService();
        $errorForm = null;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if (isset($_POST['delete_id'])) {
                $article = $_POST['delete_id'];
                //$cartService->delete($article);
            }
            if (isset($_POST['payment'])) {
                if (!empty($_POST['name']) && !empty($_POST['address'])) {
                    //$cartService->payment($_POST);
                } else {
                    $errorForm = "Tous les champs sont obligatoires !";
                }
            }
        }
        return $this->twig->render('Home/cart.html.twig', [
            // 'cartInfos' => $cartService->cartInfos() ? $cartService->cartInfos() : null,
            // 'total' => $cartService->cartInfos() ? $cartService->totalCart() : null,
            // 'errorForm' => $errorForm
            'cartInfos' => null,
            'total' => null,
            'errorForm' => $errorForm
        ]);
    }

    public function success()
    {
        return $this->twig->render('Home/success.html.twig');
    }
}
