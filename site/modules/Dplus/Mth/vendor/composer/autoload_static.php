<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbba18c97151efda2318a1679424c2004
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dplus\\Mth\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dplus\\Mth\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Dplus\\Mth\\Tlm' => __DIR__ . '/../..' . '/src/Tlm.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitbba18c97151efda2318a1679424c2004::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitbba18c97151efda2318a1679424c2004::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitbba18c97151efda2318a1679424c2004::$classMap;

        }, null, ClassLoader::class);
    }
}
