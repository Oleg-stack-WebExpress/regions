<?php 
define('TEMPLATE_DIR', get_template_directory_uri());

function get_assets_dir($url)
{
    return TEMPLATE_DIR . '/assets/' . $url;
}