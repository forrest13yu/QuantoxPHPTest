<?php

function router($httpMethods, $route, $callback, $exit = true)
{
    static $path = null;
    var_dump('1 '.$route);
    if ($path === null) {
        $path = parse_url($_SERVER['REQUEST_URI'])['path'];
        var_dump('2 '.$path);
        $scriptName = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
        var_dump('3 '.$scriptName);
        $len = strlen($scriptName);
        if ($len > 0 && $scriptName !== '/') {
            $path = substr($path, $len);
        }
        var_dump('4 '.$path);
    }

    if (!in_array($_SERVER['REQUEST_METHOD'], (array) $httpMethods)) {
        return;
    }


    $matches = null;

    $regex = '/' . str_replace('/', '\/', '^'.$route.'$') . '/';

    if (!preg_match_all($regex, $path, $matches)) {
        return;
    }

    if (empty($matches)) {
        $callback();
    } else {
        $params = array();
        foreach ($matches as $k => $v) {
            if (!is_numeric($k) && !isset($v[1])) {
                $params[$k] = $v[0];
            }
        }
        $callback($params);
    }

    if ($exit) {
        exit;
    }
}
