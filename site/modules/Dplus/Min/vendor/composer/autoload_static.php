<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitca74aaafdb38d46af24f78169485a1ba
{
    public static $classMap = array (
        'Dplus\\Min\\Inmain\\I2i\\I2i' => __DIR__ . '/../..' . '/src/Inmain/I2i/I2i.php',
        'Dplus\\Min\\Inmain\\I2i\\Response' => __DIR__ . '/../..' . '/src/Inmain/I2i/Response.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitca74aaafdb38d46af24f78169485a1ba::$classMap;

        }, null, ClassLoader::class);
    }
}
