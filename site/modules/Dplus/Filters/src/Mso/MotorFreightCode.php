<?php namespace Dplus\Filters\Mso;
// Dplus Model
use MotorFreightCodeQuery, MotorFreightCode as Model;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
 * Wrapper Class for MotorFreightCodeQuery
 */
class MotorFreightCode extends CodeFilter {
	const MODEL = 'MotorFreightCode';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$model = $this->modelName();
		$columns = [];
		$cols = array_filter($cols);

		if (empty($cols)) {
			$columns = [
				Model::aliasproperty('id'),
				Model::aliasproperty('class'),
				Model::aliasproperty('description'),
				Model::aliasproperty('description2'),
				Model::aliasproperty('description3'),
				Model::aliasproperty('description4'),
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
}
