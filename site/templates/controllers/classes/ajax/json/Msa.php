<?php namespace Controllers\Ajax\Json;
// Dplus Model
use DplusUserQuery, DplusUser;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Codes
use Dplus\Codes\Msa as MsaCodes;
// Dplus Msa
use Dplus\Msa as MsaCRUDs;
// Dplus Qnotes
use Dplus\Qnotes;
// Dplus Validators
use Dplus\CodeValidators\Msa as MsaValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Msa extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateUserid($data) {
		$fields = ['userID|text', 'loginID|text', 'jqv|bool', 'new|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$userID = $data->loginID ? $data->loginID : $data->userID;
		$exists = $validate->userid($userID);

		if ($data->jqv === false) {
			if ($data->new) {
				return $exists === false;
			}
			return $exists;
		}

		if ($data->new) {
			return $exists === false ? true : "User $userID Exists";
		}
		return $exists ? true : "User $userID Not Found";
	}

	public static function getUserid($data) {
		$fields = ['userID|text', 'loginID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$userID = $data->loginID ? $data->loginID : $data->userID;

		if ($validate->userid($userID) === false) {
			return false;
		}
		$login = DplusUserQuery::create()->findOneByLoginid($loginID);
		return array(
			'loginid' => $userID,
			'name'    => $login->name,
			'whseid'  => $login->whseid,
		);
	}

	public static function validateLgrp($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$lgrp = MsaCodes\Lgrp::getInstance();
		$exists = $lgrp->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Login Group $data->code already exists";
		}

		if ($exists === false) {
			return "Login Group $data->code not found";
		}
		return true;
	}

	public static function getLgrp($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$lgrp = MsaCodes\Lgrp::getInstance();
		if ($lgrp->exists($data->code) === false) {
			return false;
		}
		$code = $lgrp->code($data->code);
		$response = [
			'code'         => $code->code,
			'description'  => $code->description,
		];
		return $response;
	}

	public static function validateNoceid($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$qnotes = Qnotes\Noce::getInstance();
		$exists = $qnotes->notesExist($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Pre-Defined Note $data->code already exists";
		}

		if ($exists === false) {
			return "Pre-Defined Note $data->code not found";
		}
		return true;
	}

	public static function getNoceNote($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$qnotes = Qnotes\Noce::getInstance();

		if ($qnotes->notesExist($data->code) === false) {
			return false;
		}
		$note = $qnotes->noteLine($data->code);
		$response = [
			'code'  => $note->id,
			'note'  => implode("\r", $qnotes->getNotesArray($data->code)),
		];
		return $response;
	}

	public static function validateSysop($data) {
		$fields = ['system|text', 'sysop|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$sysop = MsaCRUDs\Sysop::getInstance();
		$exists = $sysop->exists($data->system, $data->sysop);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "$data->system Sysop $data->sysop already exists";
		}

		if ($exists === false) {
			return "$data->system Sysop $data->sysop not found";
		}
		return true;
	}

	public static function getSysop($data) {
		$fields = ['system|text', 'sysop|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$sysop = MsaCRUDs\Sysop::getInstance();

		if ($sysop->exists($data->system, $data->sysop) === false) {
			return false;
		}
		return $sysop->codeJson($sysop->code($data->system, $data->sysop));
	}

	public static function validateSysopSystem($data) {
		$fields = ['system|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$sysop = MsaCRUDs\Sysop::getInstance();
		$exists = in_array($data->system, $sysop::SYSTEMS);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "System $data->system already exists";
		}

		if ($exists === false) {
			return "System $data->system not found";
		}
		return true;
	}

	public static function validateSysopOption($data) {
		$fields = ['system|text', 'sysop|text', 'code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$crud   = MsaCRUDs\SysopOptions::getInstance();
		$exists = $crud->exists($data->system, $data->sysop, $data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Sysop $data->sysop Code $data->code already exists";
		}

		if ($exists === false) {
			return "Sysop $data->sysop Code $data->code not found";
		}
		return true;
	}

	public static function getSysopOption($data) {
		$fields = ['system|text', 'sysop|text', 'code|text'];
		self::sanitizeParametersShort($data, $fields);

		$crud   = MsaCRUDs\SysopOptions::getInstance();
		if ($crud->exists($data->system, $data->sysop, $data->code) === false) {
			return false;
		}
		return $crud->codeJson($crud->code($data->system, $data->sysop, $data->code));
	}

	private static function validator() {
		return new MsaValidator();
	}
}
