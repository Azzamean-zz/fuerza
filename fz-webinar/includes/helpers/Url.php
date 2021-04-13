<?php


namespace fzwebinar\Helpers;


class Url
{
    private static $currentUrl;

    /**
     * Retorna a url atual
     */
    public static function getCurrentURl()
    {
        if (!isset(self::$currentUrl)) {
            self::$currentUrl = (isset($_SERVER['HTTPS'])
                && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                ."://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        }
        return self::$currentUrl;
    }

    /**
     * Adiciona um query a uma url
     *
     * @param $url
     * @param $name
     * @param $value
     *
     * @return string
     */
    public static function addQuery($url, $name, $value)
    {
        $url_parts = parse_url($url);
        if (isset($url_parts['query'])) { // Avoid 'Undefined index: query'
            parse_str($url_parts['query'], $params);
        } else {
            $params = array();
        }
        $params[$name] = $value;
        $url_parts['query'] = http_build_query($params);
        return http_build_url($url_parts);;
    }
}