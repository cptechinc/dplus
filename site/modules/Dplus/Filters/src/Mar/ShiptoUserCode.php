<?php namespace Dplus\Filters\Mar;
// Dplus Model
use ShiptoUserCodeQuery, ShiptoUserCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for ShiptoUserCodeQuery
 */
class ShiptoUserCode extends CodeFilter {
	const MODEL = 'ShiptoUserCode';
}
