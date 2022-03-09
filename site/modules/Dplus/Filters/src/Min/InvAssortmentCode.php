<?php namespace Dplus\Filters\Min;
// Dplus Model
use InvAssortmentCodeQuery, InvAssortmentCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for InvAssortmentCodeQuery
 */
class InvAssortmentCode extends CodeFilter {
	const MODEL = 'InvAssortmentCode';
}
