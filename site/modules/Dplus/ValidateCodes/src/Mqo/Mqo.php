<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData, ProcessWire\User;

use QuoteQuery, Quote;

/**
 * Mqo
 * Class for Validating MQO codes and IDs
 */
class Mqo extends WireData {
	/**
	 * Returns if Quote Number exists in the Quote table
	 * @param  string $qnbr Quote Number
	 * @return bool
	 */
	public function quote($qnbr) {
		$q = QuoteQuery::create();
		$q->filterByQuoteid($qnbr);
		return boolval($q->count());
	}

	/**
	 * Return If User has access to Sales Order
	 * @param  string $qnbr Quote #
	 * @param  User   $user Check if User is Sales Rep
	 * @return bool
	 */
	public function quoteUser($qnbr, User $user) {
		if ($user->hasRole('slsrep') === false) {
			return true;
		}
		$q = QuoteQuery::create();
		$q->filterByOrdernumber($this->wire('sanitizer')->qnbr($qnbr));
		$q->filterBySalesPerson($user->repid);
		return boolval($q->count());
	}

	/**
	 * Validate User's Access to Quote
	 * @param  string $qnbr Quote Number
	 * @param  User   $user USer
	 * @return bool
	 */
	public function quoteAccess($qnbr, User $user) {
		if ($this->quote($qnbr)) {
			return $this->quoteUser($qnbr, $user);
		}
		return false;
	}
}
