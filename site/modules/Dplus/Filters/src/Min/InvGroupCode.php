<?php namespace Dplus\Filters\Min;
// Dplus Model
use InvGroupCodeQuery, InvGroupCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for InvGroupCodeQuery
 */
class InvGroupCode extends CodeFilter {
	const MODEL = 'InvGroupCode';
}
