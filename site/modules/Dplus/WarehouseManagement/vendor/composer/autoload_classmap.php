<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Composer\\InstalledVersions' => $vendorDir . '/composer/InstalledVersions.php',
    'Dplus\\Wm\\Base' => $baseDir . '/src/Base.php',
    'Dplus\\Wm\\Binr' => $baseDir . '/src/Binr/Binr.php',
    'Dplus\\Wm\\Inventory\\BinInquiry' => $baseDir . '/src/Inventory/BinInquiry.php',
    'Dplus\\Wm\\Inventory\\FindItem' => $baseDir . '/src/Inventory/FindItem.php',
    'Dplus\\Wm\\Inventory\\Lotm' => $baseDir . '/src/Inventory/Lotm/Lotm.php',
    'Dplus\\Wm\\Inventory\\Mlot\\Img' => $baseDir . '/src/Inventory/Mlot/Img.php',
    'Dplus\\Wm\\Inventory\\Mlot\\Labels' => $baseDir . '/src/Inventory/Mlot/Labels.php',
    'Dplus\\Wm\\Inventory\\Search' => $baseDir . '/src/Inventory/Search.php',
    'Dplus\\Wm\\Inventory\\Whse\\Lots\\Lookup' => $baseDir . '/src/Inventory/Whse/Lots/Lookup.php',
    'Dplus\\Wm\\Inventory\\Whse\\Lots\\Lookup\\ExcludePackBin' => $baseDir . '/src/Inventory/Whse/Lots/ExcludePackBin.php',
    'Dplus\\Wm\\Inventory\\Whse\\StockStatus' => $baseDir . '/src/Inventory/Whse/StockStatus.php',
    'Dplus\\Wm\\Inventory\\Whse\\StockStatus\\Provalley' => $baseDir . '/src/Inventory/Whse/StockStatus/Provalley.php',
    'Dplus\\Wm\\LastPrintJob' => $baseDir . '/src/Inventory/Printing/LastPrintJob.php',
    'Dplus\\Wm\\LastPrintJob\\Lotlbl' => $baseDir . '/src/Inventory/Printing/LastPrintJob/Lotlbl.php',
    'Dplus\\Wm\\Receiving\\Items' => $baseDir . '/src/Receiving/Items.php',
    'Dplus\\Wm\\Receiving\\Receiving' => $baseDir . '/src/Receiving/Receiving.php',
    'Dplus\\Wm\\Receiving\\Strategies\\CreatePo\\Allow' => $baseDir . '/src/Receiving/Strategies/CreatePo/Allow.php',
    'Dplus\\Wm\\Receiving\\Strategies\\CreatePo\\CreatePo' => $baseDir . '/src/Receiving/Strategies/CreatePo/CreatePo.interface.php',
    'Dplus\\Wm\\Receiving\\Strategies\\CreatePo\\Forbid' => $baseDir . '/src/Receiving/Strategies/CreatePo/Forbid.php',
    'Dplus\\Wm\\Receiving\\Strategies\\EnforcePoItemids\\EnforcePoItemids' => $baseDir . '/src/Receiving/Strategies/EnforcePoItemids/Enforce.interface.php',
    'Dplus\\Wm\\Receiving\\Strategies\\EnforcePoItemids\\Enforced' => $baseDir . '/src/Receiving/Strategies/EnforcePoItemids/Enforced.php',
    'Dplus\\Wm\\Receiving\\Strategies\\EnforcePoItemids\\Relaxed' => $baseDir . '/src/Receiving/Strategies/EnforcePoItemids/Relaxed.php',
    'Dplus\\Wm\\Receiving\\Strategies\\EnforceQty\\Enforced' => $baseDir . '/src/Receiving/Strategies/EnforceQty/Enforced.php',
    'Dplus\\Wm\\Receiving\\Strategies\\EnforceQty\\Relaxed' => $baseDir . '/src/Receiving/Strategies/EnforceQty/Relaxed.php',
    'Dplus\\Wm\\Receiving\\Strategies\\EnforceQty\\Warn' => $baseDir . '/src/Receiving/Strategies/EnforceQty/Warn.php',
    'Dplus\\Wm\\Receiving\\Strategies\\ReadQty\\LotserialQty' => $baseDir . '/src/Receiving/Strategies/ReadQty/LotserialQty.php',
    'Dplus\\Wm\\Receiving\\Strategies\\ReadQty\\LotserialSingle' => $baseDir . '/src/Receiving/Strategies/ReadQty/LotserialSingle.php',
    'Dplus\\Wm\\Receiving\\Strategies\\ReadQty\\ReadStrategy' => $baseDir . '/src/Receiving/Strategies/ReadQty/Base.php',
    'Dplus\\Wm\\Reports\\Inventory\\StockStatus' => $baseDir . '/src/Reports/Inventory/StockStatus.php',
    'Dplus\\Wm\\Reports\\Inventory\\StockStatus\\Export\\Spreadsheet' => $baseDir . '/src/Reports/Inventory/StockStatus/Export/Spreadsheet.php',
    'Dplus\\Wm\\Reports\\Inventory\\StockStatus\\Factory' => $baseDir . '/src/Reports/Inventory/StockStatus/Factory.php',
    'Dplus\\Wm\\Reports\\Inventory\\StockStatus\\Provalley' => $baseDir . '/src/Reports/Inventory/StockStatus/Provalley.php',
    'Dplus\\Wm\\Sop\\Picking\\AllocatedLots' => $baseDir . '/src/Sop/Picking/AllocatedLots.php',
    'Dplus\\Wm\\Sop\\Picking\\Inventory' => $baseDir . '/src/Sop/Picking/Inventory.php',
    'Dplus\\Wm\\Sop\\Picking\\Items' => $baseDir . '/src/Sop/Picking/Items.php',
    'Dplus\\Wm\\Sop\\Picking\\Picking' => $baseDir . '/src/Sop/Picking/Picking.php',
    'Dplus\\Wm\\Sop\\Picking\\Strategies\\Inventory\\Lookup\\ExcludePackBin' => $baseDir . '/src/Sop/Picking/Strategies/Inventory/Lookup/ExcludePackBin.php',
    'Dplus\\Wm\\Sop\\Picking\\Strategies\\Inventory\\Lookup\\IncludePackBin' => $baseDir . '/src/Sop/Picking/Strategies/Inventory/Lookup/IncludePackBin.php',
    'Dplus\\Wm\\Sop\\Picking\\Strategies\\Inventory\\Lookup\\Lookup' => $baseDir . '/src/Sop/Picking/Strategies/Inventory/Lookup/Lookup.php',
    'Dplus\\Wm\\Sop\\Picking\\Strategies\\PackBin\\Excluded' => $baseDir . '/src/Sop/Picking/Strategies/PackBin/Excluded.php',
    'Dplus\\Wm\\Sop\\Picking\\Strategies\\PackBin\\Included' => $baseDir . '/src/Sop/Picking/Strategies/PackBin/Included.php',
    'Dplus\\Wm\\Sop\\Picking\\Strategies\\PackBin\\PackBin' => $baseDir . '/src/Sop/Picking/Strategies/PackBin/PackBin.php',
);
