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

  
  

}
