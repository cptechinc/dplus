<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd88d71cb40d348caec7b5e500eca7c32
{
    public static $classMap = array (
        'Dplus\\Mqo\\Eqo\\Header' => __DIR__ . '/../..' . '/src/Eqo/Header.php',
        'Dplus\\Mqo\\Eqo\\Items' => __DIR__ . '/../..' . '/src/Eqo/Items.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitd88d71cb40d348caec7b5e500eca7c32::$classMap;

        }, null, ClassLoader::class);
    }
}
