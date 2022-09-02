<?php namespace Dplus\Filters\Mar;
use PDO;
// Propel
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use ArInvoiceQuery, ArInvoice as Model;
// Dpluso Models
use CustpermQuery, Custperm;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the ArInvoiceQuery class
 */
class ArInvoice extends AbstractFilter {
	const MODEL = 'ArInvoice';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [
			Model::aliasproperty('custid'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
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

/* =============================================================
	3. Input Query Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
}
