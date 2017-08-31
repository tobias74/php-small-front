<?php
namespace Zeitfaden\ServerContext;

class RouteManager
{

  protected $_routes = array();
  
  public function addRoute($route)
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
  
  public function analyzeRequest($request)
  {
    $args = $this->extractArgumentsFromURI($request->getRequestURI(), $request->getRequestMethod());
    $request->addArguments($args);
  }
  
}




