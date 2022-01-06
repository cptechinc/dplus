<?php namespace Dplus\DocManagement\Updater\It;

use ProcessWire\WireData;

use Dplus\DocManagement\Updater;

/**
 * Itmimg
 * Holds Document Data to send File Update Request for LOTIMG files
 *
 * @property string $itemID Item ID
 */
class Itmimg extends Updater {
	const TAG = 'IT';
	const FOLDER = 'ITMIMG';

	public function __construct() {
		parent::__construct();
		$this->itemID = '';
	}

	/**
	 * Map Field Properties from Other Properties
	 * @return void
	 */
	public function mapFields() {
		$this->field1 = $this->itemID;
	}

	/**
	 * Send Request
	 * @return bool
	 */
	public function update() {
		if (empty($this->itemID)) {
			return false;
		}
		return parent::update();
	}
}
