<?php namespace Dplus\ScreenFormatters\Ci;

use Dplus\ScreenFormatters\ScreenFormatter;

class Quotes extends ScreenFormatter {
	const URI = 'ci:quotes';

	protected $code = 'ci-quotes';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		'header'  => 'Header',
		"details" => "Details",
		"totals"  => "Totals"
	);
}
