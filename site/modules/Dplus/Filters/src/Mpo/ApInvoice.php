<?php namespace Dplus\Filters\Mpo;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use ApInvoiceQuery, ApInvoice as Model;
use ApInvoiceDetailQuery, ApInvoiceDetail;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for ApInvoiceQuery
*/
class ApInvoice extends AbstractFilter {
	const MODEL = 'ApInvoice';

/* =============================================================
	1. Abstract Contract Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('ponbr'),
			Model::aliasproperty('poref'),
			Model::aliasproperty('vendorid'),
			Model::aliasproperty('invnbr'),
		];
		$this->query->searchFilter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filters Query by VendorID
	 * @param  string|array $vendorID VendorID(s) to filter for
	 * @param  string       $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 * @return self
	 */
	public function vendorid($vendorID, $comparison = null) {
		$this->query->filterByVendorid($vendorID, $comparison);
		return $this;
	}

	/**
	 * Filters Query By Vendor Ship-from ID
	 * @param  string|array $shipfromID Vendor Ship-from ID(s)
	 * @param  string       $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 * @return self
	 */
	public function shipfromid($shipfromID, $comparison = null) {
		$this->query->filterByShipfromid($shipfromID, $comparison);
		return $this;
	}

	/**
	 * Filters Query By PO Number(s)
	 * @param  string|array $ponbr      PO Number(s)
	 * @param  string       $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 * @return bool
	 */
	public function ponbr($ponbr, $comparison = null) {
		$this->query->filterByPonbr($ponbr, $comparison);
		return $this;
	}

	/**
	 * Filters Query By Order Date(s)
	 * @param  string|array $date        PO Ordered Date(s)
	 * @param  string       $comparison  Operator to use for the column comparison, defaults to Criteria::EQUAL
	 * @return bool
	 */
	public function orderdate($date, $comparison = null) {
		$this->query->filterByDate_ordered($date, $comparison);
		return $this;
	}

	/**
	 * Filters Query By Expected Date(s)
	 * @param  string|array $date        PO Expected Date(s)
	 * @param  string       $comparison  Operator to use for the column comparison, defaults to Criteria::EQUAL
	 * @return bool
	 */
	public function expecteddate($date, $comparison = null) {
		$this->query->filterByDate_expected($date, $comparison);
		return $this;
	}

	/**
	 * Filter Query By PO Status Code(s)
	 * @param  string|array $status     Status Code(s)
	 * @param  string       $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
	 * @return self
	 */
	public function status($status, $comparison = null) {
		$this->query->filterByStatus($status, $comparison);
		return $this;
	}

/* =============================================================
	3. Filter Input Functions
============================================================= */
	/**
	 * Applies Filters to the Query
	 * NOTE:: Filters include TODO::
	 *
	 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
	 * @return void
	 */
	public function filterInput(WireInput $input) {
		$this->vendoridInput($input);

		if ($input->get->filter) {
			$this->ponbrInput($input);
		}
	}

	/**
	 * Filter Vendor ID from Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function vendoridInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if (is_array($values->vendorID)) {
			$vendorID = $values->array('vendorID');

			if (sizeof($vendorID) == 2) {
				if (!empty($vendorID[0])) {
					$this->_vendorid($vendorID[0], Criteria::GREATER_EQUAL);
				}
				if (!empty($vendorID[1])) {
					$this->_vendorid($vendorID[1], Criteria::LESS_EQUAL);
				}
				return $this;
			}
		} else {
			$vendorID = $values->text('vendorID');
		}

		if (!empty($vendorID)) {
			$this->vendorid($vendorID);
		}
		return $this;
	}

	/**
	 * Filter By PO Number from Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function ponbrInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->text('ponbr_from')) {
			$this->ponbr($values->text('ponbr_from'), Criteria::GREATER_EQUAL);
		}
		if ($values->text('ponbr_through')) {
			$this->ponbr($values->text('ponbr_through'), Criteria::LESS_EQUAL);
		}
		return $this;
	}

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Adds the Sort By to the query
	 * @param  Page   $page
	 * @return void
	 */
	public function sortby(Page $page) {
		if ($page->has_orderby()) {
			$orderbycolumn = $page->orderby_column;
			$sort = $page->orderby_sort;

			if ($orderbycolumn == 'total_total') {
				$this->query->join('ApInvoiceDetail');
				$tablecolumn = ApInvoiceDetail::aliasproperty('cost_total');
				$this->query->withColumn("SUM(ApInvoiceDetail.$tablecolumn)", 'total_total');
				$this->query->groupBy('ApInvoice.pohdnbr');
				$this->query->orderBy("total_total", $sort);
			} else {
				$tablecolumn = Model::get_aliasproperty($orderbycolumn);
				$this->query->sortBy($tablecolumn, $sort);
			}
		} else {
			$this->query->orderByDate_invoiced('DESC');
		}
	}
}
