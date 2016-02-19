<?php
/**
 * @package    Alligo.AlligoAdsManager
 * @author     Emerson Rocha Luiz <emerson@alligo.com.br>
 * @copyright  Copyright (C) 2016 Alligo Ltda. All rights reserved.
 * @license    MIT. See LICENSE
 */
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


include_once 'config.php';
if (!isset($config)) {
    die("config.php not found");
}

class AlligoAdsManager
{

    protected $errors = [];
    protected $config = null;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * A bit more save way to $_GET variables
     * 
     * @todo   make it more simple (fititnt, 2016-02-19 08:27)
     *
     * @param  String  $name
     * @param  Mixed   $default
     * @param  String  $cast
     * @param  Integer $max_len
     * @return Mixed
     */
    private function _get($name, $default = false, $cast = 'string', $max_len = 32)
    {
        $value = $default;
        if (filter_input(INPUT_GET, $name)) {
            $value = filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING);
            //echo $tempval;
            settype($tempval, $cast);
            //var_dump($tempval, $cast, settype($tempval, $cast));
            //echo $value;
        }
        return $value;
    }

    private function _prepare()
    {
        $cat = $this->_get('cat');
        if (empty($config->banners[$cat])) {
            $this->errors[] = "Category not found (" . (empty($cat) ? "empty" : $cat) . ").";
        }
        return count($this->errors) ? false : true;
    }

    /**
     * Can we print a banner?
     *
     * @return boolean
     */
    public function isOk()
    {
        return $this->_prepare();
    }

    /**
     * Echo banner html and headers
     */
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
        header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
        echo $html;
    }

    /**
     * If we had one error, trow now
     */
    public function raiseError()
    {
        $html = '<!-- Errors: ' . json_encode($this->errors) . '-->';
        echo $html;
    }
}

$AAM = new AlligoAdsManager($config);
if ($AAM->isOk()) {
    $AAM->printBanner();
} else {
    $AAM->raiseError();
}

