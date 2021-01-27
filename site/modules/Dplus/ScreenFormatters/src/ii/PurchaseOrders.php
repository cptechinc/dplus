<?php namespace Dplus\ScreenFormatters\Ii;

use Dplus\ScreenFormatters\ScreenFormatter;

class PurchaseOrders extends ScreenFormatter {
	const URI = 'ii:purchase-orders';

	protected $code = 'ii-purchase-orders';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"detail"    => "Detail",
		"lotserial" => "Lot / Serial"
	);
}
