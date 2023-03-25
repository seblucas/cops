<?php

function verifyLogin()
{
    global $config;
    if (!is_null($config['cops_basic_authentication']) &&
      is_array($config['cops_basic_authentication'])) {
        if (!isset($_SERVER['PHP_AUTH_USER']) ||
          (isset($_SERVER['PHP_AUTH_USER']) &&
            ($_SERVER['PHP_AUTH_USER'] != $config['cops_basic_authentication']['username'] ||
              $_SERVER['PHP_AUTH_PW'] != $config['cops_basic_authentication']['password']))) {
            return false;
        }
    }
    return true;
}
