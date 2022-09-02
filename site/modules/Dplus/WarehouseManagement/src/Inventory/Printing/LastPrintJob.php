<?php namespace Dplus\Wm;
// Dplus Model
use UserLastPrintJobQuery, UserLastPrintJob;
// ProcessWire
use ProcessWire\WireData;

class LastPrintJob extends WireData {
	const JOBCODE = '';

	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	public function new() {
		$job = new UserLastPrintJob();
		$job->setQty(1);
		return $job;
	}

	/**
	 * Return Query
	 * @return UserLastPrintJobQuery
	 */
	public function query() {
		return UserLastPrintJobQuery::create();
	}

	/**
	 * Return Query filtered By User ID
	 * @param  string $userID User ID
	 * @return UserLastPrintJobQuery
	 */
	public function queryUserid($userID = '') {
		$userID = $userID ? $userID : $this->user->userid;

		$q = $this->query();
		$q->filterByUserid($userID);
		return $q;
	}

	/**
	 * Return Query filtered By User ID and JOB Code
	 * @param  string $userID User ID
	 * @return UserLastPrintJobQuery
	 */
	public function queryUseridFunction($userID = '') {
		$q = $this->queryUserid($userID);

		if (static::JOBCODE) {
			$q->filterByFunctionid(static::JOBCODE);
		}
		return $q;
	}

	/**
	 * Return Last Job Record
	 * @param  string $userID User ID
	 * @return UserLastPrintJob
	 */
	public function lastJob($userID = '') {
		$q = $this->queryUseridFunction($userID);
		return $q->count() ? $q->findOne() : $this->new();
	}
}
