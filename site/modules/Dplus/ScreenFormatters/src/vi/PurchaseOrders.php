<?php namespace Dplus\ScreenFormatters\Vi;

use Dplus\ScreenFormatters\ScreenFormatter;

class PurchaseOrders extends ScreenFormatter {
	const URI = 'vi:purchase-orders';
	
	protected $code = 'vi-purchase-orders';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"header" => "Header",
		"detail" => "Detail"
	);
}
