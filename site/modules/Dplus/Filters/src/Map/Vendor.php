<?php namespace Dplus\Filters\Map;
// Dplus Model
use VendorQuery, Vendor as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the VendorQuery class
 */
class Vendor extends AbstractFilter {
	const MODEL = 'Vendor';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [
			Model::aliasproperty('vendorid'),
			Model::aliasproperty('name'),
			Model::aliasproperty('address'),
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
	 * Filter Query by VendorID
	 * @param  array|string $vendorID  Vendor ID(s)
	 * @return void
	 */
	public function vendorid($vendorID) {
		$this->query->filterByVendorid($vendorID);
	}

/* =============================================================
	3. Input Filter Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return Vendor
	 * @param  string $vendorID Vendor ID
	 * @return Vendor
	 */
	public function getVendor($vendorID) {
		return VendorQuery::create()->findOneByVendorid($vendorID);
	}

	/**
	 * Return if Vendor exists
	 * @param  string $vendorID Vendor ID
	 * @return bool
	 */
	public function exists($vendorID) {
		return boolval(VendorQuery::create()->filterByVendorid($vendorID)->count());
	}

	/**
	 * Return Position of Record in results
	 * @param  string Vendor ID
	 * @return int
	 */
	public function positionById($vendorID) {
		return $this->positionQuick($vendorID);
	}

	/**
	 * Return Position of Cust ID in result set
	 * @param  string $vendorID  Vendor ID
	 * @return int
	 */
	public function positionQuick($vendorID) {
		$q = $this->getQueryClass()->executeQuery('SET @rownum = 0');
		$table = $this->getPositionSubSql();

		$sql = "SELECT x.position FROM ($table) x WHERE apvevendid = :vendid";
		$stmt = $this->getPreparedStatementWrapper($sql);
		$stmt->bindValue(':vendid', $vendorID, \PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Return Sub Query for getting result set with vendid and position
	 * @return string
	 */
	private function getPositionSubSql() {
		$table = $this->query->getTableMap()::TABLE_NAME;
		$sql = "SELECT apvevendid, @rownum := @rownum + 1 AS position FROM $table";
		$whereClause = $this->getWhereClauseString();
		if (empty($whereClause) === false) {
			$sql .= ' WHERE ' . $whereClause;
		}
		return $sql;
	}
}
