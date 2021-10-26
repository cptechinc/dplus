<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2c03c3e02253fee3b0cbe74a13d2080d
{
    public static $classMap = array (
        'Dplus\\Filters\\AbstractFilter' => __DIR__ . '/../..' . '/src/AbstractFilter.php',
        'Dplus\\Filters\\Map\\ApContact' => __DIR__ . '/../..' . '/src/Map/ApContact.php',
        'Dplus\\Filters\\Map\\Mxrfe' => __DIR__ . '/../..' . '/src/Map/Mxrfe.php',
        'Dplus\\Filters\\Map\\Vendor' => __DIR__ . '/../..' . '/src/Map/Vendor.php',
        'Dplus\\Filters\\Map\\VendorShipfrom' => __DIR__ . '/../..' . '/src/Map/VendorShipfrom.php',
        'Dplus\\Filters\\Map\\Vxm' => __DIR__ . '/../..' . '/src/Map/Vxm.php',
        'Dplus\\Filters\\Mar\\Customer' => __DIR__ . '/../..' . '/src/Mar/Customer.php',
        'Dplus\\Filters\\Mar\\SalesPerson' => __DIR__ . '/../..' . '/src/Mar/SalesPerson.php',
        'Dplus\\Filters\\Mar\\Shipto' => __DIR__ . '/../..' . '/src/Mar/Shipto.php',
        'Dplus\\Filters\\Mgl\\GlCode' => __DIR__ . '/../..' . '/src/Mgl/GlCode.php',
        'Dplus\\Filters\\Min\\I2i' => __DIR__ . '/../..' . '/src/Min/I2i.php',
        'Dplus\\Filters\\Min\\InvAdjustmentReason' => __DIR__ . '/../..' . '/src/Min/InvAdjustmentReason.php',
        'Dplus\\Filters\\Min\\ItemGroup' => __DIR__ . '/../..' . '/src/Min/ItemGroup.php',
        'Dplus\\Filters\\Min\\ItemMaster' => __DIR__ . '/../..' . '/src/Min/ItemMaster.php',
        'Dplus\\Filters\\Min\\ItemSubstitute' => __DIR__ . '/../..' . '/src/Min/ItemSubstitute.php',
        'Dplus\\Filters\\Min\\LotMaster' => __DIR__ . '/../..' . '/src/Min/LotMaster.php',
        'Dplus\\Filters\\Min\\Upcx' => __DIR__ . '/../..' . '/src/Min/Upcx.php',
        'Dplus\\Filters\\Min\\WarehouseBin' => __DIR__ . '/../..' . '/src/Min/WarehouseBin.php',
        'Dplus\\Filters\\Misc\\CountryCode' => __DIR__ . '/../..' . '/src/Misc/CountryCode.php',
        'Dplus\\Filters\\Misc\\Funcperm' => __DIR__ . '/../..' . '/src/Misc/Funcperm.php',
        'Dplus\\Filters\\Misc\\PhoneBook' => __DIR__ . '/../..' . '/src/Misc/PhoneBook.php',
        'Dplus\\Filters\\Mki\\Kim' => __DIR__ . '/../..' . '/src/Mki/Kim.php',
        'Dplus\\Filters\\Mpm\\Bom\\Header' => __DIR__ . '/../..' . '/src/Mpm/Bom/Header.php',
        'Dplus\\Filters\\Mpm\\PrResource' => __DIR__ . '/../..' . '/src/Mpm/PrResource.php',
        'Dplus\\Filters\\Mpm\\PrWorkCenter' => __DIR__ . '/../..' . '/src/Mpm/PrWorkCenter.php',
        'Dplus\\Filters\\Mpo\\ApInvoice' => __DIR__ . '/../..' . '/src/Mpo/ApInvoice.php',
        'Dplus\\Filters\\Mpo\\PoConfirmCode' => __DIR__ . '/../..' . '/src/Mpo/PoConfirmCode.php',
        'Dplus\\Filters\\Mpo\\PurchaseOrder' => __DIR__ . '/../..' . '/src/Mpo/PurchaseOrder.php',
        'Dplus\\Filters\\Mqo\\Quote' => __DIR__ . '/../..' . '/src/Mqo/Quote.php',
        'Dplus\\Filters\\Mso\\Cxm' => __DIR__ . '/../..' . '/src/Mso/Cxm.php',
        'Dplus\\Filters\\Mso\\SalesHistory' => __DIR__ . '/../..' . '/src/Mso/SalesHistory.php',
        'Dplus\\Filters\\Mso\\SalesHistory\\Detail' => __DIR__ . '/../..' . '/src/Mso/SalesHistory/SalesHistoryDetail.php',
        'Dplus\\Filters\\Mso\\SalesOrder' => __DIR__ . '/../..' . '/src/Mso/SalesOrder.php',
        'Dplus\\Filters\\Mso\\SalesOrder\\SalesOrderDetail' => __DIR__ . '/../..' . '/src/Mso/SalesOrder/SalesOrderDetail.php',
        'Dplus\\Filters\\SortFilter' => __DIR__ . '/../..' . '/src/SortFilter.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInit2c03c3e02253fee3b0cbe74a13d2080d::$classMap;

        }, null, ClassLoader::class);
    }
}
