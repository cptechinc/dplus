<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5294399c1667d09eec658aa3396e7693
{
    public static $classMap = array (
        'Dplus\\Mpm\\Pmmain\\Bmm' => __DIR__ . '/../..' . '/src/pmmain/Bmm/Bmm.php',
        'Dplus\\Mpm\\Pmmain\\Bmm\\Components' => __DIR__ . '/../..' . '/src/pmmain/Bmm/Component.php',
        'Dplus\\Mpm\\Pmmain\\Bmm\\Header' => __DIR__ . '/../..' . '/src/pmmain/Bmm/Header.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit5294399c1667d09eec658aa3396e7693::$classMap;

        }, null, ClassLoader::class);
    }
}
