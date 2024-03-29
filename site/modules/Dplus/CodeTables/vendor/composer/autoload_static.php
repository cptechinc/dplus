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
        'Dplus\\Codes\\Map\\Aoptm' => __DIR__ . '/../..' . '/src/Map/Aoptm.php',
        'Dplus\\Codes\\Map\\Bum' => __DIR__ . '/../..' . '/src/Map/Bum.php',
        'Dplus\\Codes\\Map\\Vtm' => __DIR__ . '/../..' . '/src/Map/Vtm.php',
        'Dplus\\Codes\\Mar\\Cmm' => __DIR__ . '/../..' . '/src/Mar/Cmm.php',
        'Dplus\\Codes\\Mar\\Roptm' => __DIR__ . '/../..' . '/src/Mar/Roptm.php',
        'Dplus\\Codes\\Mar\\Trm' => __DIR__ . '/../..' . '/src/Mar/Trm.php',
        'Dplus\\Codes\\Mgl\\Dtm' => __DIR__ . '/../..' . '/src/Mgl/Dtm.php',
        'Dplus\\Codes\\Mgl\\Mhm' => __DIR__ . '/../..' . '/src/Mgl/Mhm.php',
        'Dplus\\Codes\\Mgl\\Ttm' => __DIR__ . '/../..' . '/src/Mgl/Ttm.php',
        'Dplus\\Codes\\Min\\Csccm' => __DIR__ . '/../..' . '/src/Min/Csccm.php',
        'Dplus\\Codes\\Min\\Iasm' => __DIR__ . '/../..' . '/src/Min/Iasm.php',
        'Dplus\\Codes\\Min\\Igcm' => __DIR__ . '/../..' . '/src/Min/Igcm.php',
        'Dplus\\Codes\\Min\\Igm' => __DIR__ . '/../..' . '/src/Min/Igm.php',
        'Dplus\\Codes\\Min\\Igpm' => __DIR__ . '/../..' . '/src/Min/Igpm.php',
        'Dplus\\Codes\\Min\\Ioptm' => __DIR__ . '/../..' . '/src/Min/Ioptm.php',
        'Dplus\\Codes\\Min\\Iplm' => __DIR__ . '/../..' . '/src/Min/Iplm.php',
        'Dplus\\Codes\\Min\\Iwhm' => __DIR__ . '/../..' . '/src/Min/Iwhm.php',
        'Dplus\\Codes\\Min\\Iwhm\\Qnotes' => __DIR__ . '/../..' . '/src/Min/Iwhm/Qnotes.php',
        'Dplus\\Codes\\Min\\Msdsm' => __DIR__ . '/../..' . '/src/Min/Msdsm.php',
        'Dplus\\Codes\\Min\\Spit' => __DIR__ . '/../..' . '/src/Min/Spit.php',
        'Dplus\\Codes\\Min\\Stcm' => __DIR__ . '/../..' . '/src/Min/Stcm.php',
        'Dplus\\Codes\\Min\\Tarm' => __DIR__ . '/../..' . '/src/Min/Tarm.php',
        'Dplus\\Codes\\Min\\Tarm\\Countries' => __DIR__ . '/../..' . '/src/Min/Tarm/Countries.php',
        'Dplus\\Codes\\Min\\Umm' => __DIR__ . '/../..' . '/src/Min/Umm.php',
        'Dplus\\Codes\\Mpm\\Dcm' => __DIR__ . '/../..' . '/src/Mpm/Dcm.php',
        'Dplus\\Codes\\Mpm\\Rcm' => __DIR__ . '/../..' . '/src/Mpm/Rcm.php',
        'Dplus\\Codes\\Mpo\\Cnfm' => __DIR__ . '/../..' . '/src/Mpo/Cnfm.php',
        'Dplus\\Codes\\Mpr\\Src' => __DIR__ . '/../..' . '/src/Mpr/Src.php',
        'Dplus\\Codes\\Msa\\Lgrp' => __DIR__ . '/../..' . '/src/Msa/Lgrp.php',
        'Dplus\\Codes\\Msa\\Sysop' => __DIR__ . '/../..' . '/src/Msa/Sysop.php',
        'Dplus\\Codes\\Msa\\SysopOptionalCode' => __DIR__ . '/../..' . '/src/Msa/SysopOptionalCode.php',
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
