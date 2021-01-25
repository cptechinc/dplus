<?php namespace Dplus\ScreenFormatters\Ci;

use Dplus\ScreenFormatters\ScreenFormatter;

class SalesOrders extends ScreenFormatter {
	const URI = 'ci:sales-orders';

	protected $code = 'ci-sales-orders';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"header" => "Header",
		"detail" => "Detail",
		"itemstatus" => "Item Status",
		"purchaseorder" => "Purchase Order",
		"total" => "Total",
		"shipments" => "Shipments"
	);
}
