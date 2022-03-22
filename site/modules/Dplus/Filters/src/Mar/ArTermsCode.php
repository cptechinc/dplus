<?php namespace Dplus\Filters\Mar;
// Dplus Model
use ArTermsCodeQuery, ArTermsCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for ArTermsCodeQuery
 */
class ArTermsCode extends CodeFilter {
	const MODEL = 'ArTermsCode';
}
