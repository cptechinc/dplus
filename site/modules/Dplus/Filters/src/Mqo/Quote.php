<?php namespace Dplus\Filters\Mqo;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use QuoteQuery, Quote as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for QuoteQuery
 */
class Quote extends AbstractFilter {
	const MODEL = 'Quote';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('contactid'),
			Model::get_aliasproperty('title'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filters Query by Quote Status
	 * @param  array|string $status Statuses to filter
	 * @return self
	 */
	public function quotestatus($status) {
		$statuses = [];

		if (is_array($status)) {
			foreach ($status as $key) {
				if (array_key_exists($this->wire('sanitizer')->text($key), Quote::STATUS_DESCRIPTIONS)) {
					$statuses[] = $this->wire('sanitizer')->text($key);
				}
			}
		}

		if (is_array($status) === false) {
			if (array_key_exists($this->wire('sanitizer')->text($status), Quote::STATUS_DESCRIPTIONS)) {
				$statuses[] = $this->wire('sanitizer')->text($status);
			}
		}

		if ($statuses) {
			$this->query->filterByStatus($status);
		}
		return $this;
	}

	/**
	 * Filter the Query on the Quote Number column
	 * @param  string $qnbr        Quote Number
	 * @param  string $comparison
	 * @return self
	 */
	public function quotenumber($qnbr, $comparison = null) {
		$this->query->filterByQuotenumber($qnbr, $comparison);
		return $this;
	}

	/**
	 * Filter The Query on the Quote Total Column
	 * @param  float  $total      Quote Total
	 * @param  string $comparison
	 * @return self
	 */
	public function quotetotal($total, $comparison = null) {
		$this->query->filterByTotal_total($total, $comparison);
		return $this;
	}

	/**
	 * Filter The Query on the Date Quoted Column
	 * @param  string $date       Quote Date
	 * @param  string $comparison Criteria Comparasion
	 * @return self
	 */
	public function quotedate($date, $comparison = null) {
		if ($date) {
			$this->query->filterByDate_quoted($date, $comparison);
		}
		return $this;
	}

	/**
	 * Filter The Query on the Review Date Column
	 * @param  string $date       Review Date
	 * @param  string $comparison Criteria Comparasion
	 * @return self
	 */
	public function reviewdate($date, $comparison = null) {
		if ($date) {
			$this->query->filterByDate_review($date, $comparison);
		}
		return $this;
	}

	/**
	 * Filter The Query on the Expire Date Column
	 * @param  string $date       Expire Date
	 * @param  string $comparison Criteria Comparasion
	 * @return self
	 */
	public function expiredate($date, $comparison = null) {
		if ($date) {
			$this->query->filterByDate_expires($date, $comparison);
		}
		return $this;
	}

	/**
	 * Filter the Query on the Customer ID column
	 * @param  string $custID      Customer ID
	 * @param  string $comparison
	 * @return self
	 */
	public function custid($custID, $comparison = null) {
		if ($custID)  {
			$this->query->filterByCustid($custID, $comparison);
		}
		return $this;
	}

	/**
	 * Filter the Query on the Customer Shipto ID column
	 * @param  string $shiptoID      Customer Shipto ID
	 * @param  string $comparison
	 * @return self
	 */
	public function shiptoid($shiptoID, $comparison = null) {
		if ($shiptoID)  {
			$this->query->filterByShiptoid($shiptoID, $comparison);
		}
		return $this;
	}

	/**
	 * Filter the Query on the ArspSaleper(1,2.3)
	 * @param  string $id Sales Rep
	 * @return self
	 */
	public function salespersonid($id) {
		if ($id) {
			$this->query->filterByfilterbySalesPerson($id);
		}
		return $this;
	}

	/**
	 * Filter the Query by Salespersonid if User is a Sales Person
	 * @param  User   $user
	 * @return self
	 */
	public function user(User $user) {
		if ($user->is_salesrep()) {
			$this->salespersonid($user->roleid);
		}
		return $this;
	}

/* =============================================================
	3. Input Filter Functions
============================================================= */
	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {
		$this->quotestatusInput($input);
		$this->quotenumberInput($input);
		$this->quotetotalInput($input);
		$this->quotedateInput($input);
		$this->reviewdateInput($input);
		$this->expiredateInput($input);
		$this->custidInput($input);
		$this->shiptoidInput($input);
	}

	/**
	 * Filters Query by Quote Status
	 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
	 * @return self
	 */
	public function quotestatusInput(WireInput $input) {
		if ($input->get->offsetExists('status') === false) {
			$input->get->status = [];
			return $this;
		}
		return $this->quotestatus($status);
	}

	/**
	 * Filters Query by Quote Number
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function quotenumberInput(WireInput $input) {
		if ($input->get->text('quotenbr_from')) {
			$this->quotenumber($qnbr, $comparison = null)($input->get->text('quotenbr_from'), Criteria::GREATER_EQUAL);
		}

		if ($input->get->text('quotenbr_through')) {
			$this->quotenumber($input->get->text('quotenbr_through'), Criteria::LESS_EQUAL);
		}
		return $this;
	}

	/**
	 * Filters Query by Quote Total
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function quotetotalInput(WireInput $input) {
		if ($input->get->text('quote_total_from')) {
			$this->quotetotal($input->get->text('quote_total_from'), Criteria::GREATER_EQUAL);
		}

		if ($input->get->text('quote_total_through')) {
			$this->quotetotal($input->get->text('quote_total_through'), Criteria::LESS_EQUAL);
		}
		return $this;
	}

	/**
	 * Filters Query by Quote Date
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function quotedateInput(WireInput $input) {
		if ($input->get->text('date_quoted_from') || $input->get->text('date_quoted_through')) {
			$date_quoted_from = date("Ymd", strtotime($input->get->text('date_quoted_from')));

			if (empty($input->get->text('date_quoted_through'))) {
				$date_quoted_through = date('Ymd');
			} else {
				$date_quoted_through = date("Ymd", strtotime($input->get->text('date_quoted_through')));
			}

			if ($date_quoted_from) {
				$this->quotedate($date_quoted_from, Criteria::GREATER_EQUAL);
			}

			if ($date_quoted_through) {
				$this->quotedate($date_quoted_through, Criteria::LESS_EQUAL);
			}
		}
		return $this;
	}

	/**
	 * Filters Query by Review Date
	 * @param  WireInput $input Input Date
	 * @return self
	 */
	public function reviewdateInput(WireInput $input) {
		if ($input->get->text('date_review_from') || $input->get->text('date_review_through')) {
			$date_review_from = date("Ymd", strtotime($input->get->text('date_review_from')));

			if (empty($input->get->text('date_review_through'))) {
				$date_review_through = date('Ymd');
			} else {
				$date_review_through = date("Ymd", strtotime($input->get->text('date_review_through')));
			}

			if ($date_review_from) {
				$this->reviewdate($date_review_from, Criteria::GREATER_EQUAL);
			}

			if ($date_review_through) {
				$this->reviewdate($date_review_through, Criteria::LESS_EQUAL);
			}
		}
		return $this;
	}

	/**
	 * Filters Query by Expire Date
	 *
	 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
	 * @return void
	 */
	public function expiredateInput(WireInput $input) {
		if ($input->get->text('date_expires_from') || $input->get->text('date_expires_through')) {
			$date_expires_from = date("Ymd", strtotime($input->get->text('date_expires_from')));

			if (empty($input->get->text('date_expires_through'))) {
				$date_expires_through = date('Ymd');
			} else {
				$date_expires_through = date("Ymd", strtotime($input->get->text('date_expires_through')));
			}

			if ($date_expires_from) {
				$this->query->expiredate($date_expires_from, Criteria::GREATER_EQUAL);
			}

			if ($date_expires_through) {
				$this->query->expiredate($date_expires_through, Criteria::LESS_EQUAL);
			}
		}
	}

	/**
	 * Filter the Query on the Customer ID column
	 * @param  WireInput $input
	 * @return self
	 */
	public function custidInput($input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->custID) {
			$custIDs = is_array($values->custID) ? $values->array('custID') : array($values->text('custID'));

			if (sizeof($custIDs) == 2) {
				if (!empty($custIDs[0])) {
					$this->custid($custIDs[0], Criteria::GREATER_EQUAL);
				}

				if (!empty($filter[1])) {
					$this->custid($custIDs[1], Criteria::LESS_EQUAL);
				}
			} else {
				$this->custid($custIDs);
			}
		}
		return $this;
	}

	/**
	 * Filter the Query by the Customer Shipto column
	 * @param  WireInput $input
	 */
	public function shiptoidInput($input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->custID && $values->shiptoID) {
			$shiptoID = is_array($values->shiptoID) ? $values->array('shiptoID') : $values->text('shiptoID');
			$this->shiptoid($shiptoID);
		}
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return if Quote Exists
	 * @param  string $qnbr Quote Number
	 * @return bool
	 */
	public function exists($qnbr) {
		return boolval(QuoteQuery::create()->filterByQuotenumber($qnbr)->count());
	}

	/**
	 * Return Position of Item in results
	 * @param  Model|string $quote Sales Order | Sales Order Number
	 * @return int
	 */
	public function positionQuick($quote) {
		$qnbr = $quote;
		if (is_object($quote)) {
			$qnbr = $quote->quotenumber;
		}
		$q = $this->getQueryClass();
		$q->execute_query('SET @rownum = 0');
		$table = $q->getTableMap()::TABLE_NAME;
		$sql = "SELECT x.position FROM (SELECT QthdId, @rownum := @rownum + 1 AS position FROM $table) x WHERE QthdId = :qnbr";
		$params = [':qnbr' => $qnbr];
		$stmt = $q->execute_query($sql, $params);
		return $stmt->fetchColumn();
	}
}
