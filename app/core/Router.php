<?php
/**
 * Simple MVC Router
 * Parses the URL and dispatches requests to the appropriate controller and method.
 */
class Router {
    protected string $currentController = DEFAULT_CONTROLLER . 'Controller';
    protected string $currentMethod = DEFAULT_METHOD;
    protected array $params = [];
    // Add a property to store the controller object
    protected object $controllerInstance;
    // Store custom route mappings
    protected array $routes = [];

    public function __construct() {
        // Load custom routes from config file
        $this->loadRoutes();
        
        // Get URL parts
        $url = $this->getUrl();
        
        // Check if this is a custom route first
        $requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        // Strip query string if present
        $requestUri = explode('?', $requestUri)[0];

        // Check if we have a custom route for this URI
        if ($this->tryCustomRoute($requestUri)) {
            return; // Route was processed, no need to continue
        }
        
        // Continue with standard controller/method routing
        // Url[0] = Controller name (e.g., 'Posts')
        if (isset($url[0]) && !empty($url[0])) {
            $controllerCandidate = ucwords(strtolower($url[0])) . 'Controller';
            $controllerPath = APPROOT . '/controllers/' . $controllerCandidate . '.php';

            if (file_exists($controllerPath)) {
                $this->currentController = $controllerCandidate;
                unset($url[0]);
            } else {
                // Controller not found - Option 1: Fallback to default (handled below)
                // Option 2: Show 404 immediately
                // $this->triggerNotFound("Controller not found: " . $controllerCandidate);
                // For now, let it try the default controller if specific one fails
            }
        }

        // Require the controller file (either the matched one or the default)
        $controllerFilePath = APPROOT . '/controllers/' . $this->currentController . '.php';
        if (!file_exists($controllerFilePath)) {
            $this->triggerNotFound("Default controller file not found: " . $controllerFilePath);
        }
        require_once $controllerFilePath;

        // Instantiate controller class
        if (!class_exists($this->currentController)) {
            $this->triggerNotFound("Controller class not found: " . $this->currentController);
        }
        // Instantiate the controller
        $this->controllerInstance = new $this->currentController;

        // Check for method in URL
        // Url[1] = Method name (e.g., 'edit')
        if (isset($url[1]) && !empty($url[1])) {
            $methodCandidate = strtolower($url[1]);
            // Check if method exists in the controller
            // Use method_exists and also check if it's callable (not private/protected)
            if (method_exists($this->controllerInstance, $methodCandidate) && is_callable([$this->controllerInstance, $methodCandidate])) {
                $this->currentMethod = $methodCandidate;
                unset($url[1]);
            } else {
                // Method not found or not accessible
                $this->triggerNotFound("Method not found or not accessible: " . $methodCandidate . " in controller " . get_class($this->controllerInstance));
            }
        } elseif (!method_exists($this->controllerInstance, $this->currentMethod) || !is_callable([$this->controllerInstance, $this->currentMethod])) {
             // Default method not found or not accessible
             $this->triggerNotFound("Default method not found or not accessible: " . $this->currentMethod . " in controller " . get_class($this->controllerInstance));
        }


        // Get params - Remaining parts of the URL
        // Re-index array after unsetting controller/method
        $this->params = $url ? array_values($url) : [];
    }

    /**
     * Calls the determined controller method with parameters.
     *
     * @return void
     */
    public function dispatch(): void {
        call_user_func_array([$this->controllerInstance, $this->currentMethod], $this->params);
    }

    /**
     * Parses the URL from the 'url' GET parameter set by .htaccess.
     *
     * @return array The URL parts as an array.
     */
    protected function getUrl(): array {
        // Debug log to troubleshoot URL parsing
        error_log("Processing URL in getUrl(). GET params: " . print_r($_GET, true));
        
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            // Sanitize URL
            $url = filter_var($url, FILTER_SANITIZE_URL);
            // Basic security check
            if (strpos($url, '..') !== false) {
                error_log("Security warning: Path traversal attempt in URL");
                return [];
            }
            error_log("URL after processing: " . $url);
            return explode('/', $url);
        }
        
        error_log("No URL parameter found, returning empty array");
        return []; // Default to empty for home page
    }

    /**
     * Handles 'Not Found' errors.
     * Logs the error and displays a user-friendly 404 page/message.
     *
     * @param string $message Error message for logging.
     * @return void
     */
     protected function triggerNotFound(string $message = "Resource not found."): void {
         error_log("Routing Error (404): " . $message);

         // Set header
         http_response_code(404);

         // Attempt to load a dedicated 404 view
         $viewPath = APPROOT . '/views/pages/errors/404.php';
         if (file_exists($viewPath)) {
             // You might want a minimal layout or no layout for error pages
             // For simplicity, just require the view directly here
             require_once $viewPath;
         } else {
             // Fallback if 404 view is missing
             echo "<h1>404 Not Found</h1>";
             echo "<p>The page you requested could not be found.</p>";
         }
         exit(); // Stop script execution
     }

    /**
     * Loads custom routes from the Routes.php config file
     */
    private function loadRoutes(): void {
        $routesFile = APPROOT . '/config/Routes.php';
        if (file_exists($routesFile)) {
            $this->routes = require $routesFile;
            error_log("Loaded " . count($this->routes) . " custom routes");
        } else {
            error_log("Routes file not found at: " . $routesFile);
            $this->routes = [];
        }
    }

    /**
     * Attempts to match the current request URI to a custom route
     * 
     * @param string $requestUri The current request URI
     * @return bool True if a custom route was matched and processed
     */
    private function tryCustomRoute(string $requestUri): bool {
        foreach ($this->routes as $routePattern => $routeConfig) {
            // Check if this is a parameterized route by looking for {param} pattern
            if (strpos($routePattern, '{') !== false) {
                // Convert the route pattern to a regex pattern
                $regexPattern = preg_replace('/{([^\/]+)}/', '([^/]+)', $routePattern);
                $regexPattern = '#^' . $regexPattern . '$#';
                
                // Try to match the request URI with our regex pattern
                if (preg_match($regexPattern, $requestUri, $matches)) {
                    // Extract parameter values
                    array_shift($matches); // Remove the full match
                    
                    // Extract parameter names from the original route pattern
                    preg_match_all('/{([^\/]+)}/', $routePattern, $paramNames);
                    $paramNames = $paramNames[1]; // Get the parameter names without the curly braces
                    
                    // Create an associative array of parameter names and values
                    $params = [];
                    foreach ($matches as $index => $value) {
                        if (isset($paramNames[$index])) {
                            $params[] = $value;
                        }
                    }
                    
                    $controller = $routeConfig['controller'] . 'Controller';
                    $method = $routeConfig['method'];
                    
                    error_log("Matched parameterized route: $requestUri -> $controller::$method with params: " . print_r($params, true));
                    
                    // Load the controller
                    $controllerPath = APPROOT . '/controllers/' . $controller . '.php';
                    if (!file_exists($controllerPath)) {
                        $this->triggerNotFound("Controller file not found for route '$requestUri': " . $controllerPath);
                        return true;
                    }
                    
                    require_once $controllerPath;
                    
                    // Instantiate controller
                    if (!class_exists($controller)) {
                        $this->triggerNotFound("Controller class not found for route '$requestUri': " . $controller);
                        return true;
                    }
                    
                    $this->controllerInstance = new $controller();
                    
                    // Check if the method exists and is callable
                    if (!method_exists($this->controllerInstance, $method) || 
                        !is_callable([$this->controllerInstance, $method])) {
                        $this->triggerNotFound("Method not found or not accessible for route '$requestUri': $method in controller " . get_class($this->controllerInstance));
                        return true;
                    }
                    
                    // Set properties and call the method with the extracted parameters
                    $this->currentController = $controller;
                    $this->currentMethod = $method;
                    $this->params = $params;
                    
                    $this->dispatch();
                    return true;
                }
            }
            
            // Simple exact matching for static routes
            elseif ($routePattern === $requestUri) {
                // We have a match! Extract controller and method
                $controller = $routeConfig['controller'] . 'Controller';
                $method = $routeConfig['method'];
                
                error_log("Matched custom route: $requestUri -> $controller::$method");
                
                // Load the controller
                $controllerPath = APPROOT . '/controllers/' . $controller . '.php';
                if (!file_exists($controllerPath)) {
                    $this->triggerNotFound("Controller file not found for route '$requestUri': " . $controllerPath);
                    return true; // Route was "processed" (with an error)
                }
                
                require_once $controllerPath;
                
                // Instantiate controller
                if (!class_exists($controller)) {
                    $this->triggerNotFound("Controller class not found for route '$requestUri': " . $controller);
                    return true;
                }
                
                $this->controllerInstance = new $controller();
                
                // Check if the method exists and is callable
                if (!method_exists($this->controllerInstance, $method) || 
                    !is_callable([$this->controllerInstance, $method])) {
                    $this->triggerNotFound("Method not found or not accessible for route '$requestUri': $method in controller " . get_class($this->controllerInstance));
                    return true;
                }
                
                // Set properties and call the method
                $this->currentController = $controller;
                $this->currentMethod = $method;
                $this->params = []; // No params for static routes
                
                $this->dispatch();
                return true;
            }
        }
        
        return false; // No matching custom route found
    }
}
?>