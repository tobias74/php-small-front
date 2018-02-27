<?php
namespace PhpSmallFront;

abstract class AbstractActionController
{

  public function __construct($request, $response, $renderer)
  {
    $this->_request = $request;
    $this->_response = $response;
    $this->_renderer = $renderer;

  }

  protected function getCurrentQueryString()
  {
    return http_build_query($_GET);
  }
  
  
  protected function renderAndKeepWorking($templateName, $data,$afterWorker)
  {
    $this->render($templateName, $data);
    $this->_response->setAfterWorker($afterWorker);
  }
  
  protected function render($templateName, $data)
  {
    $this->_response->setHtml($this->_renderer->render($templateName, $data));
  }


  public function getRequest()
  {
    return $this->_request;
  }

  public function getResponse()
  {
    return $this->_response;
  }

  
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
  
  
  protected function serveAsDownload($downloadName, $filePath)
  {
    $this->getResponse()->addHeader('Content-Disposition: inline; filename= '.$downloadName);
    $this->getResponse()->addHeader('Content-Length: '.filesize($filePath));
    $this->getResponse()->addHeader('Content-type: '.mime_content_type($filePath));
    $this->getResponse()->setFileName($filePath);
  }
  

}