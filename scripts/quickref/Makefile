all: functions.js funclist.zip

functions.js: before.js compressed.txt middle.js compkey.txt after.js
	cat before.js compressed.txt middle.js compkey.txt after.js > functions.js

after.js: originalafter.js
	php processafter.php

compressed.txt: prefixcompressed.txt
	php unusedcompress.php

prefixcompressed.txt: funclist.txt
	php prefixcompress.php

funclist.txt:
	php makefunclist.php

funclist.zip: Makefile after.js before.js compkey.txt compressed.txt funclist.txt functions.js test.html makefunclist.php middle.js originalafter.js prefixcompress.php prefixcompressed.txt processafter.php readme.txt unusedcompress.php
	zip funclist.zip Makefile after.js before.js compkey.txt compressed.txt funclist.txt functions.js test.html makefunclist.php middle.js originalafter.js prefixcompress.php prefixcompressed.txt processafter.php readme.txt unusedcompress.php

clean:
	rm funclist.txt prefixcompressed.txt compressed.txt functions.js after.js compkey.txt
