<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Ajax\Json as Json;

	$routes  = [
		'inv' => [
			'validate' => [
				['GET', 'tariff-code/', Json\Min::class, 'validateTariffCode'],
				['GET', 'country-code/', Json\Min::class, 'validateCountryCode'],
				['GET', 'msds-code/', Json\Min::class, 'validateMsdsCode'],
				['GET', 'itemid/', Json\Min::class, 'validateItemid'],
				['GET', 'warehouse/', Json\Min::class, 'validateWarehouseId'],
				['GET', 'itmp/exists/', Json\Min::class, 'validateItmpExists'],
				['GET', 'item-group/', Json\Min::class, 'validateInvGroupCode'],
			],
			['GET', 'tariff-code/', Json\Min::class, 'getTariffCode'],
			['GET', 'country-code/', Json\Min::class, 'getCountryCode'],
			['GET', 'msds-code/', Json\Min::class, 'getMsdsCode'],
			['GET', 'item/', Json\Min::class, 'getItm'],
		],
		'map' => [
			'validate' => [
				['GET', 'vendorid/', Json\Map::class, 'validateVendorid'],
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
			'vxm' => [
				['GET', '', Json\Map::class, 'getVxm'],
				['GET', 'itemid/', Json\Map::class, 'getVxmByItemid'],
				['GET', 'primary/', Json\Map::class, 'getVxmPrimary'],
			],
			['GET', 'vendor/', Json\Map::class, 'getVendor'],
			['GET', 'vendor/contact/', Json\Map::class, 'getVendorContact'],
		],
		'mar' => [
			'validate' => [
				['GET', 'salespersonid/', Json\Mar::class, 'validateSalesPersonId'],
				['GET', 'salesgroupid/', Json\Mar::class, 'validateSalesGroupid'],
				['GET', 'custid/', Json\Mar::class, 'validateCustid'],
			],
		],
		'mci' => [
			'validate' => [
				['GET', 'custid/', Json\Mci::class, 'validateCustid'],
			],
		],
		'mgl' => [
			'validate' => [
				['GET', 'gl-code/', Json\Mgl::class, 'validateGlCode'],
			],
			['GET', 'gl-code/', Json\Mgl::class, 'getGlCode'],
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
		'mpo' => [
			'validate' => [
				['GET', 'po/ponbr/', Json\Mpo::class, 'validatePonbr'],
			],
			['GET', 'po/item/', Json\Mpo::class, 'getPoItem'],
		],
		'msa' => [
			'validate' => [
				['GET', 'loginid/', Json\Msa::class, 'validateUserid'],
				['GET', 'userid/', Json\Msa::class, 'validateUserid'],
			],
			['GET', 'user/', Json\Msa::class, 'getUser'],
		],
		'mso' => [
			'validate' => [
				['GET', 'freight-code/', Json\Mso::class, 'validateFreightCode'],
				['GET', 'price-discount/', Json\Mso::class, 'validatePriceDiscount'],
				['GET', 'cxm/', Json\Mso::class, 'validateCxm'],
			],
			['GET', 'lowest-price/', Json\Mso::class, 'getLowestPrice'],
			'sales-order' => [
				['GET', 'line/', Json\Mso::class, 'getSalesOrderDetail'],
			],
		]
	];

	$router = new Mvc\JsonRouter();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$response = $router->route();

	echo json_encode($response);
