<?php namespace Dplus\Filters\Mar;
use PDO;
// Propel
use Propel\Runtime\Propel;
// use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use ArCust3partyFreightQuery, ArCust3partyFreight as Model;
// ProcessWire Classes
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the ArCust3partyFreightQuery class
 */
class ArCust3partyFreight extends AbstractFilter {
	const MODEL = 'ArCust3partyFreight';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [];
		if (empty($cols)) {
			$columns = [
				Model::aliasproperty('custid'),
				Model::aliasproperty('accountnbr'),
				Model::aliasproperty('name'),
				Model::aliasproperty('address1'),
				Model::aliasproperty('address2'),
				Model::aliasproperty('city'),
				Model::aliasproperty('state'),
				Model::aliasproperty('zip'),
			];
	
			$this->query->searchFilter($columns, strtoupper($q));
			return true;
		}
		foreach ($cols as $col) {
			if (Model::aliasproperty_exists($col)) {
				$columns[] = Model::aliasproperty($col);
			}
			$this->query->searchFilter($columns, strtoupper($q));
		}
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter Query by Cust ID
	 * @param  string|array $custID      Cust ID(s)
	 * @param  string       $comparison  Operator to use for the column comparison, defaults to Criteria::EQUAL
	 * @return void
	 */
	public function custid($custID, $comparison = null) {
		$this->query->filterByArcucustid($custID, $comparison);
		return $this;
	}

	/**
	 * Return Query Filtered by Customer ID, Account Number
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return ArCust3partyFreightQuery
	 */
	public function custidAccount($custID, $acctnbr) {
		$this->query->filterByCustId($custID)->filterByAccountnbr($acctnbr);
		return $this;
	}

/* =============================================================
	3. Input Query Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return if ArCust3partyFreight Exists
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return bool
	 */
	public function exists($custID, $acctnbr) {
		return boolval($this->getQueryClass()->filterByCustid($custID)->filterByAccountnbr($acctnbr)->count());
	}

	/**
	 * Return ArCust3partyFreight
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return Model
	 */
	public function getArCust3partyFreight($custID, $acctnbr) {
		return $this->getQueryClass()->filterByCustid($custID)->filterByAccountnbr($acctnbr)->findOne();
	}

	/**
	 * Return Position of Record in results
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return int
	 */
	public function positionByCustidAcctnbr($custID, $acctnbr) {
		return $this->positionQuick($custID, $acctnbr);
	}

	/**
	 * Return Position of Customer Account in result set
	 * @param  string $custID  Customer ID
	 * @param  string $acctnbr Account Number
	 * @return int
	 */
	public function positionQuick($custID, $acctnbr) {
		$q = $this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();

		$sql = "SELECT x.position FROM ($table) x WHERE arcucustid = :custid AND ar3pacctnbr = :acctnbr";
		$stmt = $this->getPreparedStatementWrapper($sql);
		$stmt->bindValue(':custid', $custID, PDO::PARAM_STR);
		$stmt->bindValue(':acctnbr', $acctnbr, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Return Sub Query for getting result set with custid and position
	 * @return string
	 */
	private function getPositionSubSql() {
		$table = $this->query->getTableMap()::TABLE_NAME;
		$sql = "SELECT Arcucustid, ar3pacctnbr, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();
		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}
		return $sql;
	}
}
