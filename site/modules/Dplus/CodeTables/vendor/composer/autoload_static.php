<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit430c49f36b741f9836c2f39ed5bfc915
{
    public static $classMap = array (
        'Dplus\\Codes\\Base' => __DIR__ . '/../..' . '/src/Base.php',
        'Dplus\\Codes\\Po\\Cnfm' => __DIR__ . '/../..' . '/src/po/Cnfm.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit430c49f36b741f9836c2f39ed5bfc915::$classMap;

        }, null, ClassLoader::class);
    }
}
