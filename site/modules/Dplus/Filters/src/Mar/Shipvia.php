<?php namespace Dplus\Filters\Mar;
// Dplus Model
use ShipviaQuery, Shipvia as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for ShipviaQuery
 */
class Shipvia extends CodeFilter {
	const MODEL = 'Shipvia';
}
