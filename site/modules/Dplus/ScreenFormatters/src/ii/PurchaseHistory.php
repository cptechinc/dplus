<?php namespace Dplus\ScreenFormatters\Ii;

use Dplus\ScreenFormatters\ScreenFormatter;

class PurchaseHistory extends ScreenFormatter {
	const URI = 'ii:purchase-history';

	protected $code = 'ii-purchase-history';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"detail"    => "Detail",
		"lotserial" => "Lot / Serial"
	);
}
