<?php namespace Dplus\Filters\Mar;
// Dplus Model
use ArPriceCodeQuery, ArPriceCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for ArPriceCodeQuery
 */
class ArPriceCode extends CodeFilter {
	const MODEL = 'ArPriceCode';
}
