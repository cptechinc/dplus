<?php namespace Mvc\Whoops\Handlers;
// ProcessWire
use ProcessWire\ProcessWire;
// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
// Whoops
use Whoops\Handler\Handler as BaseHandler;
// MVC Whooops Handlers
use Mvc\Whoops\Handlers\Page;


/**
 * Email Page Handler
 * Class to Grab Pretty Page Handler Output and email it to support
 */
class EmailPage extends Page {
	public function handle() {
		parent::handle();
		$output = ob_get_clean();
		$this->writeTmpFile($output);
		$pw = ProcessWire::getCurrentInstance();

		$mail = new PHPMailer(true);
		$mail->setFrom($pw->wire('config')->emailFrom);
		$mail->addAddress($pw->wire('config')->supportemail, 'Dev');     //Add a recipient
		$mail->addReplyTo($pw->wire('config')->emailFrom);
		$mail->addAttachment($this->getTmpFilename());
		$mail->isHTML(true);                                  //Set email format to HTML
		$mail->Subject = 'Error Report';
		$mail->Body    = $output;
		$sent = $mail->send();
		return BaseHandler::DONE;
	}

	/**
	 * Write Tmp File
	 * @return bool
	 */
	protected function writeTmpFile($html) {
		return file_put_contents($this->getTmpFilename(), $html);
	}

	/**
	 * Return Tmp Filepath
	 * @return string
	 */
	protected function getTmpFilename() {
		$pw = ProcessWire::getCurrentInstance();
		$dir = $pw->wire('files')->tempDir('errors')->get();
		return $dir.session_id().'-error.txt';
	}
}
