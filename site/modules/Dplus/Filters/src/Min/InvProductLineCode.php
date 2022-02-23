<?php namespace Dplus\Filters\Min;
// Dplus Model
use InvProductLineCodeQuery, InvProductLineCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for InvProductLineCodeQuery
 */
class InvProductLineCode extends CodeFilter {
	const MODEL = 'InvProductLineCode';
}
