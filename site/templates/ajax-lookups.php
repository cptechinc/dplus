<?php
	include($modules->get('Mvc')->controllersPath().'vendor/autoload.php');
	use Controllers\Ajax\Lookup as AjaxLookup;

	$page->searchURL = $input->url();
	$routes = [
		['GET', '', AjaxLookup::class, 'test'],
		// Tariff Codes
		['GET', 'tariff-codes/', AjaxLookup::class, 'tariffCodes'],
		['GET', 'tariff-codes/page{pagenbr:\d+}/', AjaxLookup::class, 'tariffCodes'],
		// Itm
		['GET', 'items', AjaxLookup::class, 'itmItems'],
		['GET', 'items/page{pagenbr:\d+}/', AjaxLookup::class, 'itmItems'],
		// Msds Codes
		['GET', 'msds-codes/', AjaxLookup::class, 'msdsCodes'],
		['GET', 'msds-codes/page{pagenbr:\d+}/', AjaxLookup::class, 'msdsCodes'],
		// Freight Codes
		['GET', 'freight-codes/', AjaxLookup::class, 'freightCodes'],
		['GET', 'freight-codes/page{pagenbr:\d+}/', AjaxLookup::class, 'freightCodes'],
		// VXM
		['GET', 'vxm/', AjaxLookup::class, 'vxm'],
		['GET', 'vxm/page{pagenbr:\d+}/', AjaxLookup::class, 'vxm'],
		// Warehouse
		['GET', 'warehouses/', AjaxLookup::class, 'warehouses'],
		['GET', 'warehouses/page{pagenbr:\d+}/', AjaxLookup::class, 'warehouses'],
		// Warehouse Bins
		['GET', 'warehouse/bins/', AjaxLookup::class, 'warehouseBins'],
		['GET', 'warehouse/bins/page{pagenbr:\d+}/', AjaxLookup::class, 'warehouseBins'],
		// User
		['GET', 'users/', AjaxLookup::class, 'users'],
		['GET', 'users/page{pagenbr:\d+}/', AjaxLookup::class, 'users'],
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
		['GET', 'gl-codes/', AjaxLookup::class, 'generalLedgerCodes'],
		['GET', 'gl-codes/page{pagenbr:\d+}/', AjaxLookup::class, 'generalLedgerCodes'],
		// Item Groups
		['GET', 'item-groups/', AjaxLookup::class, 'itemGroups'],
		['GET', 'item-groups/page{pagenbr:\d+}/', AjaxLookup::class, 'itemGroups'],
		// Customers
		['GET', 'customers/', AjaxLookup::class, 'customers'],
		['GET', 'customers/page{pagenbr:\d+}/', AjaxLookup::class, 'customers'],
		// Country Codes
		['GET', 'country-codes/', AjaxLookup::class, 'countryCodes'],
		['GET', 'country-codes/page{pagenbr:\d+}/', AjaxLookup::class, 'countryCodes'],
		'mar' => [
			// Salesperson Group Codes
			['GET', 'spgpm/', AjaxLookup\Mar::class, 'salespersonGroupCodes'],
			['GET', 'spgpm/page{pagenbr:\d+}/', AjaxLookup\Mar::class, 'salespersonGroupCodes'],
			// Salesperson Group Codes
			['GET', 'spm/', AjaxLookup\Mar::class, 'salespersonCodes'],
			['GET', 'spm/page{pagenbr:\d+}/', AjaxLookup\Mar::class, 'salespersonCodes'],
		],
		'mgl' => [
			// General Ledger Codes
			['GET', 'mhm/', AjaxLookup::class, 'generalLedgerCodes'],
			['GET', 'mhm/page{pagenbr:\d+}/', AjaxLookup::class, 'generalLedgerCodes'],
		],
		'min' => [
			'iplm' => [
				['GET', '', AjaxLookup\Min::class, 'productLines'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup\Min::class, 'productLines'],
			]
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
				['GET', '', AjaxLookup::class, 'sysopCodes'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup::class, 'sysopCodes'],
				'options' => [
					['GET', '', AjaxLookup::class, 'sysopOptions'],
					['GET', 'page{pagenbr:\d+}/', AjaxLookup::class, 'sysopOptions'],
				]
			],
			'printers' => [
				['GET', '', AjaxLookup::class, 'printers'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup::class, 'printers'],
			],
			'lgrp' => [
				['GET', '', AjaxLookup::class, 'loginGroups'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup::class, 'loginGroups'],
			],
			'lrole' => [
				['GET', '', AjaxLookup::class, 'loginRoles'],
				['GET', 'page{pagenbr:\d+}/', AjaxLookup::class, 'loginRoles'],
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
