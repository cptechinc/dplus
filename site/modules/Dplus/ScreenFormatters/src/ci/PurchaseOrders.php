<?php namespace Dplus\ScreenFormatters\Ci;

use Dplus\ScreenFormatters\ScreenFormatter;

class PurchaseOrders extends ScreenFormatter {
	const URI = 'ci:purchase-orders';

	protected $code = 'ci-customer-po';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		'header'  => 'Header',
		"details" => "Details",
		"totals"  => "Totals"
	);
}
