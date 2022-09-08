<?php namespace Dplus\Filters\Mgl;
// Dplus Model
use GlTextCodeQuery, GlTextCode as Model;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
* Wrapper Class for GlTextCodeQuery
*/
class GlTextCode extends CodeFilter {
	const MODEL = 'GlTextCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$model = $this->modelName();
		$columns = [];
		$cols = array_filter($cols);

		if (empty($cols)) {
			$columns = [
				Model::aliasproperty('code'),
				Model::aliasproperty('text1'),
				Model::aliasproperty('text2'),
				Model::aliasproperty('text3'),
				Model::aliasproperty('text4'),
			];
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}

		foreach ($cols as $col) {
			if ($model::aliasproperty_exists($col)) {
				$columns[] = $model::aliasproperty($col);
			}
		}
		if (empty($columns)) {
			return false;
		}
		$this->query->searchFilter($columns, strtoupper($q));
		return true;
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */

/* =============================================================
	3. Input Filter Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */

}
