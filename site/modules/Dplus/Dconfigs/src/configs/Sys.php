<?php namespace Dplus\Configs;

use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;

/**
 * ConfigSys
 * Class for getting Sys config
 */
class Sys extends AbstractConfig {
	const MODEL = 'ConfigSys';

	const CUSTOMERS = array(
		'ALUMAC' => 'alumacraft',
		'LINDST' => 'lindstrom',
	);

	/**
	 * Return the CustID from SysConfig
	 * NOTE: Defined in syscm
	 * @return string
	 */
	public static function custid() {
		$class = self::MODEL;
		$q = self::query();
		$q->select($class::aliasproperty('custid'));
		return $q->findOne();
	}
}
