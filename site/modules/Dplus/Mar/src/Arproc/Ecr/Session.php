<?php namespace Dplus\Mar\Arproc\Ecr;
// Dplus Models
use GlDatesQuery, GlDates;
// ProcessWire
use ProcessWire\ProcessWire;
use ProcessWire\WireData;

/**
 * Session
 *
 * Holds Data for ECR Session
 *
 * @property int    $glPeriod
 * @property string $dateReceived 
 */
class Session extends WireData {
	/**
	 * Return Instance
	 * @return self
	 */
	public static function session() {
		$session = self::loadFromSession();
		if (empty($session)) {
			$session = new self();
			$session->saveSession();
		}
		return $session;
	}

	public function __construct() {
		$this->glPeriod = 0;
		$this->dateReceived = date('m/d/Y');
		$this->updateGlPeriod();
	}

	public function updateGlPeriod() {
		$glDates = GlDatesQuery::create()->findOne();
		$this->glPeriod = $glDates->period($this->dateReceived);
	}

	public function updateDateReceived($date) {
		$this->dateReceived = $date;
		$this->updateGlPeriod();
		$this->saveSession();
	}

	public static function loadFromSession() {
		$pw = ProcessWire::getCurrentInstance();
		return $pw->wire('session')->getFor('ecr', 'session');
	}

	public function saveSession() {
		return $this->wire('session')->setfor('ecr', 'session', $this);
	}
}
