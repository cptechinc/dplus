<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4ed415e031ba38684c0dc56114a11d32
{
    public static $classMap = array (
        'Dplus\\Configs\\AbstractConfig' => __DIR__ . '/../..' . '/src/AbstractConfig.php',
        'Dplus\\Configs\\Ap' => __DIR__ . '/../..' . '/src/configs/Ap.php',
        'Dplus\\Configs\\Ci' => __DIR__ . '/../..' . '/src/configs/Ci.php',
        'Dplus\\Configs\\In' => __DIR__ . '/../..' . '/src/configs/In.php',
        'Dplus\\Configs\\Kt' => __DIR__ . '/../..' . '/src/configs/Kt.php',
        'Dplus\\Configs\\Po' => __DIR__ . '/../..' . '/src/configs/Po.php',
        'Dplus\\Configs\\So' => __DIR__ . '/../..' . '/src/configs/So.php',
        'Dplus\\Configs\\Sys' => __DIR__ . '/../..' . '/src/configs/Sys.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit4ed415e031ba38684c0dc56114a11d32::$classMap;

        }, null, ClassLoader::class);
    }
}
