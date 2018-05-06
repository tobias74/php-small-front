<?php
namespace PhpSmallFront;

class Application
{
  protected $_routes = array();

  public function __construct($configData)
  {
      foreach ($configData['routeConfiguration'] as $route) 
      {
        $this->addRoute(new Route(
            $route['route'],
            array_intersect_key($route, ['controller' => true, 'action' => true]),
            $route['methods']
        ));
        
      }
  
      $this->controllerProviders = $configData['controllerProviders'];
  }
  
  
  protected function addRoute($route)
  {
    array_push($this->_routes, $route);
  }

  protected function extractArgumentsFromURI($requestURI, $method)
  {
    foreach (array_reverse($this->_routes) as $route)
    {
      $result = $route->match($requestURI, $method);
      if ($result !== false)
      {
        return $result;
      }
    }

    throw new \ErrorException('the given URI did not match any route: '.$requestURI);
    //return array();
    
  }
  
  

  protected function execute($controller, $actionName)
  {
    if (!method_exists($controller, $actionName))
    {
      throw new \ErrorException("wrong Action? or what? Name:".$actionName);
    }
    
    $actionName = $actionName;
    $controller->$actionName();
  }

  public function run()
  {
      $params = $this->extractArgumentsFromURI($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
      $controllerName = ucfirst($params["controller"]);
      $controller = $this->controllerProviders[$controllerName]($params);
      $actionName = isset($params['action']) ? $params['action'] : 'index';
      $this->execute($controller,$actionName);
  }

}

