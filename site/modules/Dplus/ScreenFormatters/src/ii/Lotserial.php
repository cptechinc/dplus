<?php namespace Dplus\ScreenFormatters\Ii;

use Dplus\ScreenFormatters\ScreenFormatter;

class Lotserial extends ScreenFormatter {
	const URI = 'ii:lotserial';
	
	protected $code = 'ii-lotserial';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"lots" => "Lots"
	);

	/**
	 * Generates the celldata based of the column, column type and the json array it's in, looks at if the data is numeric
	 * @param string $data   the array in which the data is contained
	 * @param string $column the key in which we use to look up the value, may contain the type
	 */
	public function format_celldata($data, $column, $type = '') {
		if ($column['id'] != 'daysaged') {
			return parent::format_celldata($data, $column);
		}
		$pdate = new \DateTime($data['expire date']);
		$today = new \DateTime();
		return $today->diff($pdate)->format("%a");
	}
}
