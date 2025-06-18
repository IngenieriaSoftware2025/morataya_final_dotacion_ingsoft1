<?php

namespace MVC;

class Router
{
    public $getRoutes = [];
    public $postRoutes = [];
    protected $base = '';

    public function get($url, $fn)
    {
        $this->getRoutes[$this->base . $url] = $fn;
    }

    public function post($url, $fn)
    {
        $this->postRoutes[$this->base .$url] = $fn;
    }

    public function setBaseURL($base){
        $this->base = $base;
    }

    public function comprobarRutas()
    {
        // Obtener la URL desde parámetro GET si no hay .htaccess
        if(isset($_GET['url'])) {
            $currentUrl = $this->base . '/' . $_GET['url'];
        } else {
            $currentUrl = $_SERVER['REQUEST_URI'] ? str_replace("?" . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']) : $this->base .'/';
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }
        
        if ( $fn ) {
            // Call user fn va a llamar una función cuando no sabemos cual sera
            call_user_func($fn, $this); // This es para pasar argumentos
        } else {
            if( empty($_SERVER['HTTP_X_REQUESTED_WITH'])){
                $this->render('pages/notfound');
            } else {
                header('Content-type: application/json');
                echo json_encode(["ERROR" => "PÁGINA NO ENCONTRADA"]);
            }
        }
    }

    public function render($view, $datos = [], $layout = 'layout')
    {
        // Leer lo que le pasamos a la vista
        foreach ($datos as $key => $value) {
            $$key = $value;  // Variable variable
        }

        ob_start(); // Almacenamiento en memoria durante un momento...

        // Incluir la vista
        $viewPath = __DIR__ . "/views/$view.php";
        if (file_exists($viewPath)) {
            include_once $viewPath;
        } else {
            echo "<h1>Error: Vista no encontrada</h1>";
            echo "<p>No se pudo encontrar la vista: $view.php</p>";
        }
        
        $contenido = ob_get_clean(); // Limpia el Buffer
        
        // Incluir el layout correspondiente
        $layoutPath = __DIR__ . "/views/layouts/$layout.php";
        if (file_exists($layoutPath)) {
            include_once $layoutPath;
        } else {
            // Fallback al layout por defecto
            $defaultLayoutPath = __DIR__ . '/views/layouts/layout.php';
            if (file_exists($defaultLayoutPath)) {
                include_once $defaultLayoutPath;
            } else {
                // Si no hay layout, mostrar solo el contenido
                echo $contenido;
            }
        }
    }

    public function load($view, $datos = []){
        foreach ($datos as $key => $value) {
            $$key = $value;  // Variable variable
        }

        ob_start(); // Almacenamiento en memoria durante un momento...

        // Incluir la vista
        $viewPath = __DIR__ . "/views/$view.php";
        if (file_exists($viewPath)) {
            include_once $viewPath;
        } else {
            return "<h1>Error: Vista no encontrada</h1>";
        }
        
        $contenido = ob_get_clean(); // Limpia el Buffer
        return $contenido;
    }

    public function printPDF($ruta){
        header("Content-type: application/pdf");
        header("Content-Disposition: inline; filename=filename.pdf");
        @readfile(__DIR__ . '/storage/' . $ruta );
    }
}