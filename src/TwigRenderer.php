<?php
namespace PhpSmallFront;


class TwigRenderer
{

    public function __construct($templateFolder)
    {
        $loader = new \Twig_Loader_Filesystem($templateFolder);
        $this->twig = new \Twig_Environment($loader);
        
        $function = new \Twig_SimpleFunction('is_selected', function ($a,$b) {
          if ($a === $b) {
            return "selected";
          }
          else {
            return "";
          }
        });
        $this->twig->addFunction($function);    
    
        $function = new \Twig_SimpleFunction('is_not_selected', function ($a) {
          if (!$a) {
            return "selected";
          }
          else {
            return "";
          }
        });
        $this->twig->addFunction($function);    
        
        
        $function = new \Twig_SimpleFunction('is_selected_in', function ($needle, $haystack) {
          if (!$haystack)
          {
            return "";
          }
          else 
          {
            if (array_search($needle, $haystack) !== false) {
              return "selected";
            }
            else {
              return "";
            }
          }
        });
        $this->twig->addFunction($function);    

        $function = new \Twig_SimpleFunction('is_checked', function ($a,$b) {
          if ($a === $b) {
            return " checked=\"checked\" ";
          }
          else {
            return "";
          }
        });
        $this->twig->addFunction($function);    

            
    }
    
    public function render($templateName, $data)
    {
        return $this->twig->render($templateName, $data);
    }
    
}