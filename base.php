<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

require 'config.php';

define ('VERSION', '1.1.2');
define ('DB', 'db');
date_default_timezone_set($config['default_timezone']);


function useServerSideRendering()
{
    global $config;
    return preg_match('/' . $config['cops_server_side_render'] . '/', $_SERVER['HTTP_USER_AGENT']);
}

function serverSideRender($data)
{
    // Get the templates
    $theme = getCurrentTemplate ();
    $header = file_get_contents('templates/' . $theme . '/header.html');
    $footer = file_get_contents('templates/' . $theme . '/footer.html');
    $main = file_get_contents('templates/' . $theme . '/main.html');
    $bookdetail = file_get_contents('templates/' . $theme . '/bookdetail.html');
    $page = file_get_contents('templates/' . $theme . '/page.html');

    // Generate the function for the template
    $template = new doT ();
    $dot = $template->template ($page, array ('bookdetail' => $bookdetail,
                                              'header' => $header,
                                              'footer' => $footer,
                                              'main' => $main));
    // If there is a syntax error in the function created
    // $dot will be equal to FALSE
    if (!$dot) {
        return FALSE;
    }
    // Execute the template
    if (!empty ($data)) {
        return $dot ($data);
    }

    return NULL;
}

function getQueryString()
{
    if (isset($_SERVER['QUERY_STRING'])) {
        return $_SERVER['QUERY_STRING'];
    }
    return "";
}

function notFound()
{
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    header('Status: 404 Not Found');

    $_SERVER['REDIRECT_STATUS'] = 404;
}

function getURLParam($name, $default = NULL)
{
    if (!empty ($_GET) && isset($_GET[$name]) && $_GET[$name] != '') {
        return $_GET[$name];
    }
    return $default;
}

function getCurrentOption($option)
{
    global $config;
    if (isset($_COOKIE[$option])) {
        if (isset($config ['cops_' . $option]) && is_array ($config ['cops_' . $option])) {
            return explode (',', $_COOKIE[$option]);
        } else {
            return $_COOKIE[$option];
        }
    }
    if (isset($config ['cops_' . $option])) {
        return $config ['cops_' . $option];
    }

    return '';
}

function getCurrentCss()
{
    return 'templates/' . getCurrentTemplate () . '/styles/style-' . getCurrentOption('style') . '.css';
}

function getCurrentTemplate()
{
    return getCurrentOption ('template');
}

function getUrlWithVersion($url)
{
    return $url . '?v=' . VERSION;
}

function xml2xhtml($xml)
{
    return preg_replace_callback('#<(\w+)([^>]*)\s*/>#s', function($m) {
        $xhtml_tags = array('br', 'hr', 'input', 'frame', 'img', 'area', 'link', 'col', 'base', 'basefont', 'param');
        if (in_array($m[1], $xhtml_tags)) {
            return '<' . $m[1] . $m[2] . ' />';
        } else {
            return '<' . $m[1] . $m[2] . '></' . $m[1] . '>';
        }
    }, $xml);
}

function display_xml_error($error)
{
    $return = '';
    $return .= str_repeat('-', $error->column) . "^\n";

    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= 'Warning ' . $error->code . ': ';
            break;
         case LIBXML_ERR_ERROR:
            $return .= 'Error ' . $error->code . ': ';
            break;
        case LIBXML_ERR_FATAL:
            $return .= 'Fatal Error ' . $error->code . ': ';
            break;
    }

    $return .= trim($error->message) .
               "\n  Line: " . $error->line .
               "\n  Column: " . $error->column;

    if ($error->file) {
        $return .= "\n  File: " . $error->file;
    }

    return "$return\n\n--------------------------------------------\n\n";
}

function are_libxml_errors_ok()
{
    $errors = libxml_get_errors();

    foreach ($errors as $error) {
        if ($error->code == 801) return false;
    }
    return true;
}

function html2xhtml($html)
{
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);

    $doc->loadHTML('<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' .
                        $html  . '</body></html>'); // Load the HTML
    $output = $doc->saveXML($doc->documentElement); // Transform to an Ansi xml stream
    $output = xml2xhtml($output);
    if (preg_match ('#<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></meta></head><body>(.*)</body></html>#ms', $output, $matches)) {
        $output = $matches [1]; // Remove <html><body>
    }
    /*
    // In case of error with summary, use it to debug
    $errors = libxml_get_errors();

    foreach ($errors as $error) {
        $output .= display_xml_error($error);
    }
    */

    if (!are_libxml_errors_ok ()) $output = 'HTML code not valid.';

    libxml_use_internal_errors(false);
    return $output;
}

/**
 * This method is a direct copy-paste from
 * http://tmont.com/blargh/2010/1/string-format-in-php
 */
function str_format($format)
{
    $args = func_get_args();
    $format = array_shift($args);

    preg_match_all('/(?=\{)\{(\d+)\}(?!\})/', $format, $matches, PREG_OFFSET_CAPTURE);
    $offset = 0;
    foreach ($matches[1] as $data) {
        $i = $data[0];
        $format = substr_replace($format, @$args[$i], $offset + $data[1] - 1, 2 + strlen($i));
        $offset += strlen(@$args[$i]) - 2 - strlen($i);
    }

    return $format;
}

/**
 * Get all accepted languages from the browser and put them in a sorted array
 * languages id are normalized : fr-fr -> fr_FR
 * @return array of languages
 */
function getAcceptLanguages()
{
    $langs = array();

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        // break up string into pieces (languages and q factors)
        $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        if (preg_match('/^(\w{2})-\w{2}$/', $accept, $matches)) {
            // Special fix for IE11 which send fr-FR and nothing else
            $accept = $accept . ',' . $matches[1] . ';q=0.8';
        }
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $accept, $lang_parse);

        if (count($lang_parse[1])) {
            $langs = array();
            foreach ($lang_parse[1] as $lang) {
                // Format the language code (not standard among browsers)
                if (strlen($lang) == 5) {
                    $lang = str_replace('-', '_', $lang);
                    $splitted = preg_split('/_/', $lang);
                    $lang = $splitted[0] . '_' . strtoupper($splitted[1]);
                }
                array_push($langs, $lang);
            }
            // create a list like "en" => 0.8
            $langs = array_combine($langs, $lang_parse[4]);

            // set default to 1 for any without q factor
            foreach ($langs as $lang => $val) {
                if ($val === '') $langs[$lang] = 1;
            }

            // sort list based on value
            arsort($langs, SORT_NUMERIC);
        }
    }

    return $langs;
}

/**
 * Find the best translation file possible based on the accepted languages
 * @return array of language and language file
 */
function getLangAndTranslationFile()
{
    global $config;
    $langs = array();
    $lang = 'en';
    if (!empty($config['cops_language'])) {
        $lang = $config['cops_language'];
    }
    elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $langs = getAcceptLanguages();
    }
    //echo var_dump($langs);
    $lang_file = NULL;
    foreach ($langs as $language => $val) {
        $temp_file = dirname(__FILE__). '/lang/Localization_' . $language . '.json';
        if (file_exists($temp_file)) {
            $lang = $language;
            $lang_file = $temp_file;
            break;
        }
    }
    if (empty ($lang_file)) {
        $lang_file = dirname(__FILE__). '/lang/Localization_' . $lang . '.json';
    }
    return array($lang, $lang_file);
}

/**
 * This method is based on this page
 * http://www.mind-it.info/2010/02/22/a-simple-approach-to-localization-in-php/
 */
function localize($phrase, $count=-1, $reset=false)
{
    global $config;
    if ($count == 0)
        $phrase .= '.none';
    if ($count == 1)
        $phrase .= '.one';
    if ($count > 1)
        $phrase .= '.many';

    /* Static keyword is used to ensure the file is loaded only once */
    static $translations = NULL;
    if ($reset) {
        $translations = NULL;
    }
    /* If no instance of $translations has occured load the language file */
    if (is_null($translations)) {
        $lang_file_en = NULL;
        list ($lang, $lang_file) = getLangAndTranslationFile();
        if ($lang != 'en') {
            $lang_file_en = dirname(__FILE__). '/lang/' . 'Localization_en.json';
        }

        $lang_file_content = file_get_contents($lang_file);
        /* Load the language file as a JSON object and transform it into an associative array */
        $translations = json_decode($lang_file_content, true);

        /* Clean the array of all unfinished translations */
        foreach (array_keys ($translations) as $key) {
            if (preg_match ('/^##TODO##/', $key)) {
                unset ($translations [$key]);
            }
        }
        if (!is_null($lang_file_en)) {
            $lang_file_content = file_get_contents($lang_file_en);
            $translations_en = json_decode($lang_file_content, true);
            $translations = array_merge ($translations_en, $translations);
        }
    }
    if (array_key_exists ($phrase, $translations)) {
        return $translations[$phrase];
    }
    return $phrase;
}

function addURLParameter($urlParams, $paramName, $paramValue)
{
    if (empty ($urlParams)) {
        $urlParams = '';
    }
    $start = '';
    if (preg_match ('#^\?(.*)#', $urlParams, $matches)) {
        $start = '?';
        $urlParams = $matches[1];
    }
    $params = array();
    parse_str($urlParams, $params);
    if (empty ($paramValue) && $paramValue != 0) {
        unset ($params[$paramName]);
    } else {
        $params[$paramName] = $paramValue;
    }
    return $start . http_build_query($params);
}

function useNormAndUp()
{
    global $config;
    return $config ['cops_normalized_search'] == '1';
}

function normalizeUtf8String($s)
{
    include_once 'transliteration.php';
    return _transliteration_process($s);
}

function normAndUp($s)
{
    return mb_strtoupper(normalizeUtf8String($s), 'UTF-8');
}
