<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit430c49f36b741f9836c2f39ed5bfc915
{
    public static $classMap = array (
        'Dplus\\Codes\\Base' => __DIR__ . '/../..' . '/src/Base.php',
        'Dplus\\Codes\\Mgl\\Dtm' => __DIR__ . '/../..' . '/src/mgl/Dtm.php',
        'Dplus\\Codes\\Mgl\\Ttm' => __DIR__ . '/../..' . '/src/mgl/Ttm.php',
        'Dplus\\Codes\\Mpm\\Dcm' => __DIR__ . '/../..' . '/src/mpm/Dcm.php',
        'Dplus\\Codes\\Mpm\\Rcm' => __DIR__ . '/../..' . '/src/mpm/Rcm.php',
        'Dplus\\Codes\\Mpo\\Cnfm' => __DIR__ . '/../..' . '/src/mpo/Cnfm.php',
        'Dplus\\Codes\\Mpr\\Src' => __DIR__ . '/../..' . '/src/mpr/Src.php',
        'Dplus\\Codes\\Msa\\Lgrp' => __DIR__ . '/../..' . '/src/msa/Lgrp.php',
        'Dplus\\Codes\\Response' => __DIR__ . '/../..' . '/src/Response.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit430c49f36b741f9836c2f39ed5bfc915::$classMap;

        }, null, ClassLoader::class);
    }
}
