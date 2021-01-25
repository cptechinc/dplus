<?php namespace Dplus\ScreenFormatters\Vi;

use Dplus\ScreenFormatters\ScreenFormatter;

class PurchaseHistory extends ScreenFormatter {
	const URI = 'vi:purchase-history';
	
	protected $code = 'vi-purchase-history';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"detail" => "Detail"
	);
}
