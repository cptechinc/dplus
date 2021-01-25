<?php namespace Dplus\ScreenFormatters\Ii;

use Dplus\ScreenFormatters\ScreenFormatter;

class Quotes extends ScreenFormatter {
	protected $code = 'ii-quotes';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"header" => "Header",
		"detail" => "Detail"
	);
}
