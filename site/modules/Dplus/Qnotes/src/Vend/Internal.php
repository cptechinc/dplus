<?php namespace Dplus\Qnotes\Vend;
// Dolus Models
use VendorInternalNoteQuery, VendorInternalNote;
// Dplus Configs
use Dplus\Configs;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Qnotes
use Dplus\Qnotes\Qnotes;

/**
 * Class that Handles CRUD for VEND Internal Notes
 *
 * @method VendorInternalNote noteLine($vendorID, $shipfromID, $line = 1) Return Note Line
 * @method VendorInternalNote new($vendorID, $shipfromID)                 Return New Note
 */
class Internal extends Vend {
	const MODEL                = 'VendorInternalNote';
	const MODEL_KEY            = 'vendorid, sequence';
	const DESCRIPTION          = 'Vendor Internal Notes';
	const RESPONSE_TEMPLATE    = 'Vendor Internal Note {key} was {not} {crud}';
	const TYPE                 = 'VEND';
	const TABLE                = 'notes_vend_ship_internal';

	const FIELD_ATTRIBUTES = [
		'note' => ['type' => 'text', 'cols' => 50],
	];

	protected static $instance;

	/**
	 * Return Instance
	 * @return static
	 */
	public static function getInstance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Initialize Field Attributes
	 * NOTE: values may be set from configs
	 * @return void
	 */
	public function initFieldAttributes() {
		$attributes = static::FIELD_ATTRIBUTES;
		$attributes['notes']['cols'] = Configs\Ap::config()->colsNotesVend;
		return $this->fieldAttributes = $attributes;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */

/* =============================================================
	CRUD Create Functions
============================================================= */

/* =============================================================
	CRUD Delete Functions
============================================================= */

/* =============================================================
	CRUD Processing
============================================================= */

}
