<?php namespace Dplus\ScreenFormatters\Ci;

use Dplus\ScreenFormatters\ScreenFormatter;

class SalesHistory extends ScreenFormatter {
	const URI = 'ci:sales-history';

	protected $code = 'ci-sales-history';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"header" => "Header",
		"detail" => "Detail",
		"lotserial" => "Lot / Serial",
		"total" => "Totals",
		"shipments" => "Shipments"
	);
}
