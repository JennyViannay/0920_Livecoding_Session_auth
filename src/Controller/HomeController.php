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
        $articleManager = new ArticleManager();
        $articles = $articleManager->selectNineLast();

        return $this->twig->render('Home/index.html.twig', [
            'articles' => $articles
        ]);
    }
    
    public function articles(array $articles = null)
    {
        $result = [];
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
            if (isset($_POST['brand_id']) || isset($_POST['color_id']) || isset($_POST['size_id'])) {
                $articles = $filterService->getArticlesFromSearch($_POST);
            }
            if (isset($_POST['search']) && !empty($_POST['search'])) {
                $articles = $articleManager->searchByModel($_POST['search']);
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
        $articlesDetails = [];
        $cartService = new CartService();
        $filterService = new FilterService();
        $wishlistManager = new WishlistManager();
        $articleManager = new ArticleManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['delete_id'])) {
                $article = $_POST['delete_id'];
                $cartService->delete($article);
            }
            if (isset($_POST['payment'])) {
                if (!empty($_POST['name']) && !empty($_POST['address'])) {
                    $cartService->payment($_POST);
                } else {
                    $_SESSION['flash_message'] = ["Tous les champs sont obligatoires !"];
                    header('Location:/home/cart');
                }
            }
            if (isset($_POST['update_cart'])) {
                $cartService->update($_POST);
            }
            if (isset($_POST['add_article'])) {
                $article = $_POST['add_article'];
                $cartService->add($article);
            }
            if (isset($_POST['brand_id']) || isset($_POST['color_id']) || isset($_POST['size_id'])) {
                $filterService->getArticlesFromSearch($_POST);
            }
        }
        if ($id != null){
            $article = $id;
            $cartService->delete($article);
        }

        if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
            $wishlist = $wishlistManager->getWishlistByUser($_SESSION['id']);
        }

        if (isset($_SESSION['cart'])) {
            $suggest = $cartService->suggest();
        }

        if ($wishlist != null) {
            foreach ($wishlist as $wish) {
                $article = $articleManager->selectOneById($wish['article_id']);
                $article['wishlist_id'] = $wish['id'];
                $article['is_liked'] = 'true'; 
                $articlesDetails[] = $article;
            }
        }

        return $this->twig->render('Home/cart.html.twig', [
            'cartInfos' => $cartService->cartInfos() ? $cartService->cartInfos() : null,
            'total' => $cartService->cartInfos() ? $cartService->totalCart() : null,
            'wishlist' => $articlesDetails,
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
