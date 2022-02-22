<?php namespace Dplus\Filters\Min;
// Dplus Model
use CustomerStockingCellQuery, CustomerStockingCell as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for CustomerStockingCellQuery
 */
class CustomerStockingCell extends AbstractFilter {
	const MODEL = 'CustomerStockingCell';
}
