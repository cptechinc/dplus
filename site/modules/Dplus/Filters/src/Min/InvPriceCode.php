<?php namespace Dplus\Filters\Min;
// Dplus Model
use InvPriceCodeQuery, InvPriceCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for InvPriceCodeQuery
 */
class InvPriceCode extends CodeFilter {
	const MODEL = 'InvPriceCode';
}
