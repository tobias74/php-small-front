<?php
namespace PhpSmallFront;

abstract class AbstractActionController
{

  public function __construct($routeParameters, $renderer)
  {
    $this->_routeParameters = $routeParameters;
    $this->_renderer = $renderer;

  }

  protected function startTimer()
  {
    $this->startTime = microtime(true);
  }
  
  protected function reportTimer()
  {
    $endTime = microtime(true);
    $duration = $endTime - $this->startTime;
    header('SMALL-FRONT-CONTROLLER-TIMER: '.$duration);
  }

  public function getParam($name, $default = false)
  {
    return (isset($this->_routeParameters[$name]) && ($this->_routeParameters[$name] !== '')) ? $this->_routeParameters[$name] : $default;
  }

  public function hasParam($name)
  {
    return (isset($this->_routeParameters[$name]) && ($this->_routeParameters[$name] !== ''));
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


  protected function getCurrentQueryString()
  {
    return http_build_query($_GET);
  }

  protected function render($templateName, $data)
  {
    return $this->_renderer->render($templateName, $data);
  }


  protected function httpIfModifiedSince($timestamp)
  {
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
    {
      if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $timestamp)
      {
        header('HTTP/1.0 304 Not Modified');
        exit;
      }
    }  
  }

  protected function httpRange()
  {
    if (isset($_SERVER['HTTP_RANGE'])) 
    {
      $range = $_SERVER['HTTP_RANGE'];

      list($param,$range) = explode('=',$range);
      if (strtolower(trim($param)) != 'bytes') 
      {
        header("HTTP/1.1 400 Invalid Request");
        exit;
      }
      
      $range = explode(',',$range);
      $range = explode('-',$range[0]);
      if (count($range) != 2) 
      {
        header("HTTP/1.1 400 Invalid Request");
        exit;
      }
      
      $start = intval($range[0]);
      $end = intval($range[1]);
    }
    else 
    {
      $start = false;
      $end = false;
    }
    
    return array($start,$end);
    
  }

  protected function passThroughUri($uri, $start=false, $end=false)
  {
    $headerString = "Accept-language: en\r\n"."Cookie: foo=bar\r\n"."Connection: close\r\n";
    if ($start && $end){
      $headerString.="Range: bytes=".$start."-".$end."\r\n";
    }

    $opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=> $headerString
      )
    );
    
    $context = stream_context_create($opts);
    $fp = fopen($uri, 'r', false, $context);
    fpassthru($fp);
    fclose($fp);
  }

























// old version.... above is new

  protected function sendFile($filePath)
  {
    $response = $this->getResponse();
    
    if (!file_exists($filePath))
    {
      throw new \ErrorException('File not found for sending: '.$filePath);
    }
    
    try
    {
      $fileTime = filemtime($filePath);

      $fileSize = filesize($filePath);

      $response->addHeader('Last-Modified: '.gmdate('D, d M Y H:i:s',$fileTime).' GMT',true,200);
      $response->setFileName($filePath);
      $response->addHeader('Cache-Control: maxage='.(60*60*24*31));
      $response->addHeader('Expires: '.gmdate('D, d M Y H:i:s',time()+60*60*24*31).' GMT',true,200);
      $response->addHeader('Content-type: '.mime_content_type($filePath));
    }
    catch (\Exception $e)
    {
      die('send back default video / (image). file with message to wait: '.$e->getMessage());
    }
    
  }
  
  protected function sendStream($stream)
  {
    $response = $this->getResponse();
    
    try
    {
      $response->addHeader('Last-Modified: '.gmdate('D, d M Y H:i:s',$fileTime).' GMT',true,200);
      $response->setStream($stream);
      //$response->addHeader('Content-type: '.mime_content_type($filePath));
    }
    catch (\Exception $e)
    {
      die('send back default video / (image). file with message to wait: '.$e->getMessage());
    }
    
  }
  
  protected function sendUri($downloadName, $uri)
  {
    $response = $this->getResponse();
    
    try
    {
      //$response->addHeader('Last-Modified: '.gmdate('D, d M Y H:i:s',$fileTime).' GMT',true,200);
      $response->setUri($uri);
      //$response->addHeader('Content-type: '.mime_content_type($filePath));
    }
    catch (\Exception $e)
    {
      die('send back default video / (image). file with message to wait: '.$e->getMessage());
    }
    
  }

  
  protected function serveAsDownload($downloadName, $filePath)
  {
    $this->getResponse()->addHeader('Content-Disposition: inline; filename= '.$downloadName);
    $this->getResponse()->addHeader('Content-Length: '.filesize($filePath));
    $this->getResponse()->addHeader('Content-type: '.mime_content_type($filePath));
    $this->getResponse()->setFileName($filePath);
  }

  protected function serveStreamAsDownload($downloadName, $stream)
  {
    $this->getResponse()->addHeader('Content-Disposition: inline; filename= '.$downloadName);
    //$this->getResponse()->addHeader('Content-Length: '.filesize($filePath));
    $this->getResponse()->addHeader('Content-type: '.mime_content_type($filePath));
    $this->getResponse()->setStream($stream);
  }
  

}
