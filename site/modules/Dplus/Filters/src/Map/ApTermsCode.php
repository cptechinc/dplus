<?php namespace Dplus\Filters\Map;
// Propel Classes
	// use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use ApTermsCodeQuery, ApTermsCode as Model;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for adding Filters to the ApTermsCodeQuery class
 */
class ApTermsCode extends CodeFilter {
	const MODEL = 'ApTermsCode';
}
