<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6574d5244ada2c9d8ecdaee797f2d7a8
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dplus\\Xrefs\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dplus\\Xrefs\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Dplus\\Xrefs\\AbstractXrefManager' => __DIR__ . '/../..' . '/src/AbstractXrefManager.php',
        'Dplus\\Xrefs\\Cxm' => __DIR__ . '/../..' . '/src/Cxm.php',
        'Dplus\\Xrefs\\Response' => __DIR__ . '/../..' . '/src/Response.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6574d5244ada2c9d8ecdaee797f2d7a8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6574d5244ada2c9d8ecdaee797f2d7a8::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit6574d5244ada2c9d8ecdaee797f2d7a8::$classMap;

        }, null, ClassLoader::class);
    }
}
