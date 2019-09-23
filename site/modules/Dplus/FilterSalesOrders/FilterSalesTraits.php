<?php namespace ProcessWire;

	trait FilterSalesTraits {
		/**
		 * Filters Query by Order Number
		 *
		 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
		 * @return void
		 */
		public function filter_ordernumber(WireInput $input) {
			if ($input->get->text('ordernumber_from') && $input->get->text('ordernumber_through')) {
				$this->query->filterByOrdernumber(array($input->get->text('ordernumber_from'), $input->get->text('ordernumber_through')));
			} else if ($input->get->text('ordernumber_from')) {
				$this->query->filterByOrdernumber($input->get->text('ordernumber_from'));
			} else if ($input->get->text('ordernumber_through')) {
				$this->query->filterByOrdernumber($input->get->text('ordernumber_through'));
			}
		}

		/**
		 * Filters Query by Customer PO
		 *
		 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
		 * @return void
		 */
		public function filter_custpo(WireInput $input) {
			if ($input->get->text('custpo')) {
				$custpo = $input->get->text('custpo');
				$this->query->filterByCustpo("%$custpo%", Criteria::LIKE);
			}
		}

		/**
		 * Filters Query by Order Date
		 *
		 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
		 * @return void
		 */
		public function filter_orderdate(WireInput $input) {
			if ($input->get->text('orderdate_from') || $input->get->text('orderdate_through')) {
				$orderdate_from = date("Ymd", strtotime($input->get->text('orderdate_from')));

				if (empty($input->get->text('orderdate_through'))) {
					$orderdate_through = date('Ymd');
				} else {
					$orderdate_through = date("Ymd", strtotime($input->get->text('orderdate_through')));
				}
				if ($orderdate_from && $orderdate_through) {
					$this->query->filterByOrderdate(array($orderdate_from, $orderdate_through));
				} else if ($orderdate_from) {
					$this->query->filterByOrderdate($orderdate_from);
				} else if ($orderdate_through) {
					$this->query->filterByOrderdate($orderdate_through);
				}
			}
		}

		/**
		 * Filters Query by Order Date
		 *
		 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
		 * @return void
		 */
		public function filter_ordertotal(WireInput $input) {
			if ($input->get->text('order_total_from') && $input->get->text('order_total_through')) {
				$this->query->filterByOrdertotal(array($input->get->text('order_total_from'), $input->get->text('order_total_through')));
			} else if ($input->get->text('order_total_from')) {
				$this->query->filterByTotal_total($input->get->text('order_total_from'), Criteria::GREATER_EQUAL);
			} else if ($input->get->text('order_total_through')) {
				$this->query->filterByTotal_total($input->get->text('order_total_through'), Criteria::LESS_EQUAL);
			}
		}

		/**
		 * Filters Query by Customer ID
		 *
		 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
		 * @return void
		 */
		public function filter_custid($input) {
			if ($input->get->custID) {
				if (is_array($input->get->custID)) {
					$filter = $input->get->array('custID');
				} else {
					$filter = $input->get->text('custID');
				}
				$this->query->filterByCustid($filter);
			}
		}

		/**
		 * Filters Query by Customer ShiptoID
		 *
		 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
		 * @return void
		 */
		public function filter_shiptoid($input) {
			if ($input->get->shiptoID && $input->get->custID) {
				if (is_array($input->get->shiptoID)) {
					$filter = $input->get->array('shiptoID');
				} else {
					$filter = $input->get->text('shiptoID');
				}
				$this->query->filterByShiptoid($filter);
			}
		}
	}
