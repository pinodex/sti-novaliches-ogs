<?php

use Sami\Parser\Filter\TrueFilter;

$sami = new Sami\Sami(dirname(__DIR__) . '/app/', array(
    'build_dir' => dirname(__DIR__) . '/docs/',
    'cache_dir' => dirname(__DIR__) . '/cache/sami/'
));

$sami['filter'] = function () {
    return new TrueFilter();
};

return $sami;
