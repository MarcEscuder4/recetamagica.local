<?php
// ERRORS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'cat';

switch ($lang) {
    case 'es':
        $locale = 'es_ES.UTF-8';
        break;
    case 'en':
        $locale = 'en_US.UTF-8';
        break;
    default:
        $locale = 'ca_ES.UTF-8';
        break;
}

putenv("LC_ALL=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain("messages", __DIR__ . '/../locales');
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");