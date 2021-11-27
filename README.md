# PHP documentation

![Documentation build](https://github.com/php/doc-en/workflows/Integrate/badge.svg)

### Quick start

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

### See also

- [PHP Manual Contribution Guide](http://doc.php.net/tutorial/)
- [Joining the team](http://doc.php.net/tutorial/joining.php)
- [Manual sources structure](http://doc.php.net/tutorial/structure.php)
- [Editing manual sources](http://doc.php.net/tutorial/editing.php)
- [Style guidelines](http://doc.php.net/tutorial/style.php)

- [Frequently Asked Questions](http://doc.php.net/tutorial/faq.php)
- [The PHP Manual builds](http://doc.php.net/tutorial/builds.php)
- [Why we care about whitespace](http://doc.php.net/tutorial/whitespace.php)
- [User Note Editing Guidelines](http://doc.php.net/tutorial/user-notes.php)
- [Setting up a documentation environment](http://doc.php.net/tutorial/local-setup.php)

- [PHP Documentation skeletons](https://github.com/php/doc-base/tree/master/RFC/skeletons)