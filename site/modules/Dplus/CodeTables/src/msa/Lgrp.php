<?php namespace Dplus\Codes\Msa;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use SysLoginGroupQuery, SysLoginGroup;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the LGRP code table
 */
class Lgrp extends Base {
	const MODEL              = 'SysLoginGroup';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'sys_login_group';
	const DESCRIPTION        = 'Login Group';
	const DESCRIPTION_RECORD = 'Login Group';
	const RESPONSE_TEMPLATE  = 'Login Group {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'lgrp';
	const DPLUS_TABLE           = 'LGRP';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => SysLoginGroup::MAX_LENGTH_CODE],
		'description' => ['type' => 'text', 'maxlength' => 40],
	];

	/** @var self */
	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Source Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(SysLoginGroup::aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->getQueryClass();
		return $q->find();
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return SysLoginGroup
	 */
	public function new($id = '') {
		$code = new SysLoginGroup();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
