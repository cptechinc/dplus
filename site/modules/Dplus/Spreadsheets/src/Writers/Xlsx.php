<?php namespace Dplus\Spreadsheets\Writers;
// PhpSpreadsheet Library
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// Dplus Spreadsheets
use Dplus\Spreadsheets\Writer;

class Xlsx extends Writer {
	const EXTENSION = 'xlsx';

	/**
	 * Return Spreadsheet File Writer
	 * @return BaseWriter
	 */
	protected function getWriter(Spreadsheet $spreadsheet) {
		$writer = new WriterXlsx($spreadsheet);
		return $writer;
	}
}
