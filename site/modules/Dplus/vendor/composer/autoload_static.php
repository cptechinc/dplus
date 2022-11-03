<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit15650899cb104616eafc7c0a4e470ad8
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dplus\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dplus\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Dplus\\Map\\Apmain\\Vmm' => __DIR__ . '/../..' . '/src/Map/Apmain/Vmm.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit15650899cb104616eafc7c0a4e470ad8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit15650899cb104616eafc7c0a4e470ad8::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit15650899cb104616eafc7c0a4e470ad8::$classMap;

        }, null, ClassLoader::class);
    }
}
