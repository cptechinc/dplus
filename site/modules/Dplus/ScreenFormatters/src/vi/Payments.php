<?php namespace Dplus\ScreenFormatters\Vi;

use Dplus\ScreenFormatters\ScreenFormatter;

class Payments extends ScreenFormatter {
	const URI = 'vi:payments';

	protected $code = 'vi-payments';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"detail" => "Detail"
	);
}
