<?php
namespace App\Core;

class View {
    public static function render(string $view, array $data = []): void {
        extract($data);
        $viewFile = __DIR__ . '/../../views/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View não encontrada: {$view}");
        }
        
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        echo $content;
    }
    
    public static function layout(string $layout, string $view, array $data = []): void {
        extract($data);
        
        $viewFile = __DIR__ . '/../../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \Exception("View não encontrada: {$view}");
        }
        
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        $layoutFile = __DIR__ . '/../../views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout não encontrado: {$layout}");
        }
        
        include $layoutFile;
    }
    
    public static function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    public static function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }
}
