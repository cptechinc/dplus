<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2c03c3e02253fee3b0cbe74a13d2080d
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Dplus\\Filters\\AbstractFilter' => __DIR__ . '/../..' . '/src/AbstractFilter.php',
        'Dplus\\Filters\\Map\\ApContact' => __DIR__ . '/../..' . '/src/Map/ApContact.php',
        'Dplus\\Filters\\Mar\\SalesPerson' => __DIR__ . '/../..' . '/src/Mar/SalesPerson.php',
        'Dplus\\Filters\\Mgl\\GlCode' => __DIR__ . '/../..' . '/src/Mgl/GlCode.php',
        'Dplus\\Filters\\Min\\ItemGroup' => __DIR__ . '/../..' . '/src/Min/ItemGroup.php',
        'Dplus\\Filters\\Misc\\PhoneBook' => __DIR__ . '/../..' . '/src/Misc/PhoneBook.php',
        'Dplus\\Filters\\Mpo\\PurchaseOrder' => __DIR__ . '/../..' . '/src/Mpo/PurchaseOrder.php',
        'Dplus\\Filters\\Mso\\SalesHistory' => __DIR__ . '/../..' . '/src/Mso/SalesHistory.php',
        'Dplus\\Filters\\Mso\\SalesHistory\\Detail' => __DIR__ . '/../..' . '/src/Mso/SalesHistory/SalesHistoryDetail.php',
        'Dplus\\Filters\\Mso\\SalesOrder' => __DIR__ . '/../..' . '/src/Mso/SalesOrder.php',
        'Dplus\\Filters\\Mso\\SalesOrder\\SalesOrderDetail' => __DIR__ . '/../..' . '/src/Mso/SalesOrder/SalesOrderDetail.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit2c03c3e02253fee3b0cbe74a13d2080d::$classMap;

        }, null, ClassLoader::class);
    }
}
