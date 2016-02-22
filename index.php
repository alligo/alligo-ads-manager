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
            settype($tempval, $cast);
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

    /**
     * Return specific URL params
     *
     * @return String
     */
    private function _getUrlParams()
    {
        $vars = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
        $result = [];
        foreach ($vars AS $item) {
            $val = $this->_get($item);
            if ($val) {
                $result[$item] = $val;
            }
        }
        //var_dump($result);
        return (empty($result) ? '' : '&' . http_build_query($result));
    }

    /**
     * Prepare the selection of randon banners, and return success or failture
     * of this task
     *
     * @return  Boolean
     */
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
     * Is requested URL for the Admin?
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if (!isset($this->config->admin_token) || !$this->_get($this->config->admin_token)) {
            return false;
        }
        return true;
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
     * Print Admin HTML, used to help which code to share
     *
     */
    public function printAdminHtml()
    {
        $items = $this->config->banners;
        $html = [];
        $html[] = '<!doctype html>';
        $html[] = '<html lang="pt-BR">';
        $html[] = "<head>";
        $html[] = '  <title>Admin</title>';
        $html[] = '  <meta charset="UTF-8">';
        $html[] = '  <meta name="robots" content="noindex, follow"/>';
        $html[] = '  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">';
        $html[] = '</head>';
        $html[] = '<body>';
        $html[] = '<div class="container">';
        $html[] = '<div class="page-header"><h1>Alligo Ads Manager</h1></div>';
        $html[] = "<h2>Params</h2>";
        $html[] = "<form action=''>";
        $html[] = '<input type="hidden" name="' . $this->config->admin_token . '" value="1">';
        $html[] = '<p><label>Origem da campanha (utm_source)*: <input type="text" name="utm_source" value="' . $this->_get('utm_source') . '" required></label>';
        $html[] = '<p><label>Mídia da campanha (utm_medium)*: <input type="text" name="utm_medium" value="' . $this->_get('utm_medium') . '" required></label>';
        $html[] = '<p><label>Nome da campanha (utm_campaign)*: <input type="text" name="utm_campaign" value="' . $this->_get('utm_campaign') . '" required></label>';
        $html[] = '<p><label>Conteúdo da campanha (utm_content): <input type="text" name="utm_content" value="' . $this->_get('utm_content') . '"></label>';
        $html[] = '<p><label>Termo da campanha (utm_term): <input type="text" name="utm_term" value="' . $this->_get('utm_term') . '"></label>';
        $html[] = '<p><button type="submit" class="btn btn-success">Apply</button>';
        $html[] = "</form>";
        $html[] = "<h2>Code</h2>";
        foreach ($items AS $category => $values) {
            $html[] = $this->printAdminHTMLItem($values, $category);
        }

        $html[] = '</div>';
        $html[] = '</body>';
        $html[] = '</html>';
        echo implode(PHP_EOL, $html);
    }

    /**
     * Helper for printAdminHtml(). Print block for each group
     *
     * @param  String  $group
     * @param  String  $title
     * @return String
     */
    protected function printAdminHTMLItem($group, $title)
    {
        $html = [];
        $click_url = $this->config->adsbaseurl . '?cat=' . $title . $this->_getUrlParams();

        $html[] = '<h3>' . $title . ' <span class="badge">' . (empty($group) ? "zero" : count($group)) . '</span> </h3>';
        if (!empty($group)) {
            $bannernow = $group[0];
            //var_dump($bannernow);
            $html[] = '<textarea style="width: 100%; height: 130px;">';
            $html[] = '<!-- Banner ' . $title . '-->';
            $html[] = '<iframe width="' . $bannernow['banners']['width']
                . '" height="' . $bannernow['banners']['height'] . '" src="' . $click_url
                . '" style="width:' . $bannernow['banners']['width'] 
                . 'px; max-width: 100%; height: auto; border: 0" frameBorder="0" scrolling="no" frameBorder="0" scrolling="no"> </iframe>';
            $html[] = '</textarea>';
        }

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
        $customheadercode = empty($this->config->customheadercode) ? '' : $this->config->customheadercode;

        $html = <<< BANNER
<!doctype html>
<html lang="pt-BR">
  <head>
   <meta charset="UTF-8">
    <title>$name</title>
    <meta name="robots" content="noindex, follow"/>
    $customheadercode
    <style>
      * {
        margin: 0;
        padding: 0;
      }
      #ad {
        max-width: 100%;
        height: auto;
      }
    </style>
  </head>
  <body>
    <a href="$link" target="_parent">
      <img alt="$name" width="$width" height="$height" id="ad" alt="$name" src="$src"/>
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

        $errorinfo = '[' . date(DATE_ATOM) . '] URL: (' . "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" . ')'
            . ' Referer: (' . (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "empty") . ')'
            . ' Errors: (' . json_encode($this->errors) . ')' . "\r\n";

        if (!file_put_contents(__DIR__ . '/error.log', $errorinfo, FILE_APPEND)) {
            // cannot write to log
        }

        echo $html;
    }
}

$AAM = new AlligoAdsManager($config);
if ($AAM->isAdmin()) {
    $AAM->printAdminHtml();
} else if ($AAM->isOk()) {
    $AAM->printBanner();
} else {
    $AAM->raiseError();
}

