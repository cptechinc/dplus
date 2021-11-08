<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdbc64365eb6e12b523add8142a5fb6dd
{
    public static $classMap = array (
        'Dplus\\Min\\Inmain\\Itm\\Dimensions' => __DIR__ . '/../..' . '/src/Dimensions.php',
        'Dplus\\Min\\Inmain\\Itm\\Options' => __DIR__ . '/../..' . '/src/Options.php',
        'Dplus\\Min\\Inmain\\Itm\\Response' => __DIR__ . '/../..' . '/src/Response.php',
        'Dplus\\Min\\Inmain\\Itm\\Substitutes' => __DIR__ . '/../..' . '/src/Substitutes.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitdbc64365eb6e12b523add8142a5fb6dd::$classMap;

        }, null, ClassLoader::class);
    }
}
