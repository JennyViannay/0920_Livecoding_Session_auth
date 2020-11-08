<?php

namespace App\Controller;

use App\Service\CartService;
use App\Model\ArticleManager;
use App\Model\UserManager;
use App\Model\WishlistManager;

class UserController extends AbstractController
{
    public function index()
    {
        $cartService = new CartService();
        $userManager = new UserManager();
        $user = $userManager->selectOneById($_SESSION['id']);

        $wishlistManager = new WishlistManager();
        $wishlist = $wishlistManager->getWishlistByUser($user['id']);

        $articleManager = new ArticleManager();
        $articlesDetails = [];

        foreach ($wishlist as $wish) {
            $article = $articleManager->selectOneById($wish['article_id']);
            $article['wishlist_id'] = $wish['id'];
            $article['is_liked'] = 'true'; 
            $articlesDetails[] = $article;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_article'])) {
                $article = $_POST['add_article'];
                $cartService->add($article);
            }
        }

        return $this->twig->render('User/index.html.twig', [
            'user' => $user,
            'wishlist' => $articlesDetails
        ]);
    }
}