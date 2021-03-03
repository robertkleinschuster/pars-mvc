<?php
if (!function_exists('pars_debug')) {
    function pars_debug($obj, $exit = false) {
        \Pars\Helper\Debug\DebugHelper::trace($obj);
        if ($exit) {
            echo \Pars\Helper\Debug\DebugHelper::getDebug();
            exit;
        }
    }
}
