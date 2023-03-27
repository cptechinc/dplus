<?php namespace Dplus\Filters\Msa;
// PHP Core
use PDO;
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
	public function _search($q, $cols = []) {
		$model = $this->modelName();
		$columns = [];
		if (empty($cols)) {
			$columns = [
				$model::aliasproperty('id'),
				$model::aliasproperty('note'),
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
	/**
	 * Return if Note ID Exists
	 * @param  string $id Note ID
	 * @return bool
	 */
	public function exists($id) {
		return boolval($this->getQueryClass()->filterById($id)->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $note NotePredefined|Note ID
	 * @return int
	 */
	public function positionQuick($note) {
		$id = $note;
		if (is_object($note)) {
			$id = $note->id;
		}
		$q = $this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();
		$col = Model::aliasproperty('id');

		$sql = "SELECT x.position FROM ($table) x WHERE $col = :itemid";
		$stmt = $this->getPreparedStatementWrapper($sql);
		$stmt->bindValue(':itemid', $id, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Return Sub Query for getting result set with custid and position
	 * @return string
	 */
	private function getPositionSubSql() {
		$table = $this->query->getTableMap()::TABLE_NAME;
		$col = Model::aliasproperty('id');
		$sql = "SELECT $col, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();

		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}
		return $sql;
	}
}
