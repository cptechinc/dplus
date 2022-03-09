<?php namespace Dplus\Filters\Min;
// Dplus Model
use InvStockCodeQuery, InvStockCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for InvStockCodeQuery
 */
class InvStockCode extends CodeFilter {
	const MODEL = 'InvStockCode';
}
