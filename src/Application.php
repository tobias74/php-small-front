<?php
namespace PhpSmallFront;

class Application
{

    public function __construct($configData)
    {
        $this->routeManager = new RouteManager();
        foreach ($configData['routeConfiguration'] as $route) 
        {
          $this->routeManager->addRoute(new Route(
              $route['route'],
              array_intersect_key($route, ['controller' => true, 'action' => true]),
              $route['methods']
          ));
          
        }
    
        $this->controllerProviders = $configData['controllerProviders'];
    }
  
    public function produceEmptyResponse()
    {
      return new Response();
    }
  
    public function run($response)
    {
        $request = $this->getRequest();

        if ($request->getRequestMethod() === 'OPTIONS')
        {
          return $response;
        }

        $this->routeManager->analyzeRequest($request);

        $frontController = new FrontController();
        $frontController->setControllerProviders( $this->controllerProviders );
        $frontController->dispatch($request,$response);

        return $response;
    }
    
    public function publishResponse($response)
    {
        if ($response->hasAfterWorker())
        {
          ignore_user_abort(true);
          set_time_limit(0);
          ob_start();
        }
        
        if ($response->isEnabled())
        {
            $this->sendResponse($response);
        }
        
        if ($response->hasAfterWorker())
        {
          header('Connection: close');
          header('Content-Length: '.ob_get_length());
          ob_end_flush();
          ob_flush();
          flush();
        
          $response->getAfterWorker()();
        }
    }


  private function serve_file_resumable ($file) {

    // Make sure the files exists, otherwise we are wasting our time
    if (!file_exists($file)) {
      header("HTTP/1.1 404 Not Found");
      exit;
    }

    $fileTime = filemtime($file);

    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
    {
      error_log('we did get the http if modiefed...');
      if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $fileTime)
      {
        error_log('and we answered, not modified');
        header('HTTP/1.0 304 Not Modified');
        exit;
      }
      else
      {
        error_log('and we answered, yes modified, continue loading.');
      }
    }  







    // Get the 'Range' header if one was sent
    if (isset($_SERVER['HTTP_RANGE'])) $range = $_SERVER['HTTP_RANGE']; // IIS/Some Apache versions
    else if ($apache = apache_request_headers()) { // Try Apache again
      $headers = array();
      foreach ($apache as $header => $val) $headers[strtolower($header)] = $val;
      if (isset($headers['range'])) $range = $headers['range'];
      else $range = FALSE; // We can't get the header/there isn't one set
    } else $range = FALSE; // We can't get the header/there isn't one set

    // Get the data range requested (if any)
    $filesize = filesize($file);
    if ($range) {
      $partial = true;
      list($param,$range) = explode('=',$range);
      if (strtolower(trim($param)) != 'bytes') { // Bad request - range unit is not 'bytes'
        header("HTTP/1.1 400 Invalid Request");
        exit;
      }
      $range = explode(',',$range);
      $range = explode('-',$range[0]); // We only deal with the first requested range
      if (count($range) != 2) { // Bad request - 'bytes' parameter is not valid
        header("HTTP/1.1 400 Invalid Request");
        exit;
      }
      if ($range[0] === '') { // First number missing, return last $range[1] bytes
        $end = $filesize - 1;
        $start = $end - intval($range[0]);
      } else if ($range[1] === '') { // Second number missing, return from byte $range[0] to end
        $start = intval($range[0]);
        $end = $filesize - 1;
      } else { // Both numbers present, return specific range
        $start = intval($range[0]);
        $end = intval($range[1]);
        if ($end >= $filesize || (!$start && (!$end || $end == ($filesize - 1)))) $partial = false; // Invalid range/whole file specified, return whole file
      }
      $length = $end - $start + 1;
    } else $partial = false; // No range requested

    header('Accept-Ranges: bytes');

    // if requested, send extra headers and part of file...
    if ($partial) {
      header('Content-Length: '.$length);
      header('HTTP/1.1 206 Partial Content');
      header("Content-Range: bytes $start-$end/$filesize");
      if (!$fp = fopen($file, 'r')) { // Error out if we can't read the file
        header("HTTP/1.1 500 Internal Server Error");
        exit;
      }
      if ($start) fseek($fp,$start);
      while ($length) { // Read in blocks of 8KB so we don't chew up memory on the server
        $read = ($length > 8192) ? 8192 : $length;
        $length -= $read;
        print(fread($fp,$read));
      }
      fclose($fp);
    } else 
    {
      header('Content-Length: '.$filesize);
      readfile($file); // ...otherwise just send the whole file
    }

    // Exit here to avoid accidentally sending extra content on the end of the file
    exit;

  }

  public function getRequest()
  {
    $request = new Request();
    $request->setRequest($_REQUEST);
    $request->setServer($_SERVER);
    $request->setFiles($_FILES);
    return $request;
  }


  public function sendResponse($response)
  {
    if ($response->isFile())
    {
      foreach($response->getHeaders() as $header)
      {
        header($header['header'],$header['replace'],$header['code']);
      }

      $this->serve_file_resumable($response->getFileName());

    }
    else if ($response->isHtml())
    {
      foreach($response->getHeaders() as $header)
      {
        header($header['header'],$header['replace'],$header['code']);
      }

      echo $response->getHtml();      
    }
    else
    {
      header('Cache-Control: no-cache, must-revalidate');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Content-type: application/json');
      foreach($response->getHeaders() as $header)
      {
        header($header['header']); //,$header['replace'],$header['code']);
      }
      try
      {
        print(json_encode($response->getHash()));

        //$this->sendZipped(json_encode($response->getHash()));
      }
      catch (\ErrorException $e)
      {
        error_log('invalid hash: '.print_r($response->getHash(),true));
        throw $e;
      }

    }
  }

}

