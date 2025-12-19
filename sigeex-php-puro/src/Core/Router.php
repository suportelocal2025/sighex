<?php
namespace App\Core;

class Router {
    private array $routes = [];
    private array $middlewares = [];
    
    public function add(string $method, string $path, callable|array $handler, array $middlewares = []): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }
    
    public function get(string $path, callable|array $handler, array $middlewares = []): void {
        $this->add('GET', $path, $handler, $middlewares);
    }
    
    public function post(string $path, callable|array $handler, array $middlewares = []): void {
        $this->add('POST', $path, $handler, $middlewares);
    }
    
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';
        
        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                
                // Execute middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $result = $middleware();
                    if ($result === false) {
                        return;
                    }
                }
                
                // Execute handler
                if (is_array($route['handler'])) {
                    [$class, $method] = $route['handler'];
                    $controller = new $class();
                    call_user_func_array([$controller, $method], $matches);
                } else {
                    call_user_func_array($route['handler'], $matches);
                }
                return;
            }
        }
        
        http_response_code(404);
        echo "Página não encontrada";
    }
    
    private function convertToRegex(string $path): string {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
