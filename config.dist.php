<?php
/**
 * Copy this file from config.dist.php to config.php to start using.
 * 
 * $config->adsbaseurl:   Base URL of this script (not banner destination)
 * $config->admin_token:  Any administration token (only for you) that is used to preview administration code
 *                        Add at the end of your URL myadsite.com/?myadmintoken=1 to see
 * $config->customheadercode:    Code to be added at head of HTML that shows the banners. You can put AdSense tracking
 *                               code here. Requires <script> tag
 */

$config = new stdClass();

$config->adsbaseurl = 'http://myadsite.com/';
$config->admin_token = 'help';
$config->customheadercode = '';


// Categories
$config->banners = [
    'category-a' => [],
    'banners-b' => [],
    'group-c' => [],
];

$config->banners['category-a'][] = [
    'name' => 'Examples of banners of Category A',
    'url' => 'http://www.capitalsexy.com.br',
    'banners' => [
        'width' => '468',
        'height' => '60',
        'src' => 'banners/banner-468x60-001.jpg'
    ]
];

// @todo add examples