<?php namespace Dplus\Filters\Msa;
// Dplus Model
use NotePreDefinedQuery, NotePreDefined as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for NotePreDefinedQuery
*/
class NotePreDefined extends AbstractFilter {
	const MODEL = 'NotePredefined';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$model = $this->modelName();
		$columns = [
			$model::aliasproperty('id'),
			$model::aliasproperty('note'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter So it's summarized
	 * @return self
	 */
	public function filterSummarized() {
		$this->query->filterBySequence(1);
		return $this;
	}
/* =============================================================
	3. Input Filter Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */

}
