<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Ajax\Json as Json;

	$routes  = [
		['GET', 'item-lookup/', Json\ItemLookup::class, 'lookup'],
		'misc' => [
			['GET', 'time/', Json\Misc::class, 'time'],
			['GET', 'date/', Json\Misc::class, 'date'],
			['GET', 'date-time/', Json\Misc::class, 'dateTime'],
			'validate' => [
				['GET', 'printer/', Json\Misc::class, 'validatePrinter'],
			]
		],
		'inv' => [
			['GET', 'csccm/code/', Json\Min::class, 'getCsccmCode'],
			['GET', 'iasm/code/', Json\Min::class, 'getIasmCode'],
			['GET', 'igcm/code/', Json\Min::class, 'getIgcmCode'],
			['GET', 'igm/code/', Json\Min::class, 'getIgmCode'],
			['GET', 'igpm/code/', Json\Min::class, 'getIgpmCode'],
			['GET', 'iplm/code/', Json\Min::class, 'getIplmCode'],
			['GET', 'itmp/user/', Json\Min::class, 'getItmpUser'],
			'iwhm' => [
				['GET', 'code/', Json\Min::class, 'getIwhmCode'],
				['GET', 'whse/', Json\Min::class, 'getIwhmCode'],
			],
			['GET', 'msdsm/code/', Json\Min::class, 'getMsdsmCode'],
			['GET', 'spit/code/', Json\Min::class, 'getSpitCode'],
			['GET', 'stcm/code/', Json\Min::class, 'getStcmCode'],
			['GET', 'tarm/code/', Json\Min::class, 'getTarmCode'],
			['GET', 'umm/code/', Json\Min::class, 'getUmmCode'],
			'validate' => [
				['GET', 'csccm/code/', Json\Min::class, 'validateCsccmCode'],
				['GET', 'iasm/code/', Json\Min::class, 'validateIasmCode'],
				['GET', 'igcm/code/', Json\Min::class, 'validateIgcmCode'],
				['GET', 'igm/code/', Json\Min::class, 'validateIgmCode'],
				['GET', 'igpm/code/', Json\Min::class, 'validateIgpmCode'],
				['GET', 'iplm/code/', Json\Min::class, 'validateIplmCode'],
				'iwhm' => [
					['GET', 'code/', Json\Min::class, 'validateIwhmCode'],
					['GET', 'whse/', Json\Min::class, 'validateIwhmCode'],
				],
				['GET', 'msdsm/code/', Json\Min::class, 'validateMsdsmCode'],
				['GET', 'spit/code/', Json\Min::class, 'validateSpitCode'],
				['GET', 'stcm/code/', Json\Min::class, 'validateStcmCode'],
				['GET', 'tarm/code/', Json\Min::class, 'validateTarmCode'],
				['GET', 'umm/code/', Json\Min::class, 'validateUmmCode'],
				['GET', 'tariff-code/', Json\Min::class, 'validateTariffCode'],
				['GET', 'country-code/', Json\Mar::class, 'validateCocomCode'],
				['GET', 'msds-code/', Json\Min::class, 'validateMsdsCode'],
				['GET', 'itemid/', Json\Min::class, 'validateItemid'],
				'warehouse' => [
					['GET', '', Json\Min::class, 'validateWarehouseid'],
					['GET', 'bin/', Json\Min::class, 'validateWarehouseBinid'],
				],
				['GET', 'itmp/exists/', Json\Min::class, 'validateItmpExists'],
				['GET', 'item-group/', Json\Min::class, 'validateInvGroupCode'],
				'upcx' => [
					['GET', 'upc/', Json\Min::class, 'validateUpc'],
					['GET', 'upc/primary/', Json\Min::class, 'validateUpcPrimary'],
					['GET', 'upc/xref/', Json\Min::class, 'validateUpcXref'],
				],
				'code' => [
					['GET', 'stock/', Json\Min::class, 'validateStockCode'],
					['GET', 'special-item/', Json\Min::class, 'validateSpecialItemCode'],
				],
				'i2i' => [
					['GET', 'xref/', Json\Min::class, 'validateI2iExists'],
				],
				'iarn' => [
					['GET', 'code/', Json\Min::class, 'validateIarnExists'],
				],
				'itm' => [
					['GET', 'warehouse/', Json\Min::class, 'validateItmWhse'],
					['GET', 'substitute/', Json\Min::class, 'validateItmSub'],
					'xrefs' => [
						['GET', 'shortitemid/', Json\Min::class, 'validateItmShortitemid'],
						['GET', 'shortitemid/available/', Json\Min::class, 'validateItmShortitemidAvailable'],
					]
				],
				'addm' => [
					['GET', 'xref/', Json\Min::class, 'validateAddm'],
				],
			],
			['GET', 'tariff-code/', Json\Min::class, 'getTariffCode'],
			['GET', 'country-code/', Json\Mar::class, 'getCocomCode'],
			['GET', 'msds-code/', Json\Min::class, 'getMsdsCode'],
			['GET', 'item/', Json\Min::class, 'getItm'],
			'upcx' => [
				['GET', 'primary/', Json\Min::class, 'getPrimaryUpc'],
			],
			'umm' => [
				['GET', 'uom/', Json\Min::class, 'getUom'],
			],
			'itm' => [
				'options' => [
					['GET', 'notes/', Json\Min::class, 'getInvOptCodeNotes'],
				]
			]
		],
		'map' => [
			'bum' => [
				['GET', 'code/', Json\Map::class, 'getBumCode'],
			],
			'validate' => [
				'bum' => [
					['GET', 'code/', Json\Map::class, 'validateBumCode'],
				],
				['GET', 'vendorid/', Json\Map::class, 'validateVendorid'],
				'vendor' => [
					['GET', 'shipfromid/', Json\Map::class, 'validateVendorShipfromid'],
				],
				'vtm' => [
					['GET', 'code/', Json\Map::class, 'validateVtmCode'],
				],
				'vxm' => [
					['GET', '', Json\Map::class, 'validateVxm'],
					['GET', 'itemid/', Json\Map::class, 'validateVxmExistsForItemid'],
					['GET', 'vendoritemid-itemid/', Json\Map::class, 'validateVendoritemMatchesItemid'],
					['GET', 'primary-ordercode/', Json\Map::class, 'validateVxmCanBePrimary'],
					['GET', 'update-itm-cost/', Json\Map::class, 'validateVxmUpdateItmCost'],
				],
				['GET', 'mxrfe/', Json\Map::class, 'validateMxrfe'],
				['GET', 'mxrfe/new/', Json\Map::class, 'validateMxrfeNew'],
			],
			'vtm' => [
				['GET', 'code/', Json\Map::class, 'getVtmCode'],
			],
			'vxm' => [
				['GET', '', Json\Map::class, 'getVxm'],
				['GET', 'itemid/', Json\Map::class, 'getVxmByItemid'],
				['GET', 'primary/', Json\Map::class, 'getVxmPrimary'],
			],
			'vendor' => [
				['GET', '', Json\Map::class, 'getVendor'],
				['GET', 'contact/', Json\Map::class, 'getVendorContact'],
			],
		],
		'mar' => [
			'validate' => [
				['GET', 'salespersonid/', Json\Mar::class, 'validateSalesPersonId'],
				['GET', 'salesgroupid/', Json\Mar::class, 'validateSalesGroupid'],
				['GET', 'custid/', Json\Mar::class, 'validateCmmId'],
				['GET', 'ccm/code/', Json\Mar::class, 'validateCcmCode'],
				['GET', 'cmm/id/', Json\Mar::class, 'validateCmmId'],
				['GET', 'cocom/code/', Json\Mar::class, 'validateCocomCode'],
				['GET', 'cpm/code/', Json\Mar::class, 'validateCpmCode'],
				['GET', 'crcd/code/', Json\Mar::class, 'validateCrcdCode'],
				['GET', 'crtm/code/', Json\Mar::class, 'validateCrtmCode'],
				['GET', 'csv/code/', Json\Mar::class, 'validateCsvCode'],
				['GET', 'ctm/code/', Json\Mar::class, 'validateCtmCode'],
				['GET', 'cuc/code/', Json\Mar::class, 'validateCucCode'],
				['GET', 'mtm/code/', Json\Mar::class, 'validateMtmCode'],
				['GET', 'spgpm/code/', Json\Mar::class, 'validateSpgpmCode'],
				['GET', 'spm/code/', Json\Mar::class, 'validateSpmCode'],
				['GET', 'sic/code/', Json\Mar::class, 'validateSicCode'],
				['GET', 'suc/code/', Json\Mar::class, 'validateSucCode'],
				['GET', 'trm/code/', Json\Mar::class, 'validateTrmCode'],
				['GET', 'worm/code/', Json\Mar::class, 'validateWormCode'],
			],
			['GET', 'customer/', Json\Mar::class, 'getCmmCustomer'],
			['GET', 'customer/shipto/', Json\Mar::class, 'getCustomerShipto'],
			['GET', 'ccm/code/', Json\Mar::class, 'getCcmCode'],
			['GET', 'cmm/customer/', Json\Mar::class, 'getCmmCustomer'],
			['GET', 'cpm/code/', Json\Mar::class, 'getCpmCode'],
			['GET', 'cocom/code/', Json\Mar::class, 'getCocomCode'],
			['GET', 'crcd/code/', Json\Mar::class, 'getCrcdCode'],
			['GET', 'crtm/code/', Json\Mar::class, 'getCrtmCode'],
			['GET', 'csv/code/', Json\Mar::class, 'getCsvCode'],
			['GET', 'ctm/code/', Json\Mar::class, 'getCtmCode'],
			['GET', 'cuc/code/', Json\Mar::class, 'getCucCode'],
			['GET', 'mtm/code/', Json\Mar::class, 'getMtmCode'],
			['GET', 'spgpm/code/', Json\Mar::class, 'getSpgpmCode'],
			['GET', 'spm/code/', Json\Mar::class, 'getSpmCode'],
			['GET', 'sic/code/', Json\Mar::class, 'getSicCode'],
			['GET', 'suc/code/', Json\Mar::class, 'getSucCode'],
			['GET', 'trm/code/', Json\Mar::class, 'getTrmCode'],
			['GET', 'worm/code/', Json\Mar::class, 'getWormCode'],
		],
		'mci' => [
			'validate' => [
				['GET', 'custid/', Json\Mci::class, 'validateCustid'],
			],
		],
		'mdm' => [
			'docvwr' => [
				['GET', 'copy/', Json\Mdm::class, 'copyFile'],
			]
		],
		'mgl' => [
			'validate' => [
				['GET', 'gl-code/', Json\Mgl::class, 'validateGlCode'],
				['GET', 'ttm/code/', Json\Mgl::class, 'validateStmtCode'],
				['GET', 'dtm/code/', Json\Mgl::class, 'validateDistCode'],
			],
			['GET', 'gl-code/', Json\Mgl::class, 'getGlCode'],
			['GET', 'ttm/code/', Json\Mgl::class, 'getStmtCode'],
			['GET', 'dtm/code/', Json\Mgl::class, 'getDistCode'],
		],
		'mki' => [
			'validate' => [
				['GET', 'kit/', Json\Mki::class, 'validateKitid'],
				['GET', 'kit/new/', Json\Mki::class, 'validateKitidNew'],
				['GET', 'kit/delete/', Json\Mki::class, 'validateKitDeletion'],
				['GET', 'kit/component/', Json\Mki::class, 'validateKitComponent'],
			],
			['GET', 'kit/', Json\Mki::class, 'getKit'],
		],
		'mpm' => [
			'validate' => [
				'bmm' => [
					['GET', 'component/', Json\Mpm::class, 'validateBomComponent'],
					['GET', 'component/exists/', Json\Mpm::class, 'validateBomComponent'],
				],
				'dcm' => [
					['GET', 'code/', Json\Mpm::class, 'validatePrWorkCenterExists'],
				],
				'rcm' => [
					['GET', 'code/', Json\Mpm::class, 'validatePrResourceExists'],
				],
			],
			'dcm' => [
				['GET', 'code/', Json\Mpm::class, 'getPrWorkCenter'],
			],
			'rcm' => [
				['GET', 'code/', Json\Mpm::class, 'getPrResource'],
			],
		],
		'mpo' => [
			'validate' => [
				['GET', 'po/ponbr/', Json\Mpo::class, 'validatePonbr'],
				['GET', 'cnfm/', Json\Mpo::class, 'validateCnfmCode'],
			],
			['GET', 'po/item/', Json\Mpo::class, 'getPoItem'],
			'cnfm' => [
				['GET', 'code/', Json\Mpo::class, 'getCnfmCode'],
			]
		],
		'mpr' => [
			'validate' => [
				'src' => [
					['GET', 'code/', Json\Mpr::class, 'validateSourceExists'],
				],
			],
			'src' => [
				['GET', 'code/', Json\Mpr::class, 'getSource'],
			]
		],
		'msa' => [
			'validate' => [
				'logm' => [
					['GET', 'id/', Json\Msa::class, 'validateUserid'],
				],
				['GET', 'lgrp/', Json\Msa::class, 'validateLgrp'],
				['GET', 'loginid/', Json\Msa::class, 'validateUserid'],
				['GET', 'userid/', Json\Msa::class, 'validateUserid'],
				['GET', 'roleid/', Json\Msa::class, 'validateRoleid'],
				['GET', 'noce/', Json\Msa::class, 'validateNoceid'],
				['GET', 'printer/', Json\Msa::class, 'validatePrinter'],
				'sysop' => [
					['GET', '', Json\Msa::class, 'validateSysop'],
					['GET', 'system/', Json\Msa::class, 'validateSysopSystem'],
					['GET', 'option/', Json\Msa::class, 'validateSysopOption'],
					['GET', 'notecode/', Json\Msa::class, 'validateSysopNotecode'],
				]
			],
			'logm' => [
				['GET', 'user/', Json\Msa::class, 'getUser'],
			],
			['GET', 'user/', Json\Msa::class, 'getUser'],
			['GET', 'lgrp/', Json\Msa::class, 'getLgrp'],
			['GET', 'noce/', Json\Msa::class, 'getNoceNote'],
			'sysop' => [
				['GET', '', Json\Msa::class, 'getSysop'],
				['GET', 'option/', Json\Msa::class, 'getSysopOption'],
				'system' => [
					['GET', 'required-codes/', Json\Msa::class, 'getSysopRequiredCodes'],
				]
			]
		],
		'mso' => [
			'lsm' => [
				['GET', 'code/', Json\Mso::class, 'getLsmCode'],
			],
			['GET', 'mfcm/code/', Json\Mso::class, 'getMfcmCode'],
			['GET', 'rgarc/code/', Json\Mso::class, 'getRgarcCode'],
			['GET', 'rgasc/code/', Json\Mso::class, 'getRgascCode'],
			'validate' => [
				['GET', 'freight-code/', Json\Mso::class, 'validateFreightCode'],
				['GET', 'price-discount/', Json\Mso::class, 'validatePriceDiscount'],
				['GET', 'cxm/', Json\Mso::class, 'validateCxm'],
				'lsm' => [
					['GET', 'code/', Json\Mso::class, 'validateLsmCode'],
				],
				['GET', 'mfcm/code/', Json\Mso::class, 'validateMfcmCode'],
				['GET', 'rgarc/code/', Json\Mso::class, 'validateRgarcCode'],
				['GET', 'rgasc/code/', Json\Mso::class, 'validateRgascCode'],
			],
			['GET', 'lowest-price/', Json\Mso::class, 'getLowestPrice'],
			'sales-order' => [
				['GET', 'line/', Json\Mso::class, 'getSalesOrderDetail'],
			],
			['GET', 'freight-code/', Json\Mso::class, 'getFreightCode'],
			'eso' => [
				['GET', 'pricing/', Json\Mso::class, 'getPricing'],
			],
		],
		'mqo' => [
			'quote' => [
				'edit' => [
					['POST', '', Json\Mqo::class, 'editQuote'],
				]
			]
		],
		'wm' => [
			'receiving' => [
				'validate' => [
					['GET', 'require-lotserial/', Json\Wm\Receiving::class, 'requireLotserial'],
					['GET', 'allow-itemid/', Json\Wm\Receiving::class, 'allowItemOnOrder'],
					['GET', 'add-qty-warning/', Json\Wm\Receiving::class, 'doesQtyAddNeedWarning'],
				],
				'items' => [
					['GET', '', Json\Wm\Receiving::class, 'test'],
					['GET', 'line/', Json\Wm\Receiving::class, 'getLine'],
					['GET', 'lotserial/', Json\Wm\Receiving::class, 'getLineLotserial'],
				]
			],
		],
	];
	$routes['min'] = $routes['inv'];
	$router = new Mvc\Routers\Json();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$response = $router->route();

	echo json_encode($response);
