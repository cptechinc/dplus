<?php namespace Dplus\Filters\Mso;
// Dplus Model
use SoReasonCodeQuery, SoReasonCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for SoReasonCodeQuery
 */
class SoReasonCode extends CodeFilter {
	const MODEL = 'SoReasonCode';
}
