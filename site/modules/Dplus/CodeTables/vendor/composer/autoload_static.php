<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit430c49f36b741f9836c2f39ed5bfc915
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dplus\\Codes\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dplus\\Codes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Dplus\\Codes\\Base' => __DIR__ . '/../..' . '/src/Base.php',
        'Dplus\\Codes\\Base\\Simple' => __DIR__ . '/../..' . '/src/Base/Simple.php',
        'Dplus\\Codes\\Map\\Aoptm' => __DIR__ . '/../..' . '/src/map/Aoptm.php',
        'Dplus\\Codes\\Map\\Lsm' => __DIR__ . '/../..' . '/src/map/Bum.php',
        'Dplus\\Codes\\Map\\Vtm' => __DIR__ . '/../..' . '/src/map/Vtm.php',
        'Dplus\\Codes\\Mar\\Roptm' => __DIR__ . '/../..' . '/src/mar/Roptm.php',
        'Dplus\\Codes\\Mgl\\Dtm' => __DIR__ . '/../..' . '/src/mgl/Dtm.php',
        'Dplus\\Codes\\Mgl\\Mhm' => __DIR__ . '/../..' . '/src/mgl/Mhm.php',
        'Dplus\\Codes\\Mgl\\Ttm' => __DIR__ . '/../..' . '/src/mgl/Ttm.php',
        'Dplus\\Codes\\Min\\Csccm' => __DIR__ . '/../..' . '/src/min/Csccm.php',
        'Dplus\\Codes\\Min\\Iasm' => __DIR__ . '/../..' . '/src/min/Iasm.php',
        'Dplus\\Codes\\Min\\Igcm' => __DIR__ . '/../..' . '/src/min/Igcm.php',
        'Dplus\\Codes\\Min\\Ioptm' => __DIR__ . '/../..' . '/src/min/Ioptm.php',
        'Dplus\\Codes\\Min\\Iwhm' => __DIR__ . '/../..' . '/src/min/Iwhm.php',
        'Dplus\\Codes\\Mpm\\Dcm' => __DIR__ . '/../..' . '/src/mpm/Dcm.php',
        'Dplus\\Codes\\Mpm\\Rcm' => __DIR__ . '/../..' . '/src/mpm/Rcm.php',
        'Dplus\\Codes\\Mpo\\Cnfm' => __DIR__ . '/../..' . '/src/mpo/Cnfm.php',
        'Dplus\\Codes\\Mpr\\Src' => __DIR__ . '/../..' . '/src/mpr/Src.php',
        'Dplus\\Codes\\Msa\\Lgrp' => __DIR__ . '/../..' . '/src/msa/Lgrp.php',
        'Dplus\\Codes\\Msa\\Sysop' => __DIR__ . '/../..' . '/src/msa/Sysop.php',
        'Dplus\\Codes\\Msa\\SysopOptionalCode' => __DIR__ . '/../..' . '/src/msa/SysopOptionalCode.php',
        'Dplus\\Codes\\Mso\\Lsm' => __DIR__ . '/../..' . '/src/Mso/Lsm.php',
        'Dplus\\Codes\\Mso\\Mfcm' => __DIR__ . '/../..' . '/src/Mso/Mfcm.php',
        'Dplus\\Codes\\Mso\\Rgarc' => __DIR__ . '/../..' . '/src/Mso/Rgarc.php',
        'Dplus\\Codes\\Mso\\Rgasc' => __DIR__ . '/../..' . '/src/Mso/Rgasc.php',
        'Dplus\\Codes\\Mso\\Soptm' => __DIR__ . '/../..' . '/src/Mso/Soptm.php',
        'Dplus\\Codes\\Response' => __DIR__ . '/../..' . '/src/Response.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit430c49f36b741f9836c2f39ed5bfc915::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit430c49f36b741f9836c2f39ed5bfc915::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit430c49f36b741f9836c2f39ed5bfc915::$classMap;

        }, null, ClassLoader::class);
    }
}
