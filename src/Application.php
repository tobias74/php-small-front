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

    private function getController($routeParameters)
    {
      $controllerName = ucfirst($routeParameters["controller"]);
      $controller = $this->controllerProviders[$controllerName]($routeParameters);
      return $controller;
    }


    public function run()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
        {
          return;
        }
        else 
        {
          $routeParameters = $this->routeManager->extractArgumentsFromURI($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

          $controller = $this->getController($routeParameters);
          $actionName = $routeParameters['action'];
      
          $controller->$actionName();

        }
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
    
    
    private function serveStreamResumable($stream)
    {
      header('Accept-Ranges: bytes');

      $fstatData = fstat($stream);
      $fileTime = $fstatData['mtime'];
      $filesize = $fstatData['size'];

      
      
      if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
      {
        if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $fileTime)
        {
          header('HTTP/1.0 304 Not Modified');
          exit;
        }
      }  
      
      if (isset($_SERVER['HTTP_RANGE'])) 
      {
        $range = $_SERVER['HTTP_RANGE'];
        $partial = true;

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
        
        if ($range[0] === '') 
        {
          $end = $filesize - 1;
          $start = $end - intval($range[0]);
        } 
        else if ($range[1] === '') 
        {
          $start = intval($range[0]);
          $end = $filesize - 1;
        } 
        else 
        {
          $start = intval($range[0]);
          $end = intval($range[1]);
          if ($end >= $filesize || (!$start && (!$end || $end == ($filesize - 1)))) 
          {
            header('Content-Length: '.$filesize);
            fpassthru($fp);
            exit;
          }
        }
        $length = $end - $start + 1;
        
        
        header('Content-Length: '.$length);
        header('HTTP/1.1 206 Partial Content');
        header("Content-Range: bytes $start-$end/$filesize");

        if ($start) 
        {
          fseek($fp,$start);
        }
        
        while ($length) 
        {
          $read = ($length > 8192) ? 8192 : $length;
          $length -= $read;
          print(fread($fp,$read));
        }
        
        fclose($fp);

      }
      else 
      {
        header('Content-Length: '.$filesize);
        fpassthru($fp);
      }
  

      // Exit here to avoid accidentally sending extra content on the end of the file
      exit;
      
      
    }


    private function serveFileResumable($file)
    {
      if (!file_exists($file)) {
        header("HTTP/1.1 404 Not Found");
        exit;
      }
  
      if (!$fp = fopen($file, 'r')) 
      {
        header("HTTP/1.1 500 Internal Server Error");
        exit;
      }
      else 
      {
        $this->serveStreamResumable($fp);  
      }

      
    }



    private function serveUriResumable($uri)
    {
      header('Accept-Ranges: bytes');

      //$fstatData = filemtime($uri);
      //$fileTime = filemtime($uri);
      //$filesize = filesize($uri);

    $fileTime = 0;
      
      
      if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
      {
        if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $fileTime)
        {
          header('HTTP/1.0 304 Not Modified');
          exit;
        }
      }  
      
      if (isset($_SERVER['HTTP_RANGE'])) 
      {
        $range = $_SERVER['HTTP_RANGE'];
        $partial = true;

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

        $this->curlUriWithRange($uri, $start, $end);

      }
      else 
      {
        $this->curlUriWithRange($uri);
      }
  

      // Exit here to avoid accidentally sending extra content on the end of the file
      exit;
      
      
    }


    private function curlUriWithRange($uri, $start=false, $end=false)
    {
    }


  public function sendResponse($response)
  {
    if ($response->isFile())
    {
      foreach($response->getHeaders() as $header)
      {
        header($header['header'],$header['replace'],$header['code']);
      }

      $this->serveFileResumable($response->getFileName());

    }
    else if ($response->isStream())
    {
      foreach($response->getHeaders() as $header)
      {
        header($header['header'],$header['replace'],$header['code']);
      }

      $this->serveStreamResumable($response->getStream());

    }
    else if ($response->isUri())
    {
      foreach($response->getHeaders() as $header)
      {
        header($header['header'],$header['replace'],$header['code']);
      }

      $this->serveUriResumable($response->getUri());

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

