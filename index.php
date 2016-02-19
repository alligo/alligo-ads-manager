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
    protected $banner_now = null;

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

    private function _getRandonBanner($cat)
    {
        $key = array_rand($this->config->banners[$cat]);
        //var_dump($this->config->banners[$cat], $key);
        $this->banner_now = $this->config->banners[$cat][$key];
        if (empty($this->banner_now['name']) || empty($this->banner_now['url']) || empty($this->banner_now['banners'])) {
            $this->errors[] = "Banner configuration is not valid (" . (json_encode($this->banner_now)) . ").";
            //print_r($this->banner_now);
            return false;
        }
        return $this->banner_now;
    }

    private function _prepare()
    {
        $cat = $this->_get('cat');
        if (empty($this->config->banners[$cat])) {
            $this->errors[] = "Category not found (" . (empty($cat) ? "empty" : $cat) . ").";
        } else {
            $this->_getRandonBanner($cat);
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

    public function isAdmin()
    {
        if (!isset($this->config->admin_term) || !$this->_get($this->config->admin_term)) {
            return false;
        }
        return true;
    }

    public function printHtml()
    {
        $items = $this->config->banners;
        $html = [];
        $html[] = '<!doctype html>';
        $html[] = '<html lang="pt-BR">';
        $html[] = "<head>";
        $html[] = '  <title>Admin</title>';
        $html[] = '  <meta charset="UTF-8">';
        $html[] = '  <meta name="robots" content="noindex, follow"/>';
        $html[] = '</head>';
        $html[] = '<body>';
        $html[] = "<h1>Alligo Ads Manager</h1>";
        foreach ($items AS $category => $values) {
            $html[] = $this->printHTMLItem($values, $category);
        }


        $html[] = '</body>';
        $html[] = '</html>';
        echo implode(PHP_EOL, $html);
    }

    protected function printHTMLItem($group, $title)
    {
        $html = [];
        //var_dump($group);

        $html[] = "<h2>$title</h2>";
        $html[] = '<p>Qtd: ' . (empty($group) ? "zero" : count($group)) . ' </p>';
        $html[] = '<textarea style="width: 760px; height: 100px;">';
        $html[] = '<!-- Banner, start -->';
        $html[] = '<iframe src="' . $this->config->adsbaseurl . '?cat=' . $title . '" frameBorder="0" scrolling="no"> </iframe>';
        $html[] = '<!-- Banner, end -->';
        $html[] = '</textarea>';

        return implode(PHP_EOL, $html);
    }

    /**
     * Echo banner html and headers
     */
    public function printBanner()
    {
        $name = $this->banner_now['name'];
        $link = $this->banner_now['url'];
        //var_dump($this->banner_now['banners']);
        $width = $this->banner_now['banners']['width'];
        $height = $this->banner_now['banners']['height'];
        $src = $this->banner_now['banners']['src'];

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
      <img alt="$name" width="$width" height="$height" src="$src"/>
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
        //var_dump($this->config);
        echo $html;
    }
}

$AAM = new AlligoAdsManager($config);
if ($AAM->isOk()) {
    if ($AAM->isAdmin()) {
        $AAM->printHtml();
    } else {
        $AAM->printBanner();
    }
} else {
    $AAM->raiseError();
}

