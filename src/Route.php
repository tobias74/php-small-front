<?php

/*
In the making of this software I used parts of the router of the Zend Faremwork
According to the license I have copy-pasted the license text below:

New BSD License
Copyright (c) 2005-2017, Zend, a Rogue Wave Company. All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution. Neither the name of Zend or Rogue Wave Software, nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission. THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

namespace PhpSmallFront;

class Route
{
  protected $_urlDelimiter = "/";
  protected $_urlVariable = ":";
  protected $_parts = array();
  protected $_defaults = array();
  protected $_variables = array();
  protected $_staticCount = 0;
  protected $_methods;
    
  public function __construct($route, $defaults = array(), $methods = array('GET','POST','PUT','DELETE'))
  {
    $this->_methods = $methods;
    $route = trim($route, $this->_urlDelimiter);
    $this->_defaults = (array) $defaults;
    
    if ($route !== '') 
    {
      foreach (explode($this->_urlDelimiter, $route) as $pos => $part) 
      {
        if (substr($part, 0, 1) == $this->_urlVariable) 
        {
          $name = substr($part, 1);
          
          $this->_parts[$pos]     = null;
          $this->_variables[$pos] = $name;
        }
        else
        {
          $this->_parts[$pos] = $part;
          $this->_staticCount++;
        }
      }
    }
  }
  

  public function match($requestURI, $method = 'GET')
  {
    if (!in_array($method, $this->_methods))
    {
      return false;  
    }
    
    $pathStaticCount = 0;
    $values          = array();
    
    $requestURI = trim($requestURI, $this->_urlDelimiter);
    $byQuestionMark = explode('?', $requestURI);
    $path = $byQuestionMark[0];
    
    if ($path !== '') 
    {
      $pathParts = explode($this->_urlDelimiter, $path);
      
      if (count($pathParts) !== count($this->_parts))
      {
        return false;
      }
      
      foreach ($pathParts as $pos => $pathPart) 
      {

        $name     = $this->_variables[$pos] ?? null;
        $pathPart = urldecode($pathPart);
        $part = $this->_parts[$pos];
        

        // If it's a static part, match directly
        if ($name === null)
        {
          if ( $part != $pathPart )
          {
            return false;
          }
          else
          {
            $pathStaticCount++;
          }
        }
        else
        {
          $values[$name] = $pathPart;
        }

      }
    }

    // Check if all static mappings have been matched
    if ($this->_staticCount != $pathStaticCount) 
    {
        return false;
    }

    if (count($this->_variables) !== count($values))
    {
      return false;
    }

    return ($values + $this->_defaults);

  }

}




