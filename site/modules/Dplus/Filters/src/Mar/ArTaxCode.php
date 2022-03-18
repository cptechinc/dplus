<?php namespace Dplus\Filters\Mar;
// Dplus Model
use ArTaxCodeQuery, ArTaxCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for ArTaxCodeQuery
 */
class ArTaxCode extends CodeFilter {
	const MODEL = 'ArTaxCode';
}
