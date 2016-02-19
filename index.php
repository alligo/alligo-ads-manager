<?php
/**
 * @package    Alligo.AlligoAdsManager
 * @author     Emerson Rocha Luiz <emerson@alligo.com.br>
 * @copyright  Copyright (C) 2016 Alligo Ltda. All rights reserved.
 * @license    MIT. See LICENSE
 */
include_once 'config.php';
if (!isset($config)) {
    die("config.php not found");
}

class AlligoAdsManager
{

    protected $config = null;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function printBanner()
    {
        $name = 'titulo';
        $link = 'http://teste.com';
        $width = '468';
        $heigh = '60';
        $url = 'banners/banner-468x60-001.jpg';
        
        $html = <<< BANNER
<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8">
    <title>$name</title>
    <meta name="robots" content="noindex, follow"/>
    <style>
      * {
        margin: 0;
        padding: 0;
      }
      #gopleme {
        max-width: 100%;
        height: auto;
      }
    </style>
  </head>
  <body>
    <a href="$link" target="_parent">
      <img alt="$name" width="$width" height="$heigh" src="$url"/>
    </a>
  </body>
</html>      
BANNER;
        echo $html;
    }
}

$AAM = new AlligoAdsManager($config);
$AAM->printBanner();