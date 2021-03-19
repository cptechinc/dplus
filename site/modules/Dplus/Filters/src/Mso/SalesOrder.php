<?php namespace Dplus\Filters\Mso;
// Dplus Model
use SalesOrderQuery, SalesOrder as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page, ProcessWire\User;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
* Wrapper Class for SalesOrderQuery
*/
class SalesOrder extends AbstractFilter {
	const MODEL = 'SalesOrder';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('ordernumber'),
			Model::aliasproperty('custpo'),
			Model::aliasproperty('custid'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

	/**
	 * Filter Query with Input Data
	 * @param  WireInput $input Input Data
	 * @return self
	 */
	public function _filterInput(WireInput $input) {
		$this->custidInput($input);
		$this->shiptoidInput($input);

		if ($input->get->filter) {
			$this->ordernumberInput($input);
			$this->custpoInput($input);
			$this->orderdateInput($input);
			$this->ordertotalInput($input);
			$this->requestdateInput($input);
		}

		if ($input->get->offsetExists('status') === false) {
			$input->get->status = [];
		}
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter the Query on the Order Number column
	 * @param  string $ordn       Order Number
	 * @param  string $comparison
	 * @return self
	 */
	public function ordernumber($ordn, $comparison = null) {
		$ordn = SalesOrder::get_paddedordernumber($ordn);
		$this->query->filterByOrdernumber($ordn, $comparison);
		return $this;
	}

	/**
	 * Filter the Query on the Customer PO column
	 * @param  string $custpo
	 * @return self
	 */
	public function custpo($custpo = '') {
		if ($custpo) {
			$this->query->filterByCustpo("%$custpo%", Criteria::LIKE);
		}
		return $this;
	}

	/**
	 * Filter the Query on the Order Date column
	 * @param  string $date       Order Date
	 * @param  string $comparison Criteria Comparasion
	 * @return void
	 */
	public function orderdate($date, $comparison = null) {
		$this->query->filterByOrderdate($date, $comparison);
	}

	/**
	 * Filter the Query on the Request Date column
	 * @param  string $date       Request Date
	 * @param  string $comparison Criteria Comparasion
	 * @return void
	 */
	public function requestdate($date, $comparison = null) {
		$this->query->filterByRequestdate($date, $comparison);
	}

	/**
	 * Filter The Query on the Order Total Column
	 * @param  float  $total       Order Total
	 * @param  string $comparison
	 * @return self
	 */
	public function ordertotal($total, $comparison = null) {
		$this->query->filterByOrdertotal($total, $comparison);
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
	 * filter the Query By salespersonid if the User is a salesperson
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
	 * Filter the Query on the Customer PO column
	 * @param  WireInput $input
	 * @return self
	 */
	public function custpoInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$custpo = $values->text('custpo');
		$this->custpo($custpo);
		return $this;
	}

	/**
	 * Filter the Query by the Order Number column
	 * @param  WireInput $input
	 * @return self
	 */
	public function ordernumberInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->text('ordernumber_from')) {
			$this->ordernumber($values->text('ordernumber_from'), Criteria::GREATER_EQUAL);
		}

		if ($values->text('ordernumber_through')) {
			$this->ordernumber($values->text('ordernumber_through'), Criteria::LESS_EQUAL);
		}
		return $this;
	}

	/**
	 * Filter the Query on the Order Date column
	 * @param  WireInput $input
	 * @return self
	 */
	public function orderdateInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->text('orderdate_from') || $values->text('orderdate_through')) {
			$orderdate_from = date("Ymd", strtotime($values->text('orderdate_from')));

			if (empty($values->text('orderdate_through'))) {
				$orderdate_through = date('Ymd');
			} else {
				$orderdate_through = date("Ymd", strtotime($values->text('orderdate_through')));
			}

			if ($orderdate_from) {
				$this->orderdate($orderdate_from, Criteria::GREATER_EQUAL);
			}

			if ($orderdate_through) {
				$this->orderdate($orderdate_through, Criteria::LESS_EQUAL);
			}
		}
		return $this;
	}

	/**
	 * Filter the Query on the Request Date column
	 * @param  WireInput $input
	 * @return self
	 */
	public function requestdateInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->text('requestdate_from') || $values->text('requestdate_through')) {
			$requestdate_from = date("Ymd", strtotime($values->text('requestdate_from')));

			if (empty($values->text('requestdate_through'))) {
				$requestdate_through = date('Ymd');
			} else {
				$requestdate_through = date("Ymd", strtotime($values->text('requestdate_through')));
			}

			if ($requestdate_from) {
				$this->requestdate($requestdate_from, Criteria::GREATER_EQUAL);
			}

			if ($requestdate_through) {
				$this->requestdate($requestdate_through, Criteria::LESS_EQUAL);
			}
		}
		return $this;
	}

	/**
	 * Filter the Query on the Order total column
	 * @param  WireInput $input
	 * @return self
	 */
	public function ordertotalInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->text('order_total_from')) {
			$this->ordertotal($values->text('order_total_from'), Criteria::GREATER_EQUAL);
		}

		if ($values->text('order_total_through')) {
			$this->ordertotal($values->text('order_total_through'), Criteria::LESS_EQUAL);
		}
		return $this;
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
			$custIDs = $values->array('custID');

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
			$shiptoID = $values->array('shiptoID');
			$this->shiptoid($shiptoID);
		}
	}
}
