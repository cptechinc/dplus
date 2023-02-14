<?php namespace Dplus\UserOptions;
// Propel Classes
	// use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as UserRecord;
	// use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use OptionsIiQuery, OptionsIi;
// ProcessWire
	use ProcessWire\WireInputData;
// Dplus
use Dplus\Codes\Min\Iwhm;
use Dplus\UserOptions\Response;

class Iio extends AbstractManager {
	const NAME = 'iio';
	const MODEL              = 'OptionsIi';
	const MODEL_KEY          = 'userid';
	const MODEL_TABLE        = 'ii_options';
	const DESCRIPTION        = 'User Ii Options';
	const DESCRIPTION_RECORD = 'User Ii Options';
	const RECORDLOCKER_FUNCTION = 'iio';
	const DPLUS_TABLE           = 'IIO';
	const FIELD_ATTRIBUTES = [
		'userid'          => ['type' => 'text', 'maxlength' => 6],
		'activity'        => ['default' => 'Y', 'label' => 'Activity', 'whse'=> true,  'detail'=> true,  'date'=> true],
		'cost'            => ['default' => 'Y', 'label' => 'Costing', 'whse'=> true,  'detail'=> true,  'date'=> false],
		'general'         => ['default' => 'Y', 'label' => 'General', 'whse'=> false, 'detail'=> false, 'date'=> false],
		'kit'             => ['default' => 'Y', 'label' => 'Kits/BOM', 'whse'=> false, 'detail'=> false, 'date'=> false],
		'pricing'         => ['default' => 'Y', 'label' => 'Pricing', 'whse'=> false, 'detail'=> false, 'date'=> false],
		'purchasehistory' => ['default' => 'Y', 'label' => 'Purchase History', 'whse'=> true,  'detail'=> true,  'date'=> true],
		'purchaseorders'  => ['default' => 'Y', 'label' => 'Purchase Orders',  'whse'=> true,  'detail'=> false, 'date'=> false],
		'requirements'    => ['default' => 'Y', 'label' => 'Requirement', 'whse'=> true,  'detail'=> true,  'date'=> false],
		'saleshistory'    => ['default' => 'Y', 'label' => 'Sales History',  'whse'=> true,  'detail'=> true,  'date'=> true],
		'salesorders'     => ['default' => 'Y', 'label' => 'Sales Orders',  'whse'=> true,  'detail'=> false, 'date'=> false],
		'lotserial'       => ['default' => 'Y', 'label' => 'Serial / Lot Search',  'whse'=> false,  'detail'=> false, 'date'=> false],
		'stock'           => ['default' => 'Y', 'label' => 'Stock Status',  'whse'=> true,  'detail'=> true,  'date'=> false],
		'substitutes'     => ['default' => 'Y', 'label' => 'Subtitutes / Supercede',  'whse'=> true,  'detail'=> false, 'date'=> false],
		'lostsales'       => ['default' => 'Y', 'label' => 'Lost Sales / Quote',  'whse'=> true,  'detail'=> false, 'date'=> false],

		'daysactivity'        => ['max' => 9999, 'default' => 365],
		'dayspurchasehistory' => ['max' => 9999, 'default' => 365],
		'dayssaleshistory'    => ['max' => 9999, 'default' => 365],

		'dateactivity'        => ['displayFormat' => 'm/d/Y', 'recordFormat' => 'Ymd'],
		'datepurchasehistory' => ['displayFormat' => 'm/d/Y', 'recordFormat' => 'Ymd'],
		'datesaleshistory'    => ['displayFormat' => 'm/d/Y', 'recordFormat' => 'Ymd'],

		'detailactivity'      => ['default' => 'Y', 'options' => ['Y' => 'Yes', 'N' => 'No']], 
		'detailcost'          => ['default' => 'Y', 'options' => ['Y' => 'Yes', 'N' => 'No']], 
		'detailpurchasehistory' => ['default' => 'Y', 'options' => ['Y' => 'Yes', 'N' => 'No']], 
		'detailrequirements'    => ['default' => 'R', 'options' => ['A' => 'Available to Promise', 'R' => 'Requirement']],
		'detailsaleshistory'    => ['default' => 'Y', 'options' => ['Y' => 'Yes', 'N' => 'No']], 
		'detailstock'           => ['default' => 'Y', 'options' => ['Y' => 'Yes', 'N' => 'No']], 
		'itemdescription'       => ['default' => 1, 'options' => [1 => 1, 2 => 2]],
		'deleteloticerts'       => ['default' => 'N', 'options' => ['Y' => 'Yes', 'N' => 'No']],
	];

	const SCREENS = [
		'activity',
		'cost',
		'general',
		'kit',
		'pricing',
		'purchasehistory',
		'purchaseorders',
		'requirements',
		'saleshistory',
		'salesorders',
		'lotserial',
		'stock',
		'substitutes',
		'lostsales',
	];

	protected static $instance;

	/**
	 * Return Instance
	 * @return self
	 */
	public static function getInstance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

/* =============================================================
	CRUD Reads
============================================================= */


/* =============================================================
	CRUD Creates
============================================================= */


/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record fields
	 * @param  UserRecord    $user
	 * @param  WireInputData $values
	 * @return void
	 */
	protected function _inputUpdate(UserRecord $user, WireInputData $values) {
		parent::_inputUpdate($user, $values);
		$this->setUserOptions($user, $values);
	}

	/**
	 * Set User Screens' Detail
	 * @param  UserRecord    $user
	 * @param  WireInputData $values
	 * @return void
	 */
	protected function setUserOptions(UserRecord $user, WireInputData $values) {
		$options = ['itemdescription', 'deleteloticerts'];
		foreach ($options as $option) {
			$user->set($option, $this->fieldAttribute($option, 'default'));
			
			if ($values->option($option, array_keys($this->fieldAttribute($option, 'options'))) === false) {
				continue;
			}
			$user->set($option, $values->option($option, array_keys($this->fieldAttribute($option, 'options'))));
		}
	}
}
