<?php namespace Dplus\ScreenFormatters\Vi;

use Dplus\ScreenFormatters\ScreenFormatter;

class OpenInvoices extends ScreenFormatter {
	const URI = 'vi:open-invoices';
	
	protected $code = 'vi-open-invoices';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"detail" => "Detail"
	);
}
