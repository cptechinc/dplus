<?php namespace ProcessWire;

use Purl\Url;

trait DocumentManagementVi {
	public function vi_init() {
		$this->addHook('Page(pw_template=vi-documents)::documentload', function($event) {
			$page = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$vendorID = $event->arguments(2);
			$event->return = $this->get_vi_documentURL($vendorID, $folder, $document);
		});

		$this->addHook('Page(pw_template=vi-purchase-orders|vi-purchase-history)::documentsview_po', function($event) {
			$page = $event->object;
			$vendorID   = $event->arguments(0);
			$ponbr      = $event->arguments(1);
			$url = new Url($this->get_vi_docs_folderURL($vendorID, self::TAG_VENDORPO));
			$url->query->set('ponbr', $ponbr);
			$event->return = $url->getUrl();
		});

		$this->addHook('Page(pw_template=vi-purchase-orders|vi-purchase-history)::documentsview_apinvoice', function($event) {
			$page = $event->object;
			$vendorID   = $event->arguments(0);
			$invnbr     = $event->arguments(1);
			$url = new Url($this->get_vi_docs_folderURL($vendorID, self::TAG_APINVOICE));
			$url->query->set('invnbr', $invnbr);
			$event->return = $url->getUrl();
		});
	}

	/**
	 * Returns URL to the VI documents Page
	 * @param  string $vendorID Vendor ID
	 * @param  string $folder   Document Management Folder Code
	 * @return string
	 */
	public function get_vi_docs_folderURL($vendorID, $folder) {
		$url = new Url($this->wire('pages')->get('pw_template=vi-documents')->url);
		$url->query->set('vendorID', $vendorID);
		$url->query->set('folder', $folder);
		return $url->getUrl();
	}

	/**
	 * Returns URL to the VI documents Page
	 * @param  string $vendorID Vendor ID
	 * @param  string $folder   Document Management Folder Code
	 * @param  string $document Document Name
	 * @return string
	 */
	public function get_vi_documentURL($vendorID, $folder, $document) {
		$url = new Url($this->wire('pages')->get('pw_template=ci-documents')->url);
		$url->query->set('vendorID', $vendorID);
		$url->query->set('folder', $folder);
		$url->query->set('document', $document);
		return $url->getUrl();
	}
}
