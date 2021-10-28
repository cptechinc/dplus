# Dplus Online config.php

### Installer Configs
These config values are for the ProcessWire DB connection, not the dplus database

```
$config->dbHost = '';
$config->dbName = '';
$config->dbUser = '';
$config->dbPass = '';
```

### Static Application Configs
These configs will stay the same from installation to installation
```
$config->maxUrlSegments = 10;
$config->maxPageNum = 10000;

$config->errorpage_dplusdb = '1020';
$config->rootURL = $rootURL;
$config->urls->vendor = "vendor/";
$config->paths = clone $config->urls;
$config->paths->root = $rootPath . '/';

$config->showonpage = 10;
$config->showonpageoptions = array(5, 10, 20, 50);
```

### Installation Configs, These values may change from customer to customer.
```
$config->company   = 'cptech';
$config->companynbr = 3;
$config->companyfiles = "/var/www/html/data$config->companynbr/";
$config->jsonfilepath = "/var/www/html/files/json$config->companynbr/";
$config->url_webdocs = "/orderfiles/";
$config->directory_webdocs = "/var/www/html/orderfiles/";
$config->directory_httpd = "";
$config->url_images = '/img/product/';
$config->directory_images = '/var/www/html/img/product/';
```
