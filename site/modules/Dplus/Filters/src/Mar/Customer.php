<?php namespace Dplus\Filters\Mar;
use PDO;
// Propel
use Propel\Runtime\Propel;
// Dplus Models
use CustomerQuery, Customer as Model;
// Dpluso Models
use CustpermQuery, Custperm;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the CustomerQuery class
 */
class Customer extends AbstractFilter {
	const MODEL = 'Customer';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('custid'),
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
	public function active() {
		$config = ConfigCiQuery::create()->findOne();

		if ($config->show_inactive != ConfigCi::BOOL_TRUE) {
			$this->query->filterByActive(Model::STATUS_ACTIVE);
		}
		return $this;
	}

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
			$q->withColumn('DISTINCT(custid)', 'custid');
			$q->select('custid');
			$this->query->filterByCustid($q->find()->toArray(), Criteria::IN);
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
	 * Return if Customer Exists
	 * @param  string $custID Customer ID
	 * @return bool
	 */
	public function exists($custID) {
		return boolval($this->getQueryClass()->filterByCustid($custID)->count());
	}

	/**
	 * Return Customer
	 * @param  string $custID Customer ID
	 * @return Model
	 */
	public function getCustomer($custID) {
		return $this->getQueryClass()->findOneByCustid($custID);
	}

	/**
	 * Return Position of Record in results
	 * @param  string $custID Customer ID
	 * @return int
	 */
	public function positionById($custID) {
		return $this->positionQuick($custID);
	}

	/**
	 * Return Position of Cust ID in result set
	 * @param  string $custID  Customer ID
	 * @return int
	 */
	public function positionQuick($custID) {
		$q = $this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();

		$sql = "SELECT x.position FROM ($table) x WHERE arcucustid = :custid";
		$stmt = $this->getPreparedStatementWrapper($sql);
		$stmt->bindValue(':custid', $custID, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Return Sub Query for getting result set with custid and position
	 * @return string
	 */
	private function getPositionSubSql() {
		$table = $this->query->getTableMap()::TABLE_NAME;
		$sql = "SELECT Arcucustid, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();
		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}
		return $sql;
	}
}
