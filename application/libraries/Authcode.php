<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');        
class AuthCode     
{     
  var $image;     
  var $sBgcolor;     
  var $nWidth;     
  var $nHeight;     
  var $nLen;     
  var $bNoise;     
  var $nNoise;      
  var $bBorder;      
  var $aFontlist;
  function AuthCode()     
  {     
    $this->sBgcolor = "#FFFFFF";     
    $this->nWidth = 70;     
    $this->nHeight = 25;     
    $this->nLeftMargin = 5;     
    $this->nRightMargin = 5;     
    $this->nTopMargin = 3;     
    $this->nBottomMargin = 2;     
    $this->nLen = 4;     
    $this->bNoise = true;     
    $this->nNoisePoint = 50;     
    $this->nNoiseLine = 5;     
    $this->bBorder = true;     
         
    $this->aFontlist = "arial.ttf";     
  }     
    
  function OutputImg()     
  {     
    $this->image = "";     
    $this->image = imagecreate($this->nWidth, $this->nHeight);     
    $back = $this->getcolor($this->sBgcolor);     
    imagefilledrectangle($this->image, 0, 0, $this->nWidth, $this->nHeight, $back);     
    $size = ($this->nWidth - $this->nLeftMargin - $this->nRightMargin)/$this->nLen;     
    if($size>($this->nHeight - $this->nTopMargin - $this->nBottomMargin))     
     $size=$this->nHeight - $this->nTopMargin - $this->nBottomMargin;     
         
    $left = ($this->nWidth-$this->nLen*($size+$size/10))/2 + $this->nLeftMargin;     
    $code = "";     
    for ($i=0; $i<$this->nLen; $i++)     
    {     
     $randtext = rand(0, 9);     
     $code .= $randtext;     
     $textColor = imagecolorallocate($this->image, rand(0, 100), rand(0, 100), rand(0, 100));     
     $font = $this->aFontlist;      
     $randsize = rand($size-$size/10, $size+$size/10);     
     $location = $left+($i*$size+$size/10);     
     imagettftext($this->image, $randsize, rand(-18,18), $location, rand($size, $size+$size/5) + $this->nTopMargin, $textColor, $font, $randtext);      
    }     
    if($this->bNoise == true) $this->setnoise();     
    $_SESSION['AuthCode'] = md5($code);     
    $bordercolor = $this->getcolor("#FFFFFF");      
    if($this->bBorder==true) imagerectangle($this->image, 0, 0, $this->nWidth-1, $this->nHeight-1, $bordercolor);     
    header("Content-type: image/png");     
    imagepng($this->image);
    imagedestroy($this->image);
  }
  function setnoise()//设置噪点     
  {     
    for ($i=0; $i<$this->nNoiseLine; $i++){     
     $randColor = imagecolorallocate($this->image, rand(0, 255), rand(0, 255), rand(0, 255));     
     imageline($this->image, rand(0, $this->nWidth), rand(0, $this->nHeight), rand(0, $this->nWidth), rand(0, $this->nHeight), $randColor);     
    }     
         
    for ($i=0; $i<$this->nNoisePoint; $i++){     
     $randColor = imagecolorallocate($this->image, rand(0, 255), rand(0, 255), rand(0, 255));       
     imagesetpixel($this->image, rand(0, $this->nWidth), rand(0, $this->nHeight), $randColor);     
    }      
  }     
  function getcolor($color)//将Hex颜色格式转换成RGB格式     
  {     
     $color = eregi_replace ("^#","",$color);     
     $r = $color[0].$color[1];     
     $r = hexdec ($r);     
     $b = $color[2].$color[3];     
     $b = hexdec ($b);     
     $g = $color[4].$color[5];     
     $g = hexdec ($g);     
     $color = imagecolorallocate ($this->image, $r, $b, $g);      
     return $color;     
  }
}     
?>    