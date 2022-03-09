<?php namespace Dplus\Qnotes;
// Dolus Models
use WarehouseNoteQuery, WarehouseNote;
// Dplus Configs
use Dplus\Configs;
// ProcessWire
use ProcessWire\WireInput;

class Swhs extends Iwhs {
	const TYPE                 = 'SWHS';
	const TYPE_DESCRIPTION     = 'Statement';

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
		$this->fieldAttributes['note']['cols'] = $configAR->columns_notes_statement;
	}
}
