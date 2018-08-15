<?php
namespace PhpSmallFront;

abstract class AbstractActionController
{

  public function __construct($params, $renderer, $session)
  {
    $this->_routeParameters = $params;
    $this->_renderer = $renderer;
    $this->_session = $session;
  }

  public function getSession()
  {
    return $this->_session;  
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

  public function hasUploadedFile($inputName)
  {
    if (isset($_FILES[$inputName]['tmp_name']) && ($_FILES[$inputName]['tmp_name'] != "")) 
    {
      return true;
    }
    else
    {
      return false;
    }
  }
  
  public function getUploadedFile($inputName)
  {
    if (isset($_FILES[$inputName]['tmp_name']) && ($_FILES[$inputName]['tmp_name'] != "")) 
    {
      return $_FILES[$inputName];
    }
    else
    {
      throw new \Exception('file not uploaded');
    }
    
  }


}
