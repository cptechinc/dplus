<?php namespace Dplus\Filters\Mso;
// Dplus Model
use MotorFreightCodeQuery, MotorFreightCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for MotorFreightCodeQuery
 */
class MotorFreightCode extends CodeFilter {
	const MODEL = 'MotorFreightCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('id'),
			Model::aliasproperty('class'),
			Model::aliasproperty('description'),
			Model::aliasproperty('description2'),
			Model::aliasproperty('description3'),
			Model::aliasproperty('description4'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}
}
