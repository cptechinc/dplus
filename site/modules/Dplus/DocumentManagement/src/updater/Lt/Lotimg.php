<?php namespace Dplus\DocManagement\Updater\Lt;

use ProcessWire\WireData;

use Dplus\DocManagement\Updater;

/**
 * Lotimg
 * Holds Document Data to send File Update Request for LOTIMG files
 *
 * @property string $lotserial Lot / Serial Nbr
 */
class Lotimg extends Updater {
	const TAG = 'LT';
	const FOLDER = 'LOTIMG';

	public function __construct() {
		parent::__construct();
		$this->lotserial = '';
	}

	/**
	 * Map Field Properties from Other Properties
	 * @return void
	 */
	public function mapFields() {
		$this->field1 = $this->lotserial;
	}

	/**
	 * Send Request
	 * @return bool
	 */
	public function update() {
		if (empty($this->lotserial)) {
			return false;
		}
		return parent::update();
	}
}
