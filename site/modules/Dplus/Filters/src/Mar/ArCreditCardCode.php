<?php namespace Dplus\Filters\Mar;
// Dplus Model
use ArCreditCardCodeQuery, ArCreditCardCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for ArCreditCardCodeQuery
 */
class ArCreditCardCode extends CodeFilter {
	const MODEL = 'ArCreditCardCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('id'),
			Model::get_aliasproperty('description'),
			Model::get_aliasproperty('custid'),
			Model::get_aliasproperty('glaccountnbr'),
			Model::get_aliasproperty('glchargeaccountnbr'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}
}
