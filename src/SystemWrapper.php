<?php 
namespace Zeitfaden\ServerContext;

class SystemWrapper
{
  
  public function __construct()
  {
    if (count(func_get_args()) > 0)
    {
      throw new ErrorException('why did you call this with paramters? its the system wrapper....');
    }
  }
  
  public function mkdir($one,$two,$three)
  {
      $result = mkdir($one,$two,$three);
            
      return $result;
  }
  
  public function __call($name, $params)
  {
    return call_user_func_array($name, $params);
  }
  
  public function backgroundPost($url)
  {
    $parts=parse_url($url);
  
    $fp = fsockopen($parts['host'],
            isset($parts['port'])?$parts['port']:80,
            $errno, $errstr, 30);
  
    if (!$fp) 
    {
        return false;
    } 
    else 
    {
        $query = isset($parts['query']) ? $parts['query'] : "";
        
        $out = "POST ".$parts['path']." HTTP/1.1\r\n";
        $out.= "Host: ".$parts['host']."\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: ".strlen($query)."\r\n";
        $out.= "Connection: Close\r\n\r\n";
        $out.= $query;
  
        fwrite($fp, $out);
        fclose($fp);
        return true;
    }
  }
  
  //Example of use
  //backgroundPost('http://example.com/slow.php?file='.urlencode('some file.dat'));
  
}

