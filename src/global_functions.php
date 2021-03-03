<?php
if (!function_exists('pars_debug')) {
    function pars_debug($obj) {
        \Pars\Helper\Debug\DebugHelper::trace($obj);
    }
}
