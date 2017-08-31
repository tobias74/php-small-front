<?php
namespace PhpSmallFront;

class FrontController
{
  
  
  public function dispatch($request, $response)
  {
    $controller = $this->getController($request, $response);
    $actionName = $this->getActionName($request);
    $this->execute($controller,$actionName);
  }
  
  private function getController($request, $response)
  {
    $controllerName = ucfirst($request->getParam("controller",''));
    $controller = $this->getControllerProviders()[$controllerName]($request, $response);
    return $controller;
  }

  public function setControllerProviders($val)
  {
    $this->controllerProviders = $val;
  }
  
  protected function getControllerProviders()
  {
    return $this->controllerProviders;
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

  
  
  private function getActionName($request)
  {
    return $request->getParam('action','index');
  }
}


