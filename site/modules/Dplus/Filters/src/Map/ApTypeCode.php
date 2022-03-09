<?php namespace Dplus\Filters\Map;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use ApTypeCodeQuery, ApTypeCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for adding Filters to the ApTypeCodeQuery class
 */
class ApTypeCode extends CodeFilter {
	const MODEL = 'ApTypeCode';
}
