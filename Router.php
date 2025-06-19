<?php

namespace MVC;

class Router
{
    public $getRoutes = [];
    public $postRoutes = [];
    protected $base = '';

    public function get($url, $fn)
    {
        $this->getRoutes[$url] = $fn;
    }

    public function post($url, $fn)
    {
        $this->postRoutes[$url] = $fn;
    }

    public function setBaseURL($base){
        $this->base = $base;
    }

    public function comprobarRutas()
    {
        if(isset($_GET['url'])) {
            $currentUrl = '/' . $_GET['url'];
        } else {
            $currentUrl = $_SERVER['REQUEST_URI'] ?? '/';
            
            if(strpos($currentUrl, '?') !== false) {
                $currentUrl = substr($currentUrl, 0, strpos($currentUrl, '?'));
            }
            
            if(!empty($this->base) && strpos($currentUrl, $this->base) === 0) {
                $currentUrl = substr($currentUrl, strlen($this->base));
            }
        }
        
        if(empty($currentUrl) || $currentUrl === '' || $currentUrl === $this->base) {
            $currentUrl = '/';
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }
        
        if ($fn) {
            call_user_func($fn, $this);
        } else {
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-type: application/json');
                echo json_encode([
                    "ERROR" => "ENDPOINT NO ENCONTRADO", 
                    "URL" => $currentUrl,
                    "RUTAS_DISPONIBLES" => array_keys($this->getRoutes)
                ]);
            } else {
                $this->render('pages/notfound');
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
            echo "<p>No se pudo encontrar la vista: $view.php en $viewPath</p>";
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