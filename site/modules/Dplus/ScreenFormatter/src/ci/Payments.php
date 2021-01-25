<?php namespace Dplus\ScreenFormatters\Ci;

use Dplus\ScreenFormatters\ScreenFormatter;

class Payments extends ScreenFormatter {

	protected $code = 'ci-payments';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"detail" => "Detail"
	);
}
