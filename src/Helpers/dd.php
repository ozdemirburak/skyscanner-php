<?php

use Symfony\Component\VarDumper\VarDumper;

if (! function_exists('dd')) {
    /**
     * Dump all arguments and die
     */
    function dd()
    {
        array_map(function ($x) {
            VarDumper::dump($x);
        }, func_get_args());
        exit(1);
    }
}
