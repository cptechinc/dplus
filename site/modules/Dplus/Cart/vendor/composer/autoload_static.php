<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2713685c50d74f0ef72aa36df5ed0caf
{
    public static $classMap = array (
        'Dplus\\Cart\\Cart' => __DIR__ . '/../..' . '/src/Cart.php',
        'Dplus\\Cart\\Header' => __DIR__ . '/../..' . '/src/Header.php',
        'Dplus\\Cart\\Items' => __DIR__ . '/../..' . '/src/Items.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit2713685c50d74f0ef72aa36df5ed0caf::$classMap;

        }, null, ClassLoader::class);
    }
}
