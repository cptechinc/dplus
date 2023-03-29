<?php namespace Controllers\Ajax\Json;
// Dplus Model
use DplusUserQuery;
// ProcessWire Classes, Modules
use ProcessWire\WireData;;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Msa as MsaCodes;
use Dplus\Msa as DMSA;
use Dplus\Qnotes;

// Mvc Controllers
use Mvc\Controllers\Controller;

class Msa extends Controller {
	public static function test() {
		return 'test';
	}

	public static function validateUserid($data) {
		$fields = ['userID|string', 'loginID|string', 'jqv|bool', 'new|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$LOGM = DMSA\Logm::getInstance();
		$userID = $data->loginID ? $data->loginID : $data->userID;
		$exists = $LOGM->exists($userID);

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

	public static function getUser($data) {
		$fields = ['userID|string', 'loginID|string'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$userID = $data->loginID ? $data->loginID : $data->userID;

		if ($validate->userid($userID) === false) {
			return false;
		}
		$login = DplusUserQuery::create()->findOneByUserid($userID);
		return array(
			'loginid' => $userID,
			'name'    => $login->name,
			'whseid'  => $login->whseid,
		);
	}

	public static function validateLgrp($data) {
		$fields = ['code|string', 'jqv|bool', 'new|bool'];
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
		self::sanitizeParametersShort($data, ['code|string']);

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
		$fields = ['code|string', 'jqv|bool', 'new|bool'];
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
		self::sanitizeParametersShort($data, ['code|string']);

		$qnotes = Qnotes\Noce::getInstance();

		if ($qnotes->notesExist($data->code) === false) {
			return false;
		}
		$note = $qnotes->noteLine($data->code);
		return $qnotes->json($note);
	}

	public static function validateSysop($data) {
		$fields = ['system|text', 'sysop|string', 'code|string', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$sysop = Codes\Msa\Sysop::getInstance();
		if ($data->sysop) {
			$data->code = $data->sysop;
		}
		$exists = $sysop->exists($data->system, $data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "$data->system Sysop $data->code already exists";
		}

		if ($exists === false) {
			return "$data->system Sysop $data->code not found";
		}
		return true;
	}

	public static function getSysop($data) {
		$fields = ['system|text', 'sysop|string', 'code|string', 'jqv|bool', 'new|bool'];
		if ($data->sysop) {
			$data->code = $data->sysop;
		}
		self::sanitizeParametersShort($data, $fields);

		$sysop = Codes\Msa\Sysop::getInstance();

		if ($sysop->exists($data->system, $data->code) === false) {
			return false;
		}
		return $sysop->codeJson($sysop->code($data->system, $data->code));
	}

	public static function validateSysopNotecode($data) {
		$fields = ['notecode|string', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$sysop = Codes\Msa\Sysop::getInstance();
		$exists = $sysop->notecodeExists($data->notecode);

		if (empty($data->jqv) === false) {
			if (boolval($data->new) === true) {
				return $exists ? "Note Code $data->notecode exists" : true;
			}
			return $exists ? true : "Note Code $data->notecode not found";
		}

		if (boolval($data->new) === true) {
			return $exists === false;
		}
		return $exists;
	}

	public static function validateSysopSystem($data) {
		$fields = ['system|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$sysop = Codes\Msa\Sysop::getInstance();
		$exists = $sysop->systemExists($data->system);

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
		$fields = ['system|text', 'sysop|string', 'code|string', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		switch ($data->system) {
			case 'AP':
				$crud = Codes\Map\Aoptm::getInstance();
				break;
			case 'AR':
				$crud = Codes\Mar\Roptm::getInstance();
				break;
			case 'IN':
				$crud = Codes\Min\Ioptm::getInstance();
				break;
			case 'PO':
				break;
			case 'SO':
				$crud = Codes\Mso\Soptm::getInstance();
				break;
		}
		$exists = $crud->exists($data->sysop, $data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		$reflect = new \ReflectionClass($crud);

		if (boolval($data->new) === true) {
			return $exists === false ? true : strtoupper($reflect->getShortName()) . " $data->sysop Code $data->code already exists";
		}

		if ($exists === false) {
			return strtoupper($reflect->getShortName()) . " $data->sysop Code $data->code not found";
		}
		return true;
	}

	public static function getSysopOption($data) {
		$fields = ['system|text', 'sysop|string', 'code|string'];
		self::sanitizeParametersShort($data, $fields);

		switch ($data->system) {
			case 'AP':
				$crud = Codes\Map\Aoptm::getInstance();
				break;
			case 'AR':
				$crud = Codes\Mar\Roptm::getInstance();
				break;
			case 'IN':
				$crud = Codes\Min\Ioptm::getInstance();
				break;
			case 'PO':
				break;
			case 'SO':
				$crud = Codes\Mso\Soptm::getInstance();
				break;
		}
		if ($crud->exists($data->sysop, $data->code) === false) {
			return false;
		}
		return $crud->codeJson($crud->code($data->sysop, $data->code));
	}

	public static function getSysopRequiredCodes($data) {
		$fields = ['system|text'];
		self::sanitizeParametersShort($data, $fields);

		$sysop = Codes\Msa\Sysop::getInstance();
		return $sysop->getRequiredCodes($data->system);
	}

	public static function validatePrinter($data) {
		$fields = ['id|string', 'strict|bool', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$PRTD = DMSA\Prtd::getInstance();

		if ($data->jqv) { // JQueryValidate
			if ($data->strict) {
				return $PRTD->exists($data->id) ? true : "Printer $data->id not found";
			}
			return $PRTD->existsPrinterPitch($data->id) ? true : "Printer & Pitch $data->id not found";
		}

		if ($data->strict) {
			return $PRTD->exists($data->id);
		}
		return $PRTD->existsPrinterPitch($data->id);
	}

	public static function getPrinter(WireData $data) {
		$fields = ['id|string', 'strict|bool', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$PRTD = DMSA\Prtd::getInstance();
		$id = $PRTD->idByPrinterPitch($data->id);

		if ($id === false) {
			return false;
		}
		return $PRTD->printerJson($PRTD->printer($id));
	}

	public static function validateRoleid($data) {
		$fields = ['id|string', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$LROLE  = DMSA\Lrole::getInstance();
		$exists = $LROLE->exists($data->id);

		if ($data->jqv) { // JQueryValidate
			return $exists ? true : "Role ID $data->id not found";
		}
		return $exists;
	}

	public static function getRole($data) {
		self::sanitizeParametersShort($data, ['id|string']);
		$LROLE  = DMSA\Lrole::getInstance();
		$role = $LROLE->role($data->id);
		if (empty($role)) {
			return false;
		}
		return $LROLE->roleJson($role);
	}
}
