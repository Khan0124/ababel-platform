<?php
namespace App\Core;

use Exception;

/**
 * Modern Router with Middleware Support
 * Handles routing with middleware chain and improved structure
 */
class Router
{
    private $routes = [];
    private $middleware = [];
    private $globalMiddleware = [];
    private $currentGroup = '';
    private $currentPrefix = '';
    
    public function __construct()
    {
        $this->loadRoutes();
    }
    
    /**
     * Add GET route
     */
    public function get($path, $handler, $middleware = [])
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    /**
     * Add POST route
     */
    public function post($path, $handler, $middleware = [])
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    /**
     * Add PUT route
     */
    public function put($path, $handler, $middleware = [])
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    /**
     * Add DELETE route
     */
    public function delete($path, $handler, $middleware = [])
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    /**
     * Add route group
     */
    public function group($prefix, $callback, $middleware = [])
    {
        $previousPrefix = $this->currentPrefix;
        $previousGroup = $this->currentGroup;
        
        $this->currentPrefix .= $prefix;
        $this->currentGroup = $prefix;
        
        $callback($this);
        
        $this->currentPrefix = $previousPrefix;
        $this->currentGroup = $previousGroup;
        
        return $this;
    }
    
    /**
     * Add middleware to group
     */
    public function middleware($middleware)
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        
        $this->middleware[$this->currentGroup] = $middleware;
        return $this;
    }
    
    /**
     * Add global middleware
     */
    public function use($middleware)
    {
        if (is_string($middleware)) {
            $middleware = [$middleware];
        }
        
        $this->globalMiddleware = array_merge($this->globalMiddleware, $middleware);
        return $this;
    }
    
    /**
     * Add route with method
     */
    private function addRoute($method, $path, $handler, $middleware = [])
    {
        $fullPath = $this->currentPrefix . $path;
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => array_merge(
                $this->globalMiddleware,
                $this->middleware[$this->currentGroup] ?? [],
                $middleware
            )
        ];
        
        return $this;
    }
    
    /**
     * Dispatch the request
     */
    public function dispatch($method, $uri)
    {
        $route = $this->findRoute($method, $uri);
        
        if (!$route) {
            $this->handleNotFound();
        }
        
        // Extract parameters
        $params = $this->extractParams($route['path'], $uri);
        
        // Execute middleware chain
        $this->executeMiddleware($route['middleware'], function() use ($route, $params) {
            $this->executeHandler($route['handler'], $params);
        });
    }
    
    /**
     * Find matching route
     */
    private function findRoute($method, $uri)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $uri)) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Match path with parameters
     */
    private function matchPath($routePath, $uri)
    {
        $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        
        return preg_match($routePattern, $uri);
    }
    
    /**
     * Extract parameters from URI
     */
    private function extractParams($routePath, $uri)
    {
        $params = [];
        
        // Extract parameter names from route
        preg_match_all('/\{([^}]+)\}/', $routePath, $paramNames);
        
        // Create pattern to match URI
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        // Extract values
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches); // Remove full match
            
            foreach ($paramNames[1] as $index => $name) {
                $params[$name] = $matches[$index] ?? null;
            }
        }
        
        return $params;
    }
    
    /**
     * Execute middleware chain
     */
    private function executeMiddleware($middleware, $next)
    {
        if (empty($middleware)) {
            $next();
            return;
        }
        
        $current = array_shift($middleware);
        $middlewareInstance = $this->createMiddleware($current);
        
        if ($middlewareInstance) {
            $middlewareInstance->setNext(new class($middleware, $next) extends Middleware {
                private $remainingMiddleware;
                private $next;
                
                public function __construct($middleware, $next)
                {
                    parent::__construct();
                    $this->remainingMiddleware = $middleware;
                    $this->next = $next;
                }
                
                protected function handle($request)
                {
                    $this->executeMiddleware($this->remainingMiddleware, $this->next);
                }
            });
            
            $middlewareInstance->process($_REQUEST);
        } else {
            $this->executeMiddleware($middleware, $next);
        }
    }
    
    /**
     * Create middleware instance
     */
    private function createMiddleware($middlewareName)
    {
        $middlewareMap = [
            'auth' => AuthMiddleware::class,
            'security' => SecurityMiddleware::class,
            'logging' => LoggingMiddleware::class,
            'error' => ErrorHandlingMiddleware::class
        ];
        
        if (isset($middlewareMap[$middlewareName])) {
            $className = $middlewareMap[$middlewareName];
            return new $className();
        }
        
        return null;
    }
    
    /**
     * Execute route handler
     */
    private function executeHandler($handler, $params)
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            $this->executeController($handler, $params);
        }
    }
    
    /**
     * Execute controller method
     */
    private function executeController($handler, $params)
    {
        list($controller, $method) = explode('@', $handler);
        
        $controllerClass = "App\\Controllers\\{$controller}";
        
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller {$controllerClass} not found");
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $method)) {
            throw new Exception("Method {$method} not found in controller {$controllerClass}");
        }
        
        // Inject parameters into controller
        $controllerInstance->params = $params;
        
        call_user_func_array([$controllerInstance, $method], $params);
    }
    
    /**
     * Handle 404 Not Found
     */
    private function handleNotFound()
    {
        http_response_code(404);
        
        if ($this->isAjaxRequest()) {
            echo json_encode(['error' => 'Page not found']);
        } else {
            include __DIR__ . '/../Views/errors/404.php';
        }
        
        exit;
    }
    
    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Load routes from configuration
     */
    private function loadRoutes()
    {
        $routesFile = __DIR__ . '/../../config/routes.php';
        
        if (file_exists($routesFile)) {
            $routes = require $routesFile;
            
            foreach ($routes as $route) {
                $this->addRoute(
                    $route['method'],
                    $route['path'],
                    $route['handler'],
                    $route['middleware'] ?? []
                );
            }
        }
    }
    
    /**
     * Get all routes (for debugging)
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
