# PHP Documentation Guide

## Build the documentation

Run the following commands in terminal:

````bash
# Setup
git clone https://github.com/php/phd.git
git clone https://github.com/php/doc-base.git
git clone https://github.com/php/doc-en.git en

# Test your changes
php doc-base/configure.php
php phd/render.php --docbook doc-base/.manual.xml --package PHP --format xhtml

# Open output/php-chunked-xhtml/ in your browser.
````

## See also

- [PHP Manual Contribution Guide](http://doc.php.net/tutorial/)
- [PHP Documentation skeletons](https://github.com/php/doc-base/tree/master/RFC/skeletons)
- [Style Guidelines](http://doc.php.net/tutorial/style.php)