<?php

namespace App\Service;

use App\Model\ArticleManager;

class CartService 
{
    public function add($article)
    {
        if (!empty($_SESSION['cart'][$article])) {
            $_SESSION['cart'][$article]++;
        } else {
            $_SESSION['cart'][$article] = 1;
        }
        $_SESSION['count'] = $this->countArticle();
        header('Location:/');
    }

    public function delete($article)
    {
        $cart = $_SESSION['cart'];
        if(!empty($cart[$article])) {
            unset($cart[$article]);
        }
        $_SESSION['cart'] = $cart;
        $_SESSION['count'] = $this->countArticle();
        header('Location:/home/cart');
    }

    public function cartInfos()
    {
        if(isset($_SESSION['cart'])){
            $cart = $_SESSION['cart'];
            $cartInfos = [];
            $articleManager = new ArticleManager();
            foreach($cart as $id => $qty){
                $infosArticle = $articleManager->selectOneById($id);
                $infosArticle['qty'] = $qty;
                $cartInfos[] = $infosArticle;
            }
            return $cartInfos;
        } 
        return false;
    }

    function totalCart()
    {
        $total = 0;
        if($this->cartInfos() != false){
            foreach($this->cartInfos() as $item){
                $total += $item['price'] * $item['qty'];
            }
            return $total;
        }
        return $total;
    }

    public function countArticle()
    {
        $total = 0;
        if($this->cartInfos() != false){
            foreach($this->cartInfos() as $item){
                $total += $item['qty'];
            }
            return $total;
        }
        return $total;
    }
}