<?php namespace Dplus\Filters\Mgl;
// Dplus Model
use GlDistCodeQuery, GlDistCode as Model;
// Dplus Filters
use Dplus\Filters\CodeFilter;

/**
* Wrapper Class for GlDistCodeQuery
*/
class GlDistCode extends CodeFilter {
	const MODEL = 'GlDistCode';

/* =============================================================
	2. Base Filter Functions
============================================================= */

/* =============================================================
	3. Input Filter Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return if Code Exists
	 * @param  string $code
	 * @return bool
	 */
	public function exists($code) {
		return boolval($this->getQueryClass()->filterById($code)->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $item GlDistCode|Code
	 * @return int
	 */
	public function positionQuick($textcode) {
		$code = $textcode;
		if (is_object($textcode)) {
			$code = $textcode->id;
		}
		$q = $this->getQueryClass();
		$q->execute_query('SET @rownum = 0');
		$table = $q->getTableMap()::TABLE_NAME;
		$col   = Model::aliasproperty('code');
		$sql = "SELECT x.position FROM (SELECT $col, @rownum := @rownum + 1 AS position FROM $table) x WHERE $col = :code";
		$params = [':code' => $code];
		$stmt = $q->executeQuery($sql, $params);
		return $stmt->fetchColumn();
	}
}
