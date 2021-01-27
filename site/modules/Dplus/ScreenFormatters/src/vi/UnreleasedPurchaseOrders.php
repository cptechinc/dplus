<?php namespace Dplus\ScreenFormatters\Vi;

use Dplus\ScreenFormatters\ScreenFormatter;

class UnreleasedPurchaseOrders extends ScreenFormatter {
	const URI = 'vi:unrelased-purchase-orders';
	
	protected $code = 'vi-unreleased';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"header" => "Header",
		"detail" => "Detail"
	);
}
