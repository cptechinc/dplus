<?php namespace Dplus\Filters\Mso;
// Dplus Model
use SalesHistoryQuery, SalesHistory as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
// Dplus Filters
use Dplus\Filters\AbstractFilter;
use Dplus\Filters\Mso\SalesOrder as SalesOrderFilter;

/**
* Wrapper Class for SalesHistoryQuery
*/
class SalesHistory extends SalesOrderFilter {
	const MODEL = 'SalesHistory';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {
		parent::_filterInput($input);

		if ($input->get->filter) {
			$this->invoicedateInput($input);
		}
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter the Query on the Invoice Date column
	 * @param  string $date       Invoice Date
	 * @param  string $comparison Criteria Comparasion
	 * @return void
	 */
	public function invoicedate($date, $comparison = null) {
		$this->query->filterByInvoicedate($date, $comparison);
	}

/* =============================================================
	3. Input Filter Functions
============================================================= */
	/**
	 * Filter the Query on the Invoice Date column
	 * @param  WireInput $input
	 * @return self
	 */
	public function invoicedateInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->text('invoicedate_from') || $values->text('invoicedate_through')) {
			$invoicedate_from = date("Ymd", strtotime($values->text('invoicedate_from')));

			if (empty($values->text('invoicedate_through'))) {
				$invoicedate_through = date('Ymd');
			} else {
				$invoicedate_through = date("Ymd", strtotime($values->text('invoicedate_through')));
			}

			if ($invoicedate_from) {
				$this->invoicedate($invoicedate_from, Criteria::GREATER_EQUAL);
			}

			if ($invoicedate_through) {
				$this->invoicedate($invoicedate_through, Criteria::LESS_EQUAL);
			}
		}
		return $this;
	}
}
