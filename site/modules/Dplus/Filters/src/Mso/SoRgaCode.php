<?php namespace Dplus\Filters\Mso;
// Dplus Model
use SoRgaCodeQuery, SoRgaCode as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for SoRgaCodeQuery
 */
class SoRgaCode extends CodeFilter {
	const MODEL = 'SoRgaCode';
}
