<?php

namespace Controllers;

use MVC\Router;

class AppController {
    
    public static function index(Router $router){
        // session_start();
        // if(isset($_SESSION['login']) && $_SESSION['login']) {
        //     header('Location: /');
        //     exit;
        // }
        
        // if(isset($_GET['login'])) {
        //     header('Location: /login');
        //     exit;
        // }
        
        $router->render('pages/index', [], 'principal');
    }
}