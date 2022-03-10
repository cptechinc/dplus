<?php namespace Dplus\Filters\Mar;
// Dplus Model
use ArCommissionCodeQuery, ArCommissionCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for ArCommissionCodeQuery
 */
class ArCommissionCode extends CodeFilter {
	const MODEL = 'ArCommissionCode';
}
