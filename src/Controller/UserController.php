<?php

namespace App\Controller;

use App\Model\ArticleManager;
use App\Model\UserManager;
use App\Model\WishlistManager;

class UserController extends AbstractController
{
    public function index()
    {
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

        return $this->twig->render('User/index.html.twig', [
            'user' => $user,
            'wishlist' => $articlesDetails
        ]);
    }
}