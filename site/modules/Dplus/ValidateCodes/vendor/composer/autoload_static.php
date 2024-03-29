<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit05e8b5312246afdde1a9eb3568c1d72c
{
    public static $classMap = array (
        'Dplus\\CodeValidators\\Itm\\Warehouse' => __DIR__ . '/../..' . '/src/Min/Itm/Warehouse.php',
        'Dplus\\CodeValidators\\Map' => __DIR__ . '/../..' . '/src/Map/Map.php',
        'Dplus\\CodeValidators\\Map\\Mxrfe' => __DIR__ . '/../..' . '/src/Map/Mxrfe.php',
        'Dplus\\CodeValidators\\Map\\Vxm' => __DIR__ . '/../..' . '/src/Map/Vxm.php',
        'Dplus\\CodeValidators\\Mar' => __DIR__ . '/../..' . '/src/Mar/Mar.php',
        'Dplus\\CodeValidators\\Mar\\Spm' => __DIR__ . '/../..' . '/src/Mar/Spm.php',
        'Dplus\\CodeValidators\\Mgl' => __DIR__ . '/../..' . '/src/Mgl/Mgl.php',
        'Dplus\\CodeValidators\\Mii' => __DIR__ . '/../..' . '/src/Mii/Mii.php',
        'Dplus\\CodeValidators\\Mii\\Iio' => __DIR__ . '/../..' . '/src/Mii/Iio.php',
        'Dplus\\CodeValidators\\Min' => __DIR__ . '/../..' . '/src/Min/Min.php',
        'Dplus\\CodeValidators\\Min\\Itm' => __DIR__ . '/../..' . '/src/Min/Itm/Itm.php',
        'Dplus\\CodeValidators\\Min\\Upcx' => __DIR__ . '/../..' . '/src/Min/Upcx.php',
        'Dplus\\CodeValidators\\Mki' => __DIR__ . '/../..' . '/src/Mki/Mki.php',
        'Dplus\\CodeValidators\\Mki\\Kim' => __DIR__ . '/../..' . '/src/Mki/Kim.php',
        'Dplus\\CodeValidators\\Mpo' => __DIR__ . '/../..' . '/src/Mpo/Mpo.php',
        'Dplus\\CodeValidators\\Mpo\\Po' => __DIR__ . '/../..' . '/src/Mpo/Po.php',
        'Dplus\\CodeValidators\\Mpo\\PoDetail' => __DIR__ . '/../..' . '/src/Mpo/PoDetail.php',
        'Dplus\\CodeValidators\\Mqo' => __DIR__ . '/../..' . '/src/Mqo/Mqo.php',
        'Dplus\\CodeValidators\\Msa' => __DIR__ . '/../..' . '/src/Msa/Msa.php',
        'Dplus\\CodeValidators\\Mso' => __DIR__ . '/../..' . '/src/Mso/Mso.php',
        'Dplus\\CodeValidators\\Mso\\Cxm' => __DIR__ . '/../..' . '/src/Mso/Cxm.php',
        'Dplus\\CodeValidators\\UserPermission' => __DIR__ . '/../..' . '/src/Misc/UserPermission.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit05e8b5312246afdde1a9eb3568c1d72c::$classMap;

        }, null, ClassLoader::class);
    }
}
