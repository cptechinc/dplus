<?php
	$directory = $config->directory_webdocs;
	$filename = "$page->pw_template.xlsx";
	$filepath = "$directory$filename";

	$orders_array = $orders->getData();

	$columns = array(
		'ordernumber' => 'Order Number',
		'custid' => 'Customer',
		'custpo'   => 'Customer PO',
		'shiptoid' => 'Shipto ID',
		'total_total'   => 'Order Total',
		'date_ordered'   => 'Order Date',
		'status'         => 'Status'
	);

	$spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
	$worksheet = $spreadsheet->getActiveSheet();
	$highestRow = $worksheet->getHighestRow(); // e.g. 10
	$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'

	for ($i = 0; $i <= sizeof(array_keys($columns)); $i++) {
		$key = array_keys($columns)[$i];
		$worksheet->getCellByColumnAndRow($i+1, 1)->setValue($columns[$key]);
	}

	for ($i = 0; $i <= $orders->getNbResults(); $i++) {
		$order = $orders_array[$i];

		if ($order) {
			$worksheet->getCellByColumnAndRow(1, 2 + $i)->setValue($order->ordernumber);
			$worksheet->getCellByColumnAndRow(2, 2 + $i)->setValue($order->custid);
			$worksheet->getCellByColumnAndRow(3, 2 + $i)->setValue($order->custpo);
			$worksheet->getCellByColumnAndRow(4, 2 + $i)->setValue($order->shiptoid);
			$worksheet->getCellByColumnAndRow(5, 2 + $i)->setValue(format_currency($order->total_total));
			$worksheet->getStyle('E'.(2 + $i))->getNumberFormat()->setFormatCode(PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
			$worksheet->getStyle('E'.(2 + $i))->getAlignment()->setHorizontal(PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
			$worksheet->getCellByColumnAndRow(6, 2 + $i)->setValue(date('m/d/Y', strtotime($order->date_ordered)));
			$worksheet->getCellByColumnAndRow(7, 2 + $i)->setValue($order->status());
		}
	}
	$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);

	$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
	$writer->save($filepath);

	if (file_exists($filepath)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.basename($filepath));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($filepath));
		ob_clean();
		flush();
		readfile($filepath);
		exit;
	}
