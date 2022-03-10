<?php namespace Dplus\Filters\Mar;
// Dplus Model
use ArRouteCodeQuery, ArRouteCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for ArRouteCodeQuery
 */
class ArRouteCode extends CodeFilter {
	const MODEL = 'ArRouteCode';
}
