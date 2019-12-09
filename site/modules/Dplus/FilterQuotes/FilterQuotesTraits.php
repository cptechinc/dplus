<?php namespace ProcessWire;

trait FilterQuotesTraits {
	/**
	 * Filters Query by Quote Number
	 *
	 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
	 * @return void
	 */
	public function filter_quotenumber(WireInput $input) {
		if ($input->get->text('quotenbr_from') && $input->get->text('quotenbr_through')) {
			$this->query->filterByQuotenbr(array($input->get->text('quotenbr_from'), $input->get->text('quotenbr_through')));
		} else if ($input->get->text('quotenbr_from')) {
			$this->query->filterByQuotenbr($input->get->text('quotenbr_from'));
		} else if ($input->get->text('quotenbr_through')) {
			$this->query->filterByQuotenbr($input->get->text('quotenbr_through'));
		}
	}

    /**
	 * Filters Query by Quote Total
	 *
	 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
	 * @return void
	 */
	public function filter_quotetotal(WireInput $input) {
		if ($input->get->text('quote_total_from') && $input->get->text('quote_total_through')) {
			$this->query->filterByTotal_total(array($input->get->text('quote_total_from'), $input->get->text('quote_total_through')));
		} else if ($input->get->text('quote_total_from')) {
			$this->query->filterByTotal_total($input->get->text('quote_total_from'), Criteria::GREATER_EQUAL);
		} else if ($input->get->text('quote_total_through')) {
			$this->query->filterByTotal_total($input->get->text('quote_total_through'), Criteria::LESS_EQUAL);
		}
	}

	/**
	 * Filters Query by Quote Date
	 *
	 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
	 * @return void
	 */
	public function filter_quotedate(WireInput $input) {
		if ($input->get->text('date_quoted_from') || $input->get->text('date_quoted_through')) {
			$date_quoted_from = date("Ymd", strtotime($input->get->text('date_quoted_from')));

			if (empty($input->get->text('date_quoted_through'))) {
				$date_quoted_through = date('Ymd');
			} else {
				$date_quoted_through = date("Ymd", strtotime($input->get->text('date_quoted_through')));
			}
			if ($date_quoted_from && $date_quoted_through) {
				$this->query->filterByDate_quoted(array($date_quoted_from, $date_quoted_through));
			} else if ($date_quoted_from) {
				$this->query->filterByDate_quoted($date_quoted_from);
			} else if ($date_quoted_through) {
				$this->query->filterByDate_quoted($date_quoted_through);
			}
		}
	}

    /**
	 * Filters Query by Review Date
	 *
	 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
	 * @return void
	 */
	public function filter_reviewdate(WireInput $input) {
		if ($input->get->text('date_review_from') || $input->get->text('date_review_through')) {
			$date_review_from = date("Ymd", strtotime($input->get->text('date_review_from')));

			if (empty($input->get->text('date_review_through'))) {
				$date_review_through = date('Ymd');
			} else {
				$date_review_through = date("Ymd", strtotime($input->get->text('date_review_through')));
			}
			if ($date_review_from && $date_review_through) {
				$this->query->filterByDate_review(array($date_review_from, $date_review_through));
			} else if ($date_review_from) {
				$this->query->filterByDate_review($date_review_from);
			} else if ($date_review_through) {
				$this->query->filterByDate_review($date_review_through);
			}
		}
	}

    /**
	 * Filters Query by Expire Date
	 *
	 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
	 * @return void
	 */
	public function filter_expiredate(WireInput $input) {
		if ($input->get->text('date_expires_from') || $input->get->text('date_expires_through')) {
			$date_expires_from = date("Ymd", strtotime($input->get->text('date_expires_from')));

			if (empty($input->get->text('date_expires_through'))) {
				$date_expires_through = date('Ymd');
			} else {
				$date_expires_through = date("Ymd", strtotime($input->get->text('date_expires_through')));
			}
			if ($date_expires_from && $date_expires_through) {
				$this->query->filterByDate_expires(array($date_expires_from, $date_expires_through));
			} else if ($date_expires_from) {
				$this->query->filterByDate_expires($date_expires_from);
			} else if ($date_expires_through) {
				$this->query->filterByDate_expires($date_expires_through);
			}
		}
	}

    /**
	 * Filters Query by Customer PO
	 *
	 * @param  WireInput $input Object that Contains the $_GET array for values to filter on
	 * @return void
	 */
	public function filter_quotestatus(WireInput $input) {
		if ($input->get->text('status')) {
			$status = $input->get->text('status');
			$this->query->filterByStatus("%$status%", Criteria::LIKE);
		}
	}

}
