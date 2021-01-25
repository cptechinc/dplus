<?php namespace Dplus\ScreenFormatters\Ii;

use Dplus\ScreenFormatters\ScreenFormatter;

class SalesOrders extends ScreenFormatter {
	const URI = 'ii:sales-orders';
	protected $code = 'ii-sales-orders';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"detail" => "Detail"
	);
}
