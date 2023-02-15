<?php namespace Dplus\UserOptions;
// Propel Classes
	// use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as UserRecord;
	// use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use OptionsCiQuery, OptionsCi;
// ProcessWire
	use ProcessWire\WireInputData;
// Dplus
use Dplus\Codes\Min\Iwhm;
use Dplus\UserOptions\Response;

class Cio extends AbstractManager {
	const NAME = 'cio';
	const MODEL              = 'OptionsCi';
	const MODEL_KEY          = 'userid';
	const MODEL_TABLE        = 'Ci_options';
	const DESCRIPTION        = 'User Ci Options';
	const DESCRIPTION_RECORD = 'User Ci Options';
	const RECORDLOCKER_FUNCTION = 'cio';
	const DPLUS_TABLE           = 'CIO';
	const FIELD_ATTRIBUTES = [
		'userid'          => ['type' => 'text', 'maxlength' => 6],
		'notes'          => ['default' => 'Y', 'label' => 'Notes', 'date'=> false],
		'contacts'       => ['default' => 'Y', 'label' => 'Contact / Notes', 'date'=> false],
		'payments'       => ['default' => 'Y', 'label' => 'Payment', 'date'=> false],
		'corebank'       => ['default' => 'Y', 'label' => 'Core Bank', 'date'=> false],
		'credit'         => ['default' => 'Y', 'label' => 'Credit', 'date'=> false],
		'stock'          => ['default' => 'Y', 'label' => 'Customer Stock', 'date'=> false],
		'pricing'        => ['default' => 'Y', 'label' => 'Pricing', 'date'=> false],
		'standingorders' => ['default' => 'Y', 'label' => 'Standing Order', 'date'=> false],
		'salesorders'    => ['default' => 'Y', 'label' => 'Sales Order', 'date'=> false],
		'quotes'         => ['default' => 'Y', 'label' => 'Quote', 'date'=> false],
		'openinvoices'   => ['default' => 'Y', 'label' => 'Open Invoices', 'date'=> false],
		'customerpo'     => ['default' => 'Y', 'label' => 'Customer PO', 'date'=> true],
		'saleshistory'   => ['default' => 'Y', 'label' => 'Sales History', 'date'=> true],

		'dayscustomerpo'   => ['max' => 9999, 'default' => 365],
		'dayssaleshistory' => ['max' => 9999, 'default' => 365],
		'datecustomerpo'   => ['displayFormat' => 'm/d/Y', 'recordFormat' => 'Ymd'],
		'datesaleshistory' => ['displayFormat' => 'm/d/Y', 'recordFormat' => 'Ymd'],
	];

	const SCREENS = [
		'notes',
		'contacts',
		'payments',
		'corebank',
		'credit',
		'stock',
		'pricing',
		'standingorders',
		'salesorders',
		'quotes',
		'openinvoices',
		'customerpo',
		'saleshistory',
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
}
