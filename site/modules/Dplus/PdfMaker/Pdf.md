# PdfMaker
PDF Maker is a wrapper around mikehaertl/phpwkhtmltopdf for creating
PDF versions of a webpage

### Setup Hosts file to handle the subdomains
127.0.0.1       test.com
17.0.0.1        www.test.com

### Install wkhtmltopdf and its dependencies
yum install xorg-x11-fonts-75dpi.noarch
yum install xorg-x11-fonts-Type1.noarch
yum install xorg-x11-server-Xvfb
rpm -ivh https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.2/wkhtmltox-0.12.2_linux-centos6-amd64.rpm

### TEST
wkhtmltopdf 'http://google.com' stuff.pdf
