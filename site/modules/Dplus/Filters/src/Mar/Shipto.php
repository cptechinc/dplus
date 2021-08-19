<?php namespace Dplus\Filters\Mar;
use PDO;
// Propel
use Propel\Runtime\Propel;
// Dplus Models
use CustomerShiptoQuery, CustomerShipto as Model;
// Dpluso Models
use CustpermQuery, Custperm;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the CustomerShiptoQuery class
 */
class Shipto extends AbstractFilter {
	const MODEL = 'CustomerShipto';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('custid'),
			Model::aliasproperty('shiptoid'),
			Model::aliasproperty('name'),
			Model::aliasproperty('address1'),
			Model::aliasproperty('address2'),
			Model::aliasproperty('city'),
			Model::aliasproperty('state'),
			Model::aliasproperty('zip'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter Query by Cust ID
	 * @param  string|array $custID Cust ID
	 * @return void
	 */
	public function custid($custID) {
		$this->query->filterByCustid($custID);
		return $this;
	}

	/**
	 * Filter User's Customer if Sales Rep
	 * @param  User   $user
	 * @return self
	 */
	public function user(User $user) {
		if ($user->is_salesrep()) {
			$q = CustpermQuery::create();
			$q->withColumn('DISTINCT(shiptoid)', 'shiptoid');
			$q->select('shiptoid');
			$this->query->filterByArstshipid($q->find()->toArray());
		}
		return $this;
	}

/* =============================================================
	3. Input Query Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return if Customer Ship-to Exists
	 * @param  string $custID   Customer ID
	 * @param  string $shiptoID Customer Ship-to ID
	 * @return bool
	 */
	public function exists($custID, $shiptoID) {
		return boolval($this->getQueryClass()->filterByCustidShiptoid($custID, $shiptoID)->count());
	}

	/**
	 * Return Shipto
	 * @param  string $custID   Customer ID
	 * @param  string $shiptoID Customer Ship-to ID
	 * @return Model
	 */
	public function getShipto($custID, $shiptoID) {
		return $this->getQueryClass()->findOneByCustidShiptoid($custID, $shiptoID);
	}

	/**
	 * Return Position of Record in results
	 * @param  string $custID   Customer ID
	 * @param  string $shiptoID Customer Ship-to ID
	 * @return int
	 */
	public function positionById($custID, $shiptoID) {
		return $this->positionQuick($custID, $shiptoID);
	}

	/**
	 * Return Position of Cust ID in result set
	 * @param  string $custID  Customer ID
	 * @return int
	 */
	public function positionQuick($custID, $shiptoID) {
		$q = $this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();

		$sql = "SELECT x.position FROM ($table) x WHERE arcucustid = :custid AND arstshiptoid = :shiptoid";
		$stmt = $this->getPreparedStatementWrapper($sql);
		$stmt->bindValue(':custid', $custID, PDO::PARAM_STR);
		$stmt->bindValue(':shiptoid', $shiptoID, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Return Sub Query for getting result set with custid and position
	 * @return string
	 */
	private function getPositionSubSql() {
		$table = $this->query->getTableMap()::TABLE_NAME;
		$sql = "SELECT Arcucustid, Arstshiptoid, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();
		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}
		return $sql;
	}
}
