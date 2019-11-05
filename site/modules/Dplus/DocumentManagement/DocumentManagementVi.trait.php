<?php

use Purl\Url;

trait DocumentManagementVi {
	public function vi_init() {
		$this->addHook('Page(pw_template=vi-documents)::documentload', function($event) {
			$page = $event->object;
			$folder   = $event->arguments(0);
			$document = $event->arguments(1);
			$vendorID = $event->arguments(2);
			$url = $this->wire('pages')->get('pw_template=vi-documents')->url;
			$event->return = $url."?vendorID=$vendorID&folder=$folder&document=$document";
		});

		$this->addHook('Page(pw_template=vi-purchase-orders|vi-purchase-history)::documentsview_po', function($event) {
			$page = $event->object;
			$vendorID   = $event->arguments(0);
			$ponbr      = $event->arguments(1);
			$url = new Url($this->wire('pages')->get('pw_template=vi-documents')->url);
			$url->query->set('vendorID', $vendorID);
			$url->query->set('folder', self::TAG_VENDORPO);
			$url->query->set('ponbr', $ponbr);
			$event->return = $url->getUrl();
		});

		$this->addHook('Page(pw_template=vi-purchase-orders|vi-purchase-history)::documentsview_apinvoice', function($event) {
			$page = $event->object;
			$vendorID   = $event->arguments(0);
			$invnbr     = $event->arguments(1);
			$url = new Url($this->wire('pages')->get('pw_template=vi-documents')->url);
			$url->query->set('vendorID', $vendorID);
			$url->query->set('folder', self::TAG_APINVOICE);
			$url->query->set('invnbr', $invnbr);
			$event->return = $url->getUrl();
		});
	}
}
