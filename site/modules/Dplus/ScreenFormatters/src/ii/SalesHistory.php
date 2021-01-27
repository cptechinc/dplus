<?php namespace Dplus\ScreenFormatters\Ii;

use Dplus\ScreenFormatters\ScreenFormatter;

class SalesHistory extends ScreenFormatter {
	const URI = 'ii:sales-history';
	
	protected $code = 'ii-sales-history';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"detail" => "Detail",
		"lotserial" => "Lot / Serial"
	);
}
