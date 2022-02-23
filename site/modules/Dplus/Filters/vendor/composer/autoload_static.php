<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2c03c3e02253fee3b0cbe74a13d2080d
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'Dplus\\Filters\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Dplus\\Filters\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Dplus\\Filters\\AbstractFilter' => __DIR__ . '/../..' . '/src/AbstractFilter.php',
        'Dplus\\Filters\\CodeFilter' => __DIR__ . '/../..' . '/src/CodeFilter.php',
        'Dplus\\Filters\\Map\\ApBuyer' => __DIR__ . '/../..' . '/src/Map/ApBuyer.php',
        'Dplus\\Filters\\Map\\ApContact' => __DIR__ . '/../..' . '/src/Map/ApContact.php',
        'Dplus\\Filters\\Map\\ApTypeCode' => __DIR__ . '/../..' . '/src/Map/ApTypeCode.php',
        'Dplus\\Filters\\Map\\Mxrfe' => __DIR__ . '/../..' . '/src/Map/Mxrfe.php',
        'Dplus\\Filters\\Map\\Vendor' => __DIR__ . '/../..' . '/src/Map/Vendor.php',
        'Dplus\\Filters\\Map\\VendorShipfrom' => __DIR__ . '/../..' . '/src/Map/VendorShipfrom.php',
        'Dplus\\Filters\\Map\\Vxm' => __DIR__ . '/../..' . '/src/Map/Vxm.php',
        'Dplus\\Filters\\Mar\\Customer' => __DIR__ . '/../..' . '/src/Mar/Customer.php',
        'Dplus\\Filters\\Mar\\SalesPerson' => __DIR__ . '/../..' . '/src/Mar/SalesPerson.php',
        'Dplus\\Filters\\Mar\\Shipto' => __DIR__ . '/../..' . '/src/Mar/Shipto.php',
        'Dplus\\Filters\\Mgl\\GlCode' => __DIR__ . '/../..' . '/src/Mgl/GlCode.php',
        'Dplus\\Filters\\Mgl\\GlDistCode' => __DIR__ . '/../..' . '/src/Mgl/GlDistCode.php',
        'Dplus\\Filters\\Mgl\\GlTextCode' => __DIR__ . '/../..' . '/src/Mgl/GlTextCode.php',
        'Dplus\\Filters\\Min\\AddonItem' => __DIR__ . '/../..' . '/src/Min/AddonItem.php',
        'Dplus\\Filters\\Min\\CustomerStockingCell' => __DIR__ . '/../..' . '/src/Min/CustomerStockingCell.php',
        'Dplus\\Filters\\Min\\I2i' => __DIR__ . '/../..' . '/src/Min/I2i.php',
        'Dplus\\Filters\\Min\\InvAdjustmentReason' => __DIR__ . '/../..' . '/src/Min/InvAdjustmentReason.php',
        'Dplus\\Filters\\Min\\InvAssortmentCode' => __DIR__ . '/../..' . '/src/Min/InvAssortmentCode.php',
        'Dplus\\Filters\\Min\\InvCommissionCode' => __DIR__ . '/../..' . '/src/Min/InvCommissionCode.php',
        'Dplus\\Filters\\Min\\InvGroupCode' => __DIR__ . '/../..' . '/src/Min/InvGroupCode.php',
        'Dplus\\Filters\\Min\\InvProductLineCode' => __DIR__ . '/../..' . '/src/Min/InvProductLineCode.php',
        'Dplus\\Filters\\Min\\ItemGroup' => __DIR__ . '/../..' . '/src/Min/ItemGroup.php',
        'Dplus\\Filters\\Min\\ItemMaster' => __DIR__ . '/../..' . '/src/Min/ItemMaster.php',
        'Dplus\\Filters\\Min\\ItemSubstitute' => __DIR__ . '/../..' . '/src/Min/ItemSubstitute.php',
        'Dplus\\Filters\\Min\\LotMaster' => __DIR__ . '/../..' . '/src/Min/LotMaster.php',
        'Dplus\\Filters\\Min\\MsdsCode' => __DIR__ . '/../..' . '/src/Min/MsdsCode.php',
        'Dplus\\Filters\\Min\\TariffCode' => __DIR__ . '/../..' . '/src/Min/TariffCode.php',
        'Dplus\\Filters\\Min\\Upcx' => __DIR__ . '/../..' . '/src/Min/Upcx.php',
        'Dplus\\Filters\\Min\\Warehouse' => __DIR__ . '/../..' . '/src/Min/Warehouse.php',
        'Dplus\\Filters\\Min\\WarehouseBin' => __DIR__ . '/../..' . '/src/Min/WarehouseBin.php',
        'Dplus\\Filters\\Misc\\CountryCode' => __DIR__ . '/../..' . '/src/Misc/CountryCode.php',
        'Dplus\\Filters\\Misc\\Funcperm' => __DIR__ . '/../..' . '/src/Misc/Funcperm.php',
        'Dplus\\Filters\\Misc\\PhoneBook' => __DIR__ . '/../..' . '/src/Misc/PhoneBook.php',
        'Dplus\\Filters\\Misc\\Printer' => __DIR__ . '/../..' . '/src/Misc/Printer.php',
        'Dplus\\Filters\\Mki\\Kim' => __DIR__ . '/../..' . '/src/Mki/Kim.php',
        'Dplus\\Filters\\Mpm\\Bom\\Header' => __DIR__ . '/../..' . '/src/Mpm/Bom/Header.php',
        'Dplus\\Filters\\Mpm\\PrResource' => __DIR__ . '/../..' . '/src/Mpm/PrResource.php',
        'Dplus\\Filters\\Mpm\\PrWorkCenter' => __DIR__ . '/../..' . '/src/Mpm/PrWorkCenter.php',
        'Dplus\\Filters\\Mpo\\ApInvoice' => __DIR__ . '/../..' . '/src/Mpo/ApInvoice.php',
        'Dplus\\Filters\\Mpo\\PoConfirmCode' => __DIR__ . '/../..' . '/src/Mpo/PoConfirmCode.php',
        'Dplus\\Filters\\Mpo\\PurchaseOrder' => __DIR__ . '/../..' . '/src/Mpo/PurchaseOrder.php',
        'Dplus\\Filters\\Mpr\\ProspectSource' => __DIR__ . '/../..' . '/src/Mpr/ProspectSource.php',
        'Dplus\\Filters\\Mqo\\Quote' => __DIR__ . '/../..' . '/src/Mqo/Quote.php',
        'Dplus\\Filters\\Msa\\DplusUser' => __DIR__ . '/../..' . '/src/Msa/DplusUser.php',
        'Dplus\\Filters\\Msa\\MsaSysopCode' => __DIR__ . '/../..' . '/src/Msa/MsaSysopCode.php',
        'Dplus\\Filters\\Msa\\NotePreDefined' => __DIR__ . '/../..' . '/src/Msa/NotePredefined.php',
        'Dplus\\Filters\\Msa\\SysLoginGroup' => __DIR__ . '/../..' . '/src/Msa/SysLoginGroup.php',
        'Dplus\\Filters\\Msa\\SysLoginRole' => __DIR__ . '/../..' . '/src/Msa/SysLoginRole.php',
        'Dplus\\Filters\\Msa\\SysopOptionalCode' => __DIR__ . '/../..' . '/src/Msa/SysopOptionalCode.php',
        'Dplus\\Filters\\Mso\\Cxm' => __DIR__ . '/../..' . '/src/Mso/Cxm.php',
        'Dplus\\Filters\\Mso\\LostSalesCode' => __DIR__ . '/../..' . '/src/Mso/LostSalesCode.php',
        'Dplus\\Filters\\Mso\\MotorFreightCode' => __DIR__ . '/../..' . '/src/Mso/MotorFreightCode.php',
        'Dplus\\Filters\\Mso\\SalesHistory' => __DIR__ . '/../..' . '/src/Mso/SalesHistory.php',
        'Dplus\\Filters\\Mso\\SalesHistory\\Detail' => __DIR__ . '/../..' . '/src/Mso/SalesHistory/SalesHistoryDetail.php',
        'Dplus\\Filters\\Mso\\SalesOrder' => __DIR__ . '/../..' . '/src/Mso/SalesOrder.php',
        'Dplus\\Filters\\Mso\\SalesOrder\\SalesOrderDetail' => __DIR__ . '/../..' . '/src/Mso/SalesOrder/SalesOrderDetail.php',
        'Dplus\\Filters\\Mso\\SoReasonCode' => __DIR__ . '/../..' . '/src/Mso/SoReasonCode.php',
        'Dplus\\Filters\\Mso\\SoRgaCode' => __DIR__ . '/../..' . '/src/Mso/SoRgaCode.php',
        'Dplus\\Filters\\Mth\\ThermalLabelFormat' => __DIR__ . '/../..' . '/src/Mth/ThermalLabelFormat.php',
        'Dplus\\Filters\\SortFilter' => __DIR__ . '/../..' . '/src/SortFilter.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2c03c3e02253fee3b0cbe74a13d2080d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2c03c3e02253fee3b0cbe74a13d2080d::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2c03c3e02253fee3b0cbe74a13d2080d::$classMap;

        }, null, ClassLoader::class);
    }
}
