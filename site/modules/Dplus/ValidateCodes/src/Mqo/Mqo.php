<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

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
}
