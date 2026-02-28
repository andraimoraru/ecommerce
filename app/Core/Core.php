<?php
namespace App\Core;

/*
 * App Core Class
 * Creates URL and loads core controller
 * URL FORMAT - /controller/method/params
 */
class Core {
    /**
     * Fully qualified controller class name (defaults to App\Controllers\Pages)
     * @var string
     */
    protected $currentController = 'App\\Controllers\\Pages';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->getUrl();

        // Determine controller class
        if (isset($url[0]) && class_exists('App\\Controllers\\' . ucwords($url[0]))) {
            $this->currentController = 'App\\Controllers\\' . ucwords($url[0]);
            unset($url[0]);
        }

        // Instantiate controller class (Composer will autoload it)
        $this->currentController = new $this->currentController();

        // Check for method
        if (isset($url[1]) && method_exists($this->currentController, $url[1])) {
            $this->currentMethod = $url[1];
            unset($url[1]);
        }

        // Get parameters
        $this->params = $url ? array_values($url) : [];

        // Call a callback with array of params
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        } else {
            return [];
        }
    }
}