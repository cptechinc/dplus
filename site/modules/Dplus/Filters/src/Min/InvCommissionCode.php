<?php namespace Dplus\Filters\Min;
// Dplus Model
use InvCommissionCodeQuery, InvCommissionCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for InvCommissionCodeQuery
 */
class InvCommissionCode extends CodeFilter {
	const MODEL = 'InvCommissionCode';
}
