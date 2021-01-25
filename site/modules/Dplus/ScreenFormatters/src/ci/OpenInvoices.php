<?php namespace Dplus\ScreenFormatters\Ci;

use Dplus\ScreenFormatters\ScreenFormatter;

class OpenInvoices extends ScreenFormatter {
	const URI = 'ci:open-invoices';

	protected $code = 'ci-open-invoices';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"detail" => "Detail"
	);
}
