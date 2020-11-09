<?php

namespace App\Controller;

use App\Model\RoleManager;
use App\Model\UserManager;

class SecurityController extends AbstractController
{
    public function login()
    {
        $roleManager = new RoleManager();
        $userManager = new UserManager();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['email']) && !empty($_POST['password'])) {
                $user = $userManager->search($_POST['email']);
                if ($user) {
                    if ($user->password === md5($_POST['password'])) {
                        $_SESSION['username'] = $user->email;
                        $_SESSION['id'] = $user->id;
                        $_SESSION['role'] = $roleManager->selectOneById($user->role_id)['name'];
                        header('Location:/home/articles');
                    } else {
                        $_SESSION['flash_message'] = ["Password wrong !"];
                        header('Location:/security/login');
                    }
                } else {
                    $_SESSION['flash_message'] = ['User not found'];
                    header('Location:/security/login');
                }
            } else {
                $_SESSION['flash_message'] = ['Tous les champs sont obligatoires !'];
                header('Location:/security/login');
            }
        }
        return $this->twig->render('Security/login.html.twig');
    }

    public function register()
    {
        $userManager = new UserManager();
        $roleManager = new RoleManager();
        $error = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['email']) &&
                !empty($_POST['username']) &&
                !empty($_POST['password']) &&
                !empty($_POST['password2'])) {
                $user = $userManager->search($_POST['email']);
                if ($user) {
                    $error = true;
                    $_SESSION['flash_message'] = ['Email already exist'];
                    header('Location:/security/register');
                }
                if ($_POST['password'] != $_POST['password2']) {
                    $error = true;
                    $_SESSION['flash_message'] = ['Password do not match'];
                    header('Location:/security/register');
                }
                if ($error === false) {
                    $role = $roleManager->getRoleUser();
                    $user = [
                        'email' => $_POST['email'],
                        'username' => $_POST['username'],
                        'password' => md5($_POST['password']),
                        'role_id' => $role['id']
                    ];
                    $idUser = $userManager->insert($user);
                    if ($idUser) {
                        $_SESSION['username'] = $user['email'];
                        $_SESSION['id'] = $idUser;
                        $_SESSION['role'] = $roleManager->selectOneById($user['role_id'])['name'];
                        header('Location:/home/index');
                    }
                }
            }
        }
        return $this->twig->render('Security/register.html.twig', [
            'error' => $error
        ]);
    }

    public function logout()
    {
        session_destroy();
        header('Location:/');
    }
}
