<?php namespace Dplus\Qnotes\CustType;
// Dplus
use Dplus\Configs;

class Kctp extends AbstractCustType {
	const TYPE = 'KCTP';
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
