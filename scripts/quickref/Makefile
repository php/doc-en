all: functions.js funclist.zip

functions.js: before.js compressed.txt middle.js compkey.txt after.js
	cat before.js compressed.txt middle.js compkey.txt after.js > functions.js

after.js: originalafter.js
	php processafter.php

compressed.txt: prefixcompressed.txt
	php unusedcompress.php

prefixcompressed.txt: funclist.txt
	php prefixcompress.php

funclist.txt: manual_en_contents.txt
	php makefunclist.php

funclist.zip: Makefile after.js before.js compkey.txt compressed.txt funclist.txt functions.js index.html makefunclist.php manual_en_contents.txt middle.js originalafter.js prefixcompress.php prefixcompressed.txt processafter.php readme.txt unusedcompress.php
	zip funclist.zip Makefile after.js before.js compkey.txt compressed.txt funclist.txt functions.js index.html makefunclist.php manual_en_contents.txt middle.js originalafter.js prefixcompress.php prefixcompressed.txt processafter.php readme.txt unusedcompress.php
