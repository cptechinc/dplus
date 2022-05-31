<?php namespace Dplus\Filters\Misc;
// Dplus Model
use StateCodeQuery, StateCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for StateCodeQuery
 */
class StateCode extends CodeFilter {
	const MODEL = 'StateCode';
}
