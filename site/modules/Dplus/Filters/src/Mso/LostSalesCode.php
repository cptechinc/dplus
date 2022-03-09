<?php namespace Dplus\Filters\Mso;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use LostSalesCodeQuery, LostSalesCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for adding Filters to the LostSalesCodeQuery class
 */
class LostSalesCode extends CodeFilter {
	const MODEL = 'LostSalesCode';
}
