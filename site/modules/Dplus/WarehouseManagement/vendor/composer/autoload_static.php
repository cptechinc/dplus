<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitbe01d8a824371fa061fc4d032387886e
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Dplus\\Wm\\Base' => __DIR__ . '/../..' . '/src/Base.php',
        'Dplus\\Wm\\Binr' => __DIR__ . '/../..' . '/src/Binr/Binr.php',
        'Dplus\\Wm\\Inventory\\BinInquiry' => __DIR__ . '/../..' . '/src/Inventory/BinInquiry.php',
        'Dplus\\Wm\\Inventory\\FindItem' => __DIR__ . '/../..' . '/src/Inventory/FindItem.php',
        'Dplus\\Wm\\Inventory\\Lotm' => __DIR__ . '/../..' . '/src/Inventory/Lotm/Lotm.php',
        'Dplus\\Wm\\Inventory\\Mlot\\Img' => __DIR__ . '/../..' . '/src/Inventory/Mlot/Img.php',
        'Dplus\\Wm\\Inventory\\Mlot\\Labels' => __DIR__ . '/../..' . '/src/Inventory/Mlot/Labels.php',
        'Dplus\\Wm\\Inventory\\Search' => __DIR__ . '/../..' . '/src/Inventory/Search.php',
        'Dplus\\Wm\\Inventory\\Whse\\Lots\\Lookup' => __DIR__ . '/../..' . '/src/Inventory/Whse/Lots/Lookup.php',
        'Dplus\\Wm\\Inventory\\Whse\\Lots\\Lookup\\ExcludePackBin' => __DIR__ . '/../..' . '/src/Inventory/Whse/Lots/ExcludePackBin.php',
        'Dplus\\Wm\\Inventory\\Whse\\StockStatus' => __DIR__ . '/../..' . '/src/Inventory/Whse/StockStatus.php',
        'Dplus\\Wm\\Inventory\\Whse\\StockStatus\\Provalley' => __DIR__ . '/../..' . '/src/Inventory/Whse/StockStatus/Provalley.php',
        'Dplus\\Wm\\LastPrintJob' => __DIR__ . '/../..' . '/src/Inventory/Printing/LastPrintJob.php',
        'Dplus\\Wm\\LastPrintJob\\Lotlbl' => __DIR__ . '/../..' . '/src/Inventory/Printing/LastPrintJob/Lotlbl.php',
        'Dplus\\Wm\\Receiving\\Items' => __DIR__ . '/../..' . '/src/Receiving/Items.php',
        'Dplus\\Wm\\Receiving\\Receiving' => __DIR__ . '/../..' . '/src/Receiving/Receiving.php',
        'Dplus\\Wm\\Receiving\\Strategies\\CreatePo\\Allow' => __DIR__ . '/../..' . '/src/Receiving/Strategies/CreatePo/Allow.php',
        'Dplus\\Wm\\Receiving\\Strategies\\CreatePo\\CreatePo' => __DIR__ . '/../..' . '/src/Receiving/Strategies/CreatePo/CreatePo.interface.php',
        'Dplus\\Wm\\Receiving\\Strategies\\CreatePo\\Forbid' => __DIR__ . '/../..' . '/src/Receiving/Strategies/CreatePo/Forbid.php',
        'Dplus\\Wm\\Receiving\\Strategies\\EnforcePoItemids\\EnforcePoItemids' => __DIR__ . '/../..' . '/src/Receiving/Strategies/EnforcePoItemids/Enforce.interface.php',
        'Dplus\\Wm\\Receiving\\Strategies\\EnforcePoItemids\\Enforced' => __DIR__ . '/../..' . '/src/Receiving/Strategies/EnforcePoItemids/Enforced.php',
        'Dplus\\Wm\\Receiving\\Strategies\\EnforcePoItemids\\Relaxed' => __DIR__ . '/../..' . '/src/Receiving/Strategies/EnforcePoItemids/Relaxed.php',
        'Dplus\\Wm\\Receiving\\Strategies\\EnforceQty\\Enforced' => __DIR__ . '/../..' . '/src/Receiving/Strategies/EnforceQty/Enforced.php',
        'Dplus\\Wm\\Receiving\\Strategies\\EnforceQty\\Relaxed' => __DIR__ . '/../..' . '/src/Receiving/Strategies/EnforceQty/Relaxed.php',
        'Dplus\\Wm\\Receiving\\Strategies\\EnforceQty\\Warn' => __DIR__ . '/../..' . '/src/Receiving/Strategies/EnforceQty/Warn.php',
        'Dplus\\Wm\\Receiving\\Strategies\\ReadQty\\LotserialQty' => __DIR__ . '/../..' . '/src/Receiving/Strategies/ReadQty/LotserialQty.php',
        'Dplus\\Wm\\Receiving\\Strategies\\ReadQty\\LotserialSingle' => __DIR__ . '/../..' . '/src/Receiving/Strategies/ReadQty/LotserialSingle.php',
        'Dplus\\Wm\\Receiving\\Strategies\\ReadQty\\ReadStrategy' => __DIR__ . '/../..' . '/src/Receiving/Strategies/ReadQty/Base.php',
        'Dplus\\Wm\\Reports\\Inventory\\StockStatus' => __DIR__ . '/../..' . '/src/Reports/Inventory/StockStatus.php',
        'Dplus\\Wm\\Reports\\Inventory\\StockStatus\\Export\\Spreadsheet' => __DIR__ . '/../..' . '/src/Reports/Inventory/StockStatus/Export/Spreadsheet.php',
        'Dplus\\Wm\\Reports\\Inventory\\StockStatus\\Factory' => __DIR__ . '/../..' . '/src/Reports/Inventory/StockStatus/Factory.php',
        'Dplus\\Wm\\Reports\\Inventory\\StockStatus\\Provalley' => __DIR__ . '/../..' . '/src/Reports/Inventory/StockStatus/Provalley.php',
        'Dplus\\Wm\\Sop\\Picking\\AllocatedLots' => __DIR__ . '/../..' . '/src/Sop/Picking/AllocatedLots.php',
        'Dplus\\Wm\\Sop\\Picking\\Inventory' => __DIR__ . '/../..' . '/src/Sop/Picking/Inventory.php',
        'Dplus\\Wm\\Sop\\Picking\\Items' => __DIR__ . '/../..' . '/src/Sop/Picking/Items.php',
        'Dplus\\Wm\\Sop\\Picking\\Picking' => __DIR__ . '/../..' . '/src/Sop/Picking/Picking.php',
        'Dplus\\Wm\\Sop\\Picking\\Strategies\\Inventory\\Lookup\\ExcludePackBin' => __DIR__ . '/../..' . '/src/Sop/Picking/Strategies/Inventory/Lookup/ExcludePackBin.php',
        'Dplus\\Wm\\Sop\\Picking\\Strategies\\Inventory\\Lookup\\IncludePackBin' => __DIR__ . '/../..' . '/src/Sop/Picking/Strategies/Inventory/Lookup/IncludePackBin.php',
        'Dplus\\Wm\\Sop\\Picking\\Strategies\\Inventory\\Lookup\\Lookup' => __DIR__ . '/../..' . '/src/Sop/Picking/Strategies/Inventory/Lookup/Lookup.php',
        'Dplus\\Wm\\Sop\\Picking\\Strategies\\PackBin\\Excluded' => __DIR__ . '/../..' . '/src/Sop/Picking/Strategies/PackBin/Excluded.php',
        'Dplus\\Wm\\Sop\\Picking\\Strategies\\PackBin\\Included' => __DIR__ . '/../..' . '/src/Sop/Picking/Strategies/PackBin/Included.php',
        'Dplus\\Wm\\Sop\\Picking\\Strategies\\PackBin\\PackBin' => __DIR__ . '/../..' . '/src/Sop/Picking/Strategies/PackBin/PackBin.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInitbe01d8a824371fa061fc4d032387886e::$classMap;

        }, null, ClassLoader::class);
    }
}
