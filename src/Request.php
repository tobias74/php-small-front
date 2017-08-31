<?php
namespace PhpSmallFront;

class Request
{
  protected $_request;
  protected $_session;
  protected $_files;
  protected $_server;

  public function __construct()
  {
    
  }
  
  public function setSession($val)
  {
    $this->_session = $val;
  }

  public function getSession()
  {
    return $this->_session; 
  }
  
  public function setServer($val)
  {
    $this->_server = $val;
  }
  
  public function setRequest($val)
  {
    $this->_request = $val;
  }
  
  public function getRequest()
  {
    return $this->_request;
  }
  
  public function getController()
  {
    return $this->_request['controller'];
  }
  
  public function getAction()
  {
    return $this->_request['action'];
  }
  
  public function getParams()
  {
    $myArray = $this->_request;
    unset($myArray['controller']);
    unset($myArray['action']);
    $myArray = array_merge($myArray);
    return $myArray;
  }
  
  public function setFiles($val)
  {
    $this->_files = $val;
  }
  
  public function getRequestURI()
  {
    return isset($this->_server['REQUEST_URI']) ? $this->_server['REQUEST_URI'] : "";
  }
  
  public function getRequestMethod()
  {
    return isset($this->_server['REQUEST_METHOD']) ? $this->_server['REQUEST_METHOD'] : "";    
  }
  
  public function addArguments($hash)
  {
    $this->_request = array_merge($this->_request, $hash);
  }
  

  public function getParam($name, $default = false)
  {
    return (isset($this->_request[$name]) && ($this->_request[$name] !== '')) ? $this->_request[$name] : $default;
  }
  
  public function setParam($name, $value)
  {
    $this->_request[$name] = $value;
  }
  
  public function hasParam($name)
  {
    return (isset($this->_request[$name]) && ($this->_request[$name] !== ''));
  }

  public function getSessionVar($name, $default)
  {
    return isset($this->_session[$name]) ? $this->_session[$name] : $default;
  }

  public function setSessionVar($name, $val)
  {
    $this->_session[$name] = $val;
  }
  
  
  public function hasUploadedFile($inputName)
  {
    if (isset($this->_files[$inputName]['tmp_name']) && ($_FILES[$inputName]['tmp_name'] != "")) 
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
    if (isset($this->_files[$inputName]['tmp_name']) && ($_FILES[$inputName]['tmp_name'] != "")) 
    {
      $input_file       = $_FILES[$inputName]['tmp_name'];
      $input_file_name  = $_FILES[$inputName]['name'];
      $input_file_size  = $_FILES[$inputName]['size'];
      $input_file_type  = $_FILES[$inputName]['type'];
      $input_file_error = $_FILES[$inputName]['error'];
      
      return $this->_files[$inputName];
    }
    else
    {
      throw new ZeitfadenException('file not uploaded');
    }
    
  }
  
}
