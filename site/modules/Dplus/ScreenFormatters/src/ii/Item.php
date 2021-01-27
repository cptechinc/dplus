<?php namespace Dplus\ScreenFormatters\Ii;

use Dplus\ScreenFormatters\ScreenFormatter;

class Item extends ScreenFormatter {
	const URI = 'ii:item';
	
	protected $code = 'ii-item';

	/**
	 * Key Value array of Sections that exist I.E. header => Header, detail => Detail
	 * @var string
	 */
	protected $datasections = array(
		"item" => "item",
	);

	/**
	 * Return if the Column weight is formatted
	 * @return bool
	 */
	public function is_weight_formatted() {
		return $this->is_column_formatted('Weight');
	}

	/**
	 * Return if the Column is formatted
	 * @return bool
	 */
	public function is_column_formatted($col) {
		$formatter = $this->get_formatter();
		if (array_key_exists($col, $formatter['item']['columns']) == false) {
			return false;
		}
		$column = $formatter['item']['columns'][$col];
		if ($column['line'] == 0 || $column['column'] == 0) {
			return false;
		}
		return true;
	}
}
