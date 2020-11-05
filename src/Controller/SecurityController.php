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
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['email']) && !empty($_POST['password'])) {
                $user = $userManager->search($_POST['email']);
                if ($user) {
                    if ($user->password === md5($_POST['password'])) {
                        $_SESSION['username'] = $user->email;
                        $_SESSION['id'] = $user->id;
                        $_SESSION['role'] = $roleManager->selectOneById($user->role_id)['name'];
                        header('Location:/admin/index');
                    } else {
                        $_SESSION['flash_message'] = ["Password incorrect !"];
                    }
                } else {
                    $_SESSION['flash_message'] = ['User not found'];
                }
            } else {
                $_SESSION['flash_message'] = ['Tous les champs sont obligatoires !'];
            }
        }
        return $this->twig->render('Admin/login.html.twig', [
            'error' => $error
        ]);
    }

    public function register()
    {
        $userManager = new UserManager();
        $roleManager = new RoleManager();
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['email']) &&
                !empty($_POST['username']) &&
                !empty($_POST['password']) &&
                !empty($_POST['password2'])) {
                $user = $userManager->search($_POST['email']);
                if ($user) {
                    $_SESSION['flash_message'] = ['Email already exist'];
                }
                if ($_POST['password'] != $_POST['password2']) {
                    $_SESSION['flash_message'] = ['Password do not match'];
                }
                if ($error === null) {
                    $user = [
                        'email' => $_POST['email'],
                        'username' => $_POST['username'],
                        'password' => md5($_POST['password']),
                        'role_id' => $roleManager->getRoleUserId()
                    ];
                    $idUser = $userManager->insert($user);
                    if ($idUser) {
                        $_SESSION['username'] = $user->email;
                        $_SESSION['id'] = $user->id;
                        $_SESSION['role_id'] = $user->role_id;
                        header('Location:/admin/index');
                    }
                }
            }
        }
        return $this->twig->render('Admin/register.html.twig', [
            'error' => $error
        ]);
    }

    public function logout()
    {
        session_destroy();
        header('Location:/');
    }
}
