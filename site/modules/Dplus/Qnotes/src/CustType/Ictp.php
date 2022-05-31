<?php namespace Dplus\Qnotes\CustType;
// Dplus Configs
use Dplus\Configs;
// ProcessWire
use ProcessWire\WireInput;

class Ictp extends Base {
	const TYPE                 = 'ICTP';
	const TYPE_DESCRIPTION     = 'Invoice';

	protected static $instance;

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Initialize Field Attributes
	 * NOTE: values may be set from configs
	 * @return void
	 */
	public function initFieldAttributes() {
		$this->fieldAttributes = static::FIELD_ATTRIBUTES;
		$configAR = Configs\Ar::config();
		$this->fieldAttributes['note']['cols'] = $configAR->columns_notes_invoice;
	}
}
