<?php

namespace App\Service;

use Stripe\Stripe;
use App\Model\ArticleManager;
use App\Model\CommandManager;

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
        header('Location:/home/articles');
    }

    public function update(array $array)
    {
        for ($i = 0; $i < count($array['id']); $i++ ) {
            foreach ($_SESSION['cart'] as $key => $value) {
                $_SESSION['cart'][$array['id'][$i]] = $array['qty'][$i]; 
            }
        }
        header('Location:/home/cart');
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

    public function payment($infos)
    {
        $stripe = \Stripe\Stripe::setApiKey(API_KEY);

        $commandManager = new CommandManager();
        $data = [
            'name' => $infos['name'],
            'address' => $infos['address'],
            'total' => $this->totalCart(),
            'date' => date("Y-m-d")
        ];
        $commandManager->insert($data);

        try {
            $data = [
                'source' => $_POST['stripeToken'],
                'description' => $_POST['name'],
                'email' => $_POST['email']
            ];
            $customer = \Stripe\Customer::create($data);
            $charge = \Stripe\Charge::create([
                'amount' => $this->totalCart() * 100,
                'currency' => 'eur',
                'description' => 'Example charge',
                'customer' => $customer->id,
                'statement_descriptor' => 'Custom descriptor',
            ]);

            unset($_SESSION['cart']);
            unset($_SESSION['count']);
            $_SESSION['transaction'] = $charge->receipt_url;

            $sender = 'jenny.test4php@gmail.com';
            $recipient = 'jenny.viannay75@gmail.com';

            $subject = "Commande confirmée";
            $message = "Félicitation, vous recevrez votre commande dans un délai de 48h !";
            $headers = 'From:' . $sender;

            $isSend = mail($recipient, $subject, $message, $headers);
            if (!$isSend) {
                var_dump("Error: Message not accepted"); die;
            }

            header('Location:/home/success');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $e->getError();
        }
    }

    public function suggest():array
    {
        $articleManager = new ArticleManager();
        $articles = $articleManager->selectAll();

        $suggest = [];
        array_push($suggest, $articles[array_rand($articles, 1)]);
        array_push($suggest, $articles[array_rand($articles, 1)]);
        array_push($suggest, $articles[array_rand($articles, 1)]);
        array_push($suggest, $articles[array_rand($articles, 1)]);
        return $suggest;
    }
}