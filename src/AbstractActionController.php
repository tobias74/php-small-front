<?php
namespace PhpSmallFront;

abstract class AbstractActionController
{

  public function __construct($params, $renderer)
  {
    $this->_routeParameters = $params;
    $this->_renderer = $renderer;
  }

  public function getParam($name, $default = false)
  {
    return (isset($this->_routeParameters[$name]) && ($this->_routeParameters[$name] !== '')) ? $this->_routeParameters[$name] : $default;
  }
  
  public function hasParam($name)
  {
    return (isset($this->_routeParameters[$name]) && ($this->_routeParameters[$name] !== ''));
  }

  protected function getCurrentQueryString()
  {
    return http_build_query($_GET);
  }
  
  protected function render($templateName, $data)
  {
    return $this->_renderer->render($templateName, $data);
  }

  protected function startTimer()
  {
    $this->startTime = microtime(true);
  }
  
  protected function reportTimer()
  {
    $endTime = microtime(true);
    $duration = $endTime-$this->startTime;
    header('ZEITFADEN-TIMER: '.$duration);    
  }




}
