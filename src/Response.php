<?php
namespace Zeitfaden\ServerContext;


class Response
{
  protected $_hash;
  protected $_enabled=true;
  protected $_headers=array();
  protected $_isFile = false;
  protected $fileName = "";
  protected $html = "";
  protected $afterWorker = false;
  
  public function __construct()
  {
    $this->_hash = array();
  }
  
  public function setAfterWorker($val)
  {
    $this->afterWorker = $val;
  }
  
  public function getAfterWorker()
  {
    return $this->afterWorker;
  }
  
  public function hasAfterWorker()
  {
    return ($this->getAfterWorker() != false);
  }
  
  public function setFileName($fileName)
  {
     $this->_isFile = true;
     $this->fileName = $fileName;
  }
  
  public function getFileName()
  {
     return $this->fileName;
  }

  public function setHtml($val)
  {
    $this->html = $val;
  }
  
  public function getHtml()
  {
    return $this->html;  
  }
  
  public function isHtml()
  {
    return !!$this->html;  
  }
  
  public function isFile()
  {
    return $this->_isFile;
  }
  
  public function disable()
  {
    $this->_enabled = false;
  }
  
  public function enable()
  {
    $this->_enabled = true;
  }
  
  
  
  
  public function addHeaders($headers)
  {
    foreach ($headers as $header)
    {
      $this->addHeader($header); 
    }
    
  }
  
  public function addHeader($header,$replace=true,$code=200)
  {
    if (is_array($header))
    {
      foreach($header as $h)
      {
        $this->_headers[] = array('header'=> $h,'replace' => $replace,'code' => $code);
      }
      error_log('we had an array in this place in reonse, where we were unsure if this is ok...');
      //throw new \ErrorException('wrong parameter in addHeader: '.print_r($header,true));
    }
    else 
    {
      $this->_headers[] = array('header'=> $header,'replace' => $replace,'code' => $code);
    }
  }
  
  public function getHeaders()
  {
    return $this->_headers;  
  }
  
  public function isEnabled()
  {
    return $this->_enabled;
  }
  
  public function appendValue($name, $value)
  {
    $this->_hash[$name] = $value;
  }
  
  public function hasValue($name)
  {
    if (isset($this->_hash[$name]))
    {
      return true;
    } 
    else
    {
      return false;
    }
  }
  public function getValue($name)
  {
    return $this->_hash[$name];
  }
  
  public function getHash()
  {
    return $this->_hash;
  }
  
  public function setHash($value)
  {
    $this->_hash = $value;
  }
  
}





