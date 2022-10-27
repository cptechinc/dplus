<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Ajax\Lookup as AjaxLookup;

	$page->searchURL = $input->url();
	$routes = [
		['GET', '', AjaxLookup::class, 'test'],
		// Tariff Codes
		['GET', 'tariff-codes/', AjaxLookup\Min::class, 'tariffCodes'],
		['GET', 'tariff-codes/page{pagenbr:\d+}/', AjaxLookup\Min::class, 'tariffCodes'],
		// Itm
		['GET', 'items', AjaxLookup\Min::class, 'itmItems'],
		['GET', 'items/page{pagenbr:\d+}/', AjaxLookup\Min::class, 'itmItems'],
		// Msds Codes
		['GET', 'msds-codes/', AjaxLookup\Min::class, 'msdsCodes'],
		['GET', 'msds-codes/page{pagenbr:\d+}/', AjaxLookup\Min::class, 'msdsCodes'],
		// Freight Codes
		['GET', 'freight-codes/', AjaxLookup\Mso::class, 'freightCodes'],
		['GET', 'freight-codes/page{pagenbr:\d+}/', AjaxLookup\Mso::class, 'freightCodes'],
		// VXM
		['GET', 'vxm/', AjaxLookup::class, 'vxm'],
		['GET', 'vxm/page{pagenbr:\d+}/', AjaxLookup::class, 'vxm'],
		// Warehouse
		['GET', 'warehouses/', AjaxLookup\Min::class, 'warehouses'],
		['GET', 'warehouses/page{pagenbr:\d+}/', AjaxLookup\Min::class, 'warehouses'],
		// Warehouse Bins
		['GET', 'warehouse/bins/', AjaxLookup\Min::class, 'warehouseBins'],
		['GET', 'warehouse/bins/page{pagenbr:\d+}/', AjaxLookup\Min::class, 'warehouseBins'],
		// User
		['GET', 'users/', AjaxLookup\Msa::class, 'users'],
		['GET', 'users/page{pagenbr:\d+}/', AjaxLookup\Msa::class, 'users'],
		// Vendors
		['GET', 'vendors/', AjaxLookup::class, 'vendors'],
		['GET', 'vendors/page{pagenbr:\d+}/', AjaxLookup::class, 'vendors'],
		// Vendor Contacts
		'vendor' => [
			['GET', 'contacts/', AjaxLookup::class, 'vendorContacts'],
			['GET', 'contacts/page{pagenbr:\d+}/', AjaxLookup::class, 'vendorContacts'],
			'ship-froms' => [
				['GET', '', AjaxLookup::class, 'vendorShipfroms'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup::class, 'vendorShipfroms'],
			]
		],
		// Purchase Orders
		['GET', 'purchase-orders/', AjaxLookup::class, 'purchaseOrders'],
		['GET', 'purchase-orders/page{pagenbr:\d+}/', AjaxLookup::class, 'purchaseOrders'],
		// General Ledger Codes
		['GET', 'gl-codes/', AjaxLookup\Mgl::class, 'generalLedgerCodes'],
		['GET', 'gl-codes/page{pagenbr:\d+}/', AjaxLookup\Mgl::class, 'generalLedgerCodes'],
		// Item Groups
		['GET', 'item-groups/', AjaxLookup\Min::class, 'itemGroups'],
		['GET', 'item-groups/page{pagenbr:\d+}/', AjaxLookup\Min::class, 'itemGroups'],
		// Customers
		['GET', 'customers/', AjaxLookup\Mar::class, 'customers'],
		['GET', 'customers/page{pagenbr:\d+}/', AjaxLookup\Mar::class, 'customers'],
		// Country Codes
		['GET', 'country-codes/', AjaxLookup\Misc::class, 'countryCodes'],
		['GET', 'country-codes/page{pagenbr:\d+}/', AjaxLookup\Misc::class, 'countryCodes'],
		'mar' => [
			'mtm' => [
				['GET', '', AjaxLookup\Mar::class, 'taxCodes'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Mar::class, 'taxCodes'],
			],
			'spgpm' => [
				['GET', '', AjaxLookup\Mar::class, 'salesPersonGroups'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Mar::class, 'salesPersonGroups'],
			],
			'tm' => [
				['GET', '', AjaxLookup\Mar::class, 'arCustTaxCodes'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Mar::class, 'arCustTaxCodes'],
			]
		],
		'mgl' => [
			// General Ledger Codes
			['GET', 'mhm/', AjaxLookup\Mgl::class, 'generalLedgerCodes'],
			['GET', 'mhm/page{pagenbr:\d+}/', AjaxLookup\Mgl::class, 'generalLedgerCodes'],
		],
		'min' => [
			'iplm' => [
				['GET', '', AjaxLookup\Min::class, 'productLines'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Min::class, 'productLines'],
			],
			'igm' => [
				['GET', '', AjaxLookup\Min::class, 'itemGroups'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Min::class, 'itemGroups'],
			],
		],
		'misc' => [
			'state-codes' => [
				['GET', '', AjaxLookup\Misc::class, 'stateCodes'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Misc::class, 'stateCodes'],
			],
			['GET', 'country-codes/', AjaxLookup\Misc::class, 'countryCodes'],
			['GET', 'country-codes/page{pagenbr:\d+}/', AjaxLookup\Misc::class, 'countryCodes'],
		],
		'mpo' => [
			['GET', 'ap-invoices/', AjaxLookup::class, 'purchaseOrders'],
			['GET', 'ap-invoices/page{pagenbr:\d+}/', AjaxLookup::class, 'purchaseOrders'],
		],
		'mpm' => [
			'dcm' => [
				['GET', 'codes/', AjaxLookup::class, 'dcmCodes'],
				['GET', 'codes/page{pagenbr:\d+}/', AjaxLookup::class, 'dcmCodes'],
			]
		],
		'msa' => [
			'sysop' => [
				['GET', '', AjaxLookup\Msa::class, 'sysopCodes'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Msa::class, 'sysopCodes'],
				'options' => [
					['GET', '', AjaxLookup\Msa::class, 'sysopOptions'],
					['GET', 'page{pagenbr:\d+}/', AjaxLookup\Msa::class, 'sysopOptions'],
				]
			],
			'printers' => [
				['GET', '', AjaxLookup\Msa::class, 'printers'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Msa::class, 'printers'],
			],
			'lgrp' => [
				['GET', '', AjaxLookup\Msa::class, 'loginGroups'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Msa::class, 'loginGroups'],
			],
			'lrole' => [
				['GET', '', AjaxLookup\Msa::class, 'loginRoles'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Msa::class, 'loginRoles'],
			]
		],
		'mth' => [
			'labels' => [
				['GET', '', AjaxLookup\Mth::class, 'labels'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Mth::class, 'labels'],
			],
		],
	];

	$router = new Mvc\Routers\Router();
	$router->setRoutes($routes);
	$router->setRoutePrefix($page->url);
	$page->body = $router->route();

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
