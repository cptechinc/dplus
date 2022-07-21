<?php namespace Dplus\Qnotes;
// Dplus Configs
use Dplus\Configs;


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
