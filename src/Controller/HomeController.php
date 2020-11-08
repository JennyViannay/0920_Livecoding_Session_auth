<?php

namespace App\Controller;

use App\Service\CartService;
use App\Service\FilterService;
use App\Model\ArticleManager;
use App\Model\BrandManager;
use App\Model\ColorManager;
use App\Model\SizeManager;
use App\Model\WishlistManager;

class HomeController extends AbstractController
{
    public function index()
    {
        $result = [];
        $cartService = new CartService();
        $articleManager = new ArticleManager();
        $filterService = new FilterService();

        $articles = $articleManager->selectNineLast();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['add_article'])) {
                $article = $_POST['add_article'];
                $cartService->add($article);
            }
            if (isset($_POST['search']) || isset($_POST['brand_id']) || isset($_POST['color_id']) || isset($_POST['size_id'])) {
                $articles = $filterService->search($_POST);
            }
        }

        return $this->twig->render('Home/index.html.twig', [
            'articles' => $articles
        ]);
    }
    
    public function articles()
    {
        $result = [];
        $cartService = new CartService();
        $filterService = new FilterService();

        $brandManager = new BrandManager();
        $brands = $brandManager->selectAll();

        $sizeManager = new SizeManager();
        $sizes = $sizeManager->selectAll();

        $colorManager = new ColorManager();
        $colors = $colorManager->selectAll();

        $articleManager = new ArticleManager();
        $articles = $articleManager->selectAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['add_article'])) {
                $article = $_POST['add_article'];
                $cartService->add($article);
            }
            if (isset($_POST['search']) || isset($_POST['brand_id']) || isset($_POST['color_id']) || isset($_POST['size_id'])) {
                $articles = $filterService->search($_POST);
            }
        }

        $wishlist = null;
        $wishlistManager = new WishlistManager();

        if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
            $wishlist = $wishlistManager->getWishlistByUser($_SESSION['id']);
        }

        if(!isset($_SESSION['username']) && empty($_SESSION['username'])){
            foreach ($articles as $article) {
                $result[] = $article;
            }
        }

        if($wishlist){
            foreach ($articles as $article) {
                foreach($wishlist as $wish){
                    if($wish['article_id'] === $article['id']){
                        $article['is_liked'] = 'true';    
                    }
                }
                $result[] = $article; 
            }
        } else {
            $result = $articles;
        }

        return $this->twig->render('Home/articles.html.twig', [
            'articles' => $result,
            'brands' => $brands,
            'colors' => $colors,
            'sizes' => $sizes,
            'wishlist' => $wishlist
        ]);
    }

    public function showArticle($id)
    {
        $cartService = new CartService();
        $articleManager = new ArticleManager();
        $article = $articleManager->selectOneById($id);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['add_article'])) {
                $article = $_POST['add_article'];
                $cartService->add($article);
            }
        }
        return $this->twig->render('Home/show_article.html.twig', ['article' => $article]);
    }

    public function cart(int $id = null)
    {
        $wishlist = null;
        $suggest = null;
        $cartService = new CartService();
        $wishlistManager = new WishlistManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['payment'])) {
                if (!empty($_POST['name']) && !empty($_POST['address'])) {
                    $cartService->payment($_POST);
                } else {
                    $_SESSION['flash_message'] = ["Tous les champs sont obligatoires !"];
                }
            }
            if (isset($_POST['update_cart'])) {
                $cartService->update($_POST);
            }
        }
        if ($id != null){
            $article = $id;
            $cartService->delete($article);
        }

        if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
            $wishlist = $wishlistManager->getWishlistByUser($_SESSION['id']);
        }

        if (isset($_SESSION['cart'])){
            $suggest = $cartService->suggest();
        }
        return $this->twig->render('Home/cart.html.twig', [
            'cartInfos' => $cartService->cartInfos() ? $cartService->cartInfos() : null,
            'total' => $cartService->cartInfos() ? $cartService->totalCart() : null,
            'wishlist' => $wishlist,
            'suggest' =>  $suggest
        ]);
    }

    public function success()
    {
        return $this->twig->render('Home/success.html.twig');
    }

    public function like(int $id)
    {
        $wishlistManager = new WishlistManager();
        $isLiked = $wishlistManager->isLikedByUser($id, $_SESSION['id']);
        if (!$isLiked) {
            $wish = [
                'user_id' => $_SESSION['id'],
                'article_id' => $id
            ];
            $wishlistManager->insert($wish);
            header('Location:/');
        } else {
            $_SESSION['flash_message'] = ['Article déjà dans votre wishlist'];
            header('Location:/');
        }
    }

    public function dislike(int $id)
    {
        $wishlistManager = new WishlistManager();
        $wishlistManager->delete($id, $_SESSION['id']);
        header('Location:/');
    }

    public function clear_flash()
    {
        unset($_SESSION['flash_message']);
        return json_encode('true');
    }
}
