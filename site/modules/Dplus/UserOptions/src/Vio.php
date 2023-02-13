<?php namespace Dplus\UserOptions;
// Propel Classes
	// use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as UserRecord;
	// use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use OptionsViQuery, OptionsVi;
// ProcessWire
	// use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
	// use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus User Options
use Dplus\UserOptions\Response;

class Vio extends AbstractManager {
	const NAME = 'vio';
	const MODEL              = 'OptionsVi';
	const MODEL_KEY          = 'userid';
	const MODEL_TABLE        = 'vi_options';
	const DESCRIPTION        = 'User Vi Options';
	const DESCRIPTION_RECORD = 'User Vi Options';
	const RESPONSE_TEMPLATE  = 'User {userid} was {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'vio';
	const DPLUS_TABLE           = 'VIO';
	const FIELD_ATTRIBUTES = [
		'userid'          => ['type' => 'text', 'maxlength' => 6],
		'payments'        => ['default' => 'Y', 'label' => 'Payments', 'sanitizer' => 'ynbool'],
		'contacts'        => ['default' => 'Y', 'label' => 'Contacts', 'sanitizer' => 'ynbool'],
		'costing'         => ['default' => 'Y', 'label' => 'Costing', 'sanitizer' => 'ynbool'],
		'purchaseorders'  => ['default' => 'Y', 'label' => 'Purchase Orders', 'sanitizer' => 'ynbool'],
		'openinvoices'    => ['default' => 'Y', 'label' => 'Open Invoices', 'sanitizer' => 'ynbool'],
		'purchasehistory' => ['default' => 'Y', 'label' => 'Purchase History', 'sanitizer' => 'ynbool'],
		'unreleased'      => ['default' => 'Y', 'label' => 'Unreleased', 'sanitizer' => 'ynbool'],
		'uninvoiced'      => ['default' => 'Y', 'label' => 'Uninvoiced', 'sanitizer' => 'ynbool'],
		'notes'           => ['default' => 'Y', 'label' => 'Notes', 'sanitizer' => 'ynbool'],
		'summary'         => ['default' => 'Y', 'label' => '24 Month Summary', 'sanitizer' => 'ynbool'],
	];

	const SCREENS = [
		'contacts',
		'payments',
		'costing',
		'purchaseorders',
		'openinvoices',
		'purchasehistory',
		'unreleased',
		'uninvoiced',
		'notes',
		'summary',
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


/* =============================================================
	CRUD Response
============================================================= */
	/**
	 * Add Replacements, values for the Response Message
	 * @param UserRecord  $options   User Record
	 * @param Response    $response  Response
	 */
	protected function addResponseMsgReplacements(UserRecord $options, Response $response) {

	}
}
