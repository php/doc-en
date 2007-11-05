<?php

/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2007 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Dave Barr <dave@php.net>                                 |
  +----------------------------------------------------------------------+
  
  $Id$
*/

$cvs_id = '$Id$';
$srcdir = "./";

echo "configure.php: $cvs_id\n";

// Settings
$cygwin_php_bat = '../phpdoc-tools/php.bat';
$cygwin_jade = '../phpdoc-tools/jade/jade.exe';
$cygwin_openjade = '../phpdoc-tools/openjade/openjade.exe';
$cygwin_nsgmls = '../phpdoc-tools/jade/nsgmls.exe';
$cygwin_onsgmls = '../phpdoc-tools/openjade/onsgmls.exe';
$cygwin_xsltproc = '../phpdoc-tools/libxml/xsltproc.exe';
$cygwin_xmllint = '../phpdoc-tools/libxml/xmllint.exe';
$php_bin_names = array('php', 'php5', 'cli/php', 'php.exe', 'php5.exe', 'php-cli.exe', 'php-cgi.exe');
$jade_bin_names = array('openjade', 'jade', 'openjade.exe', 'jade.exe');
$nsgmls_bin_names = array('onsgmls', 'nsgmls', 'onsgmls.exe', 'nsgmls.exe');
$xsltproc_bin_names = array('xsltproc', 'xsltproc.exe');
$xmllint_bin_names = array('xmllint', 'xmllint.exe');
$catalogs = array(
    "$srcdir/entities/ISO/catalog",
    "$srcdir/dsssl/docbook/catalog",
    "../phpdoc-tools/jade/catalog",
    "/usr/share/sgml/CATALOG.docbk41",
    "/usr/share/sgml/CATALOG.jade_dsl",
    "$srcdir/dsssl/defaults/catalog",
);
$sources = array("$srcdir/../php-src", "$srcdir/../php5");
$pear_sources = array("$srcdir/../pear");
$pecl_sources = array("$srcdir/../pecl");
$infiles_cache = "$srcdir/infiles.cache";

// Reject old PHP installations
if (phpversion() < 5) {
    echo "PHP 5 or above is required. Version detected: " . phpversion() . "\n";
    exit(100);
}
else {
    echo "PHP version: " . phpversion() . "\n";
}

echo "\n";

$ac = array();
$ac['srcdir'] = $srcdir;
$ac['WORKDIR'] = $srcdir;
$ac['PHP'] = '';
$ac['CYGWIN'] = 0;
$ac['INIPATH'] = './scripts';
$ac['JADE'] = '';
$ac['WINJADE'] = 0;
$ac['NSGMLS'] = '';
$ac['XSLTPROC'] = '';
$ac['XMLLINT'] = '';
$ac['DOCBOOK_HTML'] = "$srcdir/docbook/docbook-dsssl/html/docbook.dsl";
$ac['DOCBOOK_PRINT'] = "$srcdir/docbook/docbook-dsssl/print/docbook.dsl";
$ac['CATALOG'] = '';
$ac['PHP_SOURCE'] = '';
$ac['PEAR_SOURCE'] = '';
$ac['PECL_SOURCE'] = '';
$ac['EXT_SOURCE'] = '';
$ac['HTMLCSS'] = '';
$ac['CHMENABLED'] = 'no';
$ac['CHMONLY_INCL_BEGIN'] = '<!--';
$ac['CHMONLY_INCL_END'] = '-->';
$ac['INTERNALSDISABLED'] = 'no';
$ac['INTERNALS_EXCL_BEGIN'] = '';
$ac['INTERNALS_EXCL_END'] = '';
$ac['HACK_RTL_LANGS_PAGES'] = '';
$ac['HACK_RTL_LANGS_PHPWEB'] = '';
$ac['LANG_HACK_FOR_HE'] = 'no';
$ac['LANG'] = 'en';
$ac['LANGWEB'] = 'en';
$ac['PHP_BUILD_DATE'] = date('Y-m-d');
$ac['MANUAL'] = 'php_manual_en';
$ac['PAPER_TYPE'] = '';
$ac['PDF_PAPER_TYPE'] = '';
$ac['NUMBER_FIRST'] = '#f';
$ac['LEFT_MARGIN'] = '';
$ac['RIGHT_MARGIN'] = '';
$ac['TOP_MARGIN'] = '';
$ac['BOTTOM_MARGIN'] = '';
$ac['HEADER_MARGIN'] = '';
$ac['FOOTER_MARGIN'] = '';
$ac['LINE_SPACING'] = '';
$ac['HEAD_BEFORE'] = '';
$ac['HEAD_AFTER'] = '';
$ac['BODY_START'] = '';
$ac['BLOCK_SEP'] = '';
$ac['TREESAVING'] = '#f';
$ac['ENCODING'] = '';
$ac['PALMDOCTITLE'] = '';
$ac['HTMLHELP_ENCODING'] = '';
$ac['SP_OPTIONS'] = 'SP_ENCODING=XML SP_CHARSET_FIXED=YES';
$ac['FORCE_DOM_SAVE'] = false;

$allowed_opts = array(
    'help',
    'force-dom-save',
    'with-php=',
    'with-jade=',
    'with-nsgmls=',
    'with-xsltproc=',
    'with-xmllint=',
    'with-dsssl=',
    'with-source=',
    'with-pear-source==',
    'with-pecl-source==',
    'with-extension=',
    'with-htmlcss=',
    'with-chm=',
    'without-internals',
    'with-lang=',
    'with-treesaving',
);

foreach ($_SERVER['argv'] as $opt) {
    if (strpos($opt, "=") !== false) {
        list($o, $v) = explode("=", $opt);
    } else {
        $o = $opt;
        $v = "yes";
    }

    switch ($o) {
        case '--help':
            usage();
            exit();
        case '--with-cygwin';
            if ($v == "yes") {
                $ac['CYGWIN'] = 1;
            } else {
                $ac['GYGWIN'] = 0;
            }
            break;
        case '--with-php':
            $ac['PHP'] = $v;
            break;
        case '--with-inipath':
            $ac['INIPATH'] = $v;
            break;
        case '--with-jade':
            $ac['JADE'] = $v;
            break;
        case '--with-nsgmls':
            $ac['NSGMLS'] = $v;
            break;
        case '--with-xsltproc':
            $ac['XSLTPROC'] = $v;
            break;
        case '--with-xmllint':
            $ac['XMLLINT'] = $v;
            break;
        case '--with-dsssl':
            $ac['DOCBOOK_HTML'] = "$v/html/docbook.dsl";
            $ac['DOCBOOK_PRINT'] = "$v/print/docbook.dsl";
            break;
        case '--with-source':
            $ac['PHP_SOURCE'] = $v;
            break;
        case '--with-pear-source':
            $ac['PEAR_SOURCE'] = $v != '' ? $v : 'yes';
            break;
        case '--with-pecl-source':
            $ac['PECL_SOURCE'] = $v != '' ? $v : 'yes';
            break;
        case '--with-extension':
            $ac['EXT_SOURCE'] = $v;
            break;
        case '--with-htmlcss':
            $ac['HTMLCSS'] = $v;
            break;
        case '--with-chm':
            $ac['CHMENABLED'] = $v;
            break;
        case '--without-internals':
            $ac['INTERNALSDISABLED'] = 'yes';
            break;
        case '--with-lang':
            $ac['LANG'] = $v;
            break;
        case '--with-treesaving':
            $ac['TREESAVING'] = '#t';
            break;
        case '--force-dom-save':
            $ac['FORCE_DOM_SAVE'] = true;
            break;
    }
}

function find_file($file_array)
{
    $paths = explode(PATH_SEPARATOR, getenv('PATH'));

    if (is_array($paths)) {
        foreach ($paths as $path) {
            foreach ($file_array as $name) {
                if (file_exists("$path/$name") && is_file("$path/$name")) {
                    return "$path/$name";
                }
            }
        }
    }

    return '';
}

// Check for PHP executable
echo 'checking for php: ';

if ($ac['PHP'] == '') {
    // Find PHP executable ourselves
    $ac['PHP'] = find_file($php_bin_names);
}
else if (file_exists($cygwin_php_bat)) {
    $ac['PHP'] = $cygwin_php_bat;
    echo "$cygwin_php_bat\n";
}

if ($ac['PHP'] == '') {
    echo "no\n";
    echo "  Error: Could not find a PHP executable. Use --with-php=/path/to/php\n";
    exit(103);
}

if (!file_exists($ac['PHP'])) {
    echo "no\n";
    echo "  Error: --with-php: {$ac['PHP']} does not exist.\n";
    exit(104);
}
else if (!is_file($ac['PHP'])) {
    echo "no\n";
    echo "  Error: --with-php: {$ac['PHP']} is not a file.\n";
    exit(105);
}
else {
    echo $ac['PHP'] . "\n";
}


// Check for Jade/OpenJade executable
echo 'checking for jade: ';

if ($ac['JADE'] != '') {
    // User-supplied
    if (!file_exists($ac['JADE'])) {
        echo "no\n";
        echo "  Error: --with-jade: {$ac['JADE']} does not exist.\n";
        exit(106);
    }
    else if (!is_file($ac['JADE'])) {
        echo "no\n";
        echo "  Error: --with-jade: {$ac['JADE']} is not a file.\n";
        exit(107);
    }
}
else {
    // Find Jade executable ourselves
    $ac['JADE'] = find_file($jade_bin_names);
}

if ($ac['JADE'] == '' && file_exists($cygwin_jade) && is_file($cygwin_jade)) {
    $ac['JADE'] = $cygwin_jade;
    $ac['WINJADE'] = 1;
}
else if ($ac['JADE'] == '' && file_exists($cygwin_openjade) && is_file($cygwin_openjade)) {
    $ac['JADE'] = $cygwin_openjade;
    $ac['WINJADE'] = 1;
}

if ($ac['JADE'] == '') {
    echo "no\n";
    echo "  Error: Could not find a Jade/OpenJade executable. Use --with-jade=/path/to/jade\n";
}
else {
    echo $ac['JADE'] . "\n";
}


// Check for NSGMLS executable
echo 'checking for nsgmls: ';

if ($ac['NSGMLS'] != '') {
    // User-supplied
    if (!file_exists($ac['NSGMLS'])) {
        echo "no\n";
        echo "  Error: --with-nsgmls: {$ac['NSGMLS']} does not exist.\n";
        exit(109);
    }
    else if (!is_file($ac['NSGMLS'])) {
        echo "no\n";
        echo "  Error: --with-nsgmls: {$ac['NSGMLS']} is not a file.\n";
        exit(110);
    }
}
else {
    // Find NSGMLS executable ourselves
    $ac['NSGMLS'] = find_file($nsgmls_bin_names);
}

if ($ac['NSGMLS'] == '' && file_exists($cygwin_nsgmls) && is_file($cygwin_nsgmls)) {
    $ac['NSGMLS'] = $cygwin_nsgmls;
}
else if ($ac['NSGMLS'] == '' && file_exists($cygwin_onsgmls) && is_file($cygwin_onsgmls)) {
    $ac['NSGMLS'] = $cygwin_onsgmls;
}

if ($ac['NSGMLS'] == '') {
    echo "no\n";
    echo "  Error: Could not find a NSGMLS executable. Use --with-nsgmls=/path/to/jade\n";
}
else {
    echo $ac['NSGMLS'] . "\n";
}


// Check for XSLTPROC executable
echo 'checking for xsltproc: ';

if ($ac['XSLTPROC'] != '') {
    // User-supplied
    if (!file_exists($ac['XSLTPROC'])) {
        echo "no\n";
        echo "  Error: --with-xsltproc: {$ac['XSLTPROC']} does not exist.\n";
        exit(112);
    }
    else if (!is_file($ac['XSLTPROC'])) {
        echo "no\n";
        echo "  Error: --with-xsltproc: {$ac['XSLTPROC']} is not a file.\n";
        exit(113);
    }
}
else {
    // Find XSLTPROC executable ourselves
    $ac['XSLTPROC'] = find_file($xsltproc_bin_names);
}

if ($ac['XSLTPROC'] == '' && file_exists($cygwin_xsltproc) && is_file($cygwin_xsltproc)) {
    $ac['XSLTPROC'] = $cygwin_xsltproc;
}

if ($ac['XSLTPROC'] == '') {
    echo "no\n";
    echo "  Warning: Could not find a XSLTPROC executable. XSL Transformations won't work.\n";
}
else {
    echo $ac['XSLTPROC'] . "\n";
}


// Check for XMLLINT executable
echo 'checking for xmllint: ';

if ($ac['XMLLINT'] != '') {
    // User-supplied
    if (!file_exists($ac['XMLLINT'])) {
        echo "no\n";
        echo "  Error: --with-xmllint: {$ac['XMLLINT']} does not exist.\n";
        exit(114);
    }
    else if (!is_file($ac['XMLLINT'])) {
        echo "no\n";
        echo "  Error: --with-xmllint: {$ac['XMLLINT']} is not a file.\n";
        exit(115);
    }
}
else {
    // Find XMLLINT executable ourselves
    $ac['XMLLINT'] = find_file($xmllint_bin_names);
}

if ($ac['XMLLINT'] == '' && file_exists($cygwin_xmllint) && is_file($cygwin_xmllint)) {
    $ac['XMLLINT'] = $cygwin_xmllint;
}

if ($ac['XMLLINT'] == '') {
    echo "no\n";
    echo "  Warning: Could not find a XMLLINT executable. XSL Validation won't work.\n";
}
else {
    echo $ac['XMLLINT'] . "\n";
}


// Check for DocBook DSLs
echo 'checking for docbook (HTML): ';

if (!file_exists($ac['DOCBOOK_HTML'])) {
    echo "no\n";
    echo "  Warning: {$ac['DOCBOOK_HTML']} does not exist.\n";
}
else if (!is_file($ac['DOCBOOK_HTML'])) {
    echo "no\n";
    echo "  Warning: {$ac['DOCBOOK_HTML']} is not a file.\n";
}
else {
    echo $ac['DOCBOOK_HTML'] . "\n";
}

echo 'checking for docbook (print): ';

if (!file_exists($ac['DOCBOOK_PRINT'])) {
    echo "no\n";
    echo "  Warning: {$ac['DOCBOOK_PRINT']} does not exist.\n";
}
else if (!is_file($ac['DOCBOOK_PRINT'])) {
    echo "no\n";
    echo "  Warning: {$ac['DOCBOOK_PRINT']} is not a file.\n";
}
else {
    echo $ac['DOCBOOK_PRINT'] . "\n";
}


// Check for Catalogs
echo 'checking catalogs: ';

foreach ($catalogs as $catalog) {
    if (file_exists($catalog) && is_file($catalog)) {
        $ac['CATALOG'] .= " -c $catalog";
    }
}

if ($ac['CATALOG'] == '') {
    echo "no\n";
    echo "  Warning: No catalog files found.\n";
}
else {
    $ac['CATALOG'] = substr($ac['CATALOG'], 1);

    echo $ac['CATALOG'] . "\n";
}


// Check for PHP Source
echo 'checking for PHP source: ';

if ($ac['PHP_SOURCE'] != '') {
    if (!file_exists($ac['PHP_SOURCE'])) {
        echo "no\n";
        echo "  Warning: {$ac['PHP_SOURCE']} does not exist.\n";
    }
    else if (!is_dir($ac['PHP_SOURCE'])) {
        echo "no\n";
        echo "  Warning: {$ac['PHP_SOURCE']} is not a directory.\n";
    }
    else {
        echo $ac['PHP_SOURCE'] . "\n";
    }
}
else {
    foreach ($sources as $source) {
        if (file_exists($source) && is_dir($source)) {
            $ac['PHP_SOURCE'] = $source;
            break;
        }
    }

    if ($ac['PHP_SOURCE'] == '') {
        echo "no\n";
        echo "  Warning: No PHP source directory found.\n";
    }
    else {
        echo $ac['PHP_SOURCE'] . "\n";
    }
}


// Check for PEAR Source
echo 'checking for PEAR source: ';

if ($ac['PEAR_SOURCE'] != '') {
    if ($ac['PEAR_SOURCE'] != 'yes') {
        if (!file_exists($ac['PEAR_SOURCE'])) {
            echo "no\n";
            echo "  Warning: {$ac['PEAR_SOURCE']} does not exist.\n";
        }
        else if (!is_dir($ac['PEAR_SOURCE'])) {
            echo "no\n";
            echo "  Warning: {$ac['PEAR_SOURCE']} is not a directory.\n";
        }
        else {
            echo $ac['PEAR_SOURCE'] . "\n";
        }
    }
    else {
        foreach ($pear_sources as $source) {
            if (file_exists($source) && is_dir($source)) {
                $ac['PEAR_SOURCE'] = $source;
                break;
            }
        }

        if ($ac['PEAR_SOURCE'] == '') {
            echo "no\n";
            echo "  Warning: No PEAR source directory found.\n";
        }
        else {
            echo $ac['PEAR_SOURCE'] . "\n";
        }
    }
}
else {
    $ac['PEAR_SOURCE'] = 'no';
    echo "no\n";
}

// Check for PECL Source
echo 'checking for PECL source: ';

if ($ac['PECL_SOURCE'] != '') {
    if ($ac['PECL_SOURCE'] != 'yes') {
        if (!file_exists($ac['PECL_SOURCE'])) {
            echo "no\n";
            echo "  Warning: {$ac['PECL_SOURCE']} does not exist.\n";
        }
        else if (!is_dir($ac['PECL_SOURCE'])) {
            echo "no\n";
            echo "  Warning: {$ac['PECL_SOURCE']} is not a directory.\n";
        }
        else {
            echo $ac['PECL_SOURCE'] . "\n";
        }
    }
    else {
        foreach ($pecl_sources as $source) {
            if (file_exists($source) && is_dir($source)) {
                $ac['PECL_SOURCE'] = $source;
                break;
            }
        }

        if ($ac['PECL_SOURCE'] == '') {
            echo "no\n";
            echo "  Warning: No PECL source directory found.\n";
        }
        else {
            echo $ac['PECL_SOURCE'] . "\n";
        }
    }
}
else {
    $ac['PECL_SOURCE'] = 'no';
    echo "no\n";
}

// Check for additional PHP extensions

if ($ac['EXT_SOURCE'] != '') {
    echo "checking for additional PHP extensions:\n";

    $exts = explode(',', $ac['EXT_SOURCE']);
    $ac['EXT_SOURCE'] = '';

    foreach ($exts as $ext) {
        if (file_exists("$ext/manual") && is_dir("$ext/manual")) {
            $ac['EXT_SOURCE'] .= ":$ext";
            echo "  extension '$ext' ok\n";
        }
        else {
            echo "  extension '$ext' ignored\n";
        }
    }
}


// Check for CSS to use for HTML docs
echo 'checking for CSS to use for HTML docs: ';

if ($ac['HTMLCSS'] != '') {
    echo $ac['HTMLCSS'] . "\n";
    $ac['HTMLCSS'] = "(define %stylesheet% \"{$ac['HTMLCSS']}\")";
}
else {
    echo "none\n";
}


// Check for CHM
echo 'checking for CHM-only inclusion: ';
if ($ac['CHMENABLED'] == 'yes') {
    $ac['CHMONLY_INCL_BEGIN'] = '';
    $ac['CHMONLY_INCL_END'] = '';
    echo "enabled\n";
}
else {
    $ac['CHMENABLED'] = 'no';
    echo "disabled\n";
}


// Check for internals
echo 'checking for internals doc exclusion: ';
if ($ac['INTERNALSDISABLED'] == 'yes') {
    $ac['INTERNALS_EXCL_BEGIN'] = '<!--';
    $ac['INTERNALS_EXCL_END'] = '-->';
    echo "yes\n";
}
else {
    $ac['INTERNALSDISABLED'] = 'no';
    echo "no\n";
}


// Check for language
echo 'checking for language: ';

if ($ac['LANG'] != 'en') {
    if (!file_exists("$srcdir/{$ac['LANG']}")) {
        echo "no\n";
        echo "  Error: Language '{$ac['LANG']}' not supported!\n";
        exit(116);
    }

    $ac['MANUAL'] = "php_manual_{$ac['LANG']}";

    switch ($ac['LANG']) {
        case 'kr':
            $ac['LANG'] = 'ko';
            $ac['LANGWEB'] = 'ko';
            $ac['LANGDIR'] = 'kr';
            break;
        case 'ja':
            $ac['LANG'] = 'ja';
            $ac['LANGWEB'] = 'ja';
            $ac['LANGDIR'] = 'ja';
            $ac['PHP_BUILD_DATE'] = date('Y/m/d');
            break;
        case 'he':
            $ac['LANG'] = 'en';
            $ac['LANGWEB'] = 'he';
            $ac['LANGDIR'] = 'he';
            $ac['LANG_HACK_FOR_HE'] = 'yes';
            $ac['HACK_RTL_LANGS_PAGES'] = $ac['PHP'] . ' ' . "$srcdir/scripts/rtlpatch/rtlpatch.php ./html";
            $ac['HACK_RTL_LANGS_PHPWEB'] = $ac['PHP'] . ' ' . "$srcdir/scripts/rtlpatch/rtlpatch.php ./php";
            break;
        case 'hk':
            $ac['LANG'] = 'zh_hk';
            $ac['LANGWEB'] = 'zh_hk';
            $ac['LANGDIR'] = 'hk';
            break;
        case 'tw':
            $ac['LANG'] = 'zh_tw';
            $ac['LANGWEB'] = 'zh_tw';
            $ac['LANGDIR'] = 'tw';
            break;
        case 'cn':
            $ac['LANG'] = 'zh_cn';
            $ac['LANGWEB'] = 'zh_cn';
            $ac['LANGDIR'] = 'tw';
            break;
        default:
            $ac['LANGWEB'] = $ac['LANG'];
            $ac['LANGDIR'] = $ac['LANG'];
            break;
    }

    echo $ac['LANG'] . "\n";
}
else {
    $ac['LANGDIR'] = $ac['LANG'];
    $ac['LANGWEB'] = $ac['LANG'];
    echo "en (default)\n";
}


// Check paper type
if (in_array($ac['LANG'], explode('|', 'ar|cs|de|hu|it|ja|ko|pl|ro|sk|tr|zh_hk|zh_tw|zh_cn'))) {
    $ac['PAPER_TYPE'] = 'A4';
    $ac['PDF_PAPER_TYPE'] = 'a4';
}
else {
    $ac['PAPER_TYPE'] = 'USletter';
    $ac['PDF_PAPER_TYPE'] = 'letter';
}


// Localize order of number and element name in some headers autogenerated by Jade
if (in_array($ac['LANG'], explode('|', 'hu|ko'))) {
    $ac['NUMBER_FIRST'] = '#t';
}


// Reduce margins?
echo 'checking for treesaving: ';

if ($ac['TREESAVING'] == '#t') {
    $ac['LEFT_MARGIN']   = '(define %left-margin% 4pi)';
    $ac['RIGHT_MARGIN']  = '(define %right-margin% 3pi)';
    $ac['TOP_MARGIN']    = '(define %top-margin% 3pi)';
    $ac['HEADER_MARGIN'] = '(define %header-margin% 2pi)';
    $ac['FOOTER_MARGIN'] = '(define %footer-margin% 2pi)';
    $ac['BOTTOM_MARGIN'] = '(define %bottom-margin% 3pi)';
    $ac['LINE_SPACING']  = '(define %line-spacing-factor% 1.2)';
    $ac['HEAD_BEFORE']   = '(define %head-before-factor% 0.6)';
    $ac['HEAD_AFTER']    = '(define %head-after-factor% 0.3)';
    $ac['BODY_START']    = '(define %body-start-indent% 3pi)';
    $ac['BLOCK_SEP']     = '(define %block-sep% (* %para-sep% 1.2))';
    echo "yes\n";
}
else {
    echo "no\n";
}

// Force creation of .manual.xml
echo 'checking for forced .manual.xml: ';

if ($ac['FORCE_DOM_SAVE']) {
    echo "yes\n";
}
else {
    echo "no\n";
}


// Encoding
switch ($ac['LANG']) {
    case 'zh_tw':
    case 'zh_hk':
        $ac['ENCODING'] = 'big5';
        break;
    case 'zh_cn':
        $ac['ENCODING'] = 'gb2312';
        break;
    case 'cs':
    case 'hu':
    case 'ro':
    case 'sk':
        $ac['ENCODING'] = 'ISO-8859-2';
        break;
    case 'ar':
        $ac['ENCODING'] = 'ISO-8859-6';
        break;
    case 'tr':
        $ac['ENCODING'] = 'ISO-8859-9';
        break;
    case 'he':
        $ac['ENCODING'] = 'ISO-8859-8';
        break;
    case 'el':
        $ac['ENCODING'] = 'ISO-8859-7';
        break;
    default:
        $ac['ENCODING'] = 'UTF-8';
        break;
}

if ($ac['LANG_HACK_FOR_HE'] == 'yes') {
    $ac['ENCODING'] = 'ISO-8859-8';
}


// Palm doc title
switch ($ac['LANG']) {
    case 'de':    $ac['PALMDOCTITLE'] = "'PHP Handbuch'"; break;
    case 'es':    $ac['PALMDOCTITLE']="'Manual de PHP'"; break;
    case 'fr':    $ac['PALMDOCTITLE']="'Manuel PHP'"; break;
    case 'hu':    $ac['PALMDOCTITLE']="'PHP Kézikönyv'"; break;
    case 'it':    $ac['PALMDOCTITLE']="'Manuale PHP'"; break;
    case 'nl':    $ac['PALMDOCTITLE']="'PHP Handleiding'"; break;
    case 'pl':    $ac['PALMDOCTITLE']="'Podrêcznik PHP'"; break;
    case 'pt_BR': $ac['PALMDOCTITLE']="'Manual do PHP'"; break;
    case 'ro':    $ac['PALMDOCTITLE']="'Manual PHP'"; break;
    case 'zh_hk': $ac['PALMDOCTITLE']="PHP ¤â¥U"; break;
    default:      $ac['PALMDOCTITLE']="'PHP Manual'"; break;
}


switch ($ac['ENCODING']) {
    case 'ISO-8859-2': $ac['HTMLHELP_ENCODING'] = 'windows-1250'; break;
    case 'ISO-8859-6': $ac['HTMLHELP_ENCODING'] = 'windows-1256'; break;
    case 'ISO-8859-8': $ac['HTMLHELP_ENCODING'] = 'windows-1255'; break;
    case 'ISO-8859-9': $ac['HTMLHELP_ENCODING'] = 'windows-1254'; break;
    default:           $ac['HTMLHELP_ENCODING'] = $ac['ENCODING']; break;
}

$ac['ENCODING'] = 'UTF-8';

/* recursive glob() with a callback function */
function globbetyglob($globber, $userfunc)
{
    foreach (glob("$globber/*") as $file) {
        if (is_dir($file)) {
            globbetyglob($file, $userfunc);
        }
        else {
            call_user_func($userfunc, $file);
        }
    }
}

function find_dot_in($filename) {
    if (substr($filename, -3) == '.in') {
        $GLOBALS['infiles'][] = $filename;
    }
}

function generate_output_file($in, $out, $ac) {
    $data = file_get_contents($in);

    if ($data === false)
        return false;

    foreach ($ac as $k => $v) {
        $data = preg_replace('/@' . preg_quote($k) . '@/', $v, $data);
    }

    return file_put_contents($out, $data);
}

function make_scripts_executable($filename) {
    if (substr($filename, -3) == '.sh') {
        chmod($filename, 0755);
    }
}

if (file_exists($infiles_cache)) {
    $infiles = file($infiles_cache);
}
else {
    $infiles = array();
    globbetyglob($srcdir, 'find_dot_in');
    file_put_contents($infiles_cache, implode("\n", $infiles));
}

$ac['AUTOGENERATED_RULES'] = '';
/*
foreach ($infiles as $in) {
    $in = basename(chop($in));
    $out = substr($in, 0, -3);

    if ($in == 'configure.in') {
        $ac['AUTOGENERATED_RULES'] .=   "configure: configure.in\n"
                                      . "\t autoconf\n";
    }
    else if ($in == 'manual.xml.in') {
    }
    else {
        $ac['AUTOGENERATED_RULES'] .=   "$in: $out config.status\n"
                                      . "\t CONFIG_FILES=$@ CONFIG_HEADERS= ./config.status\n";
    }
}
*/

foreach ($infiles as $in) {
    $in = chop($in);

    if (basename($in) == 'configure.in')
        continue;

    $out = substr($in, 0, -3);
    echo "generating $out: ";
    if (generate_output_file($in, $out, $ac)) {
        echo "done\n";
    }
    else {
        echo "fail\n";
        exit(117);
    }
}

globbetyglob($ac['INIPATH'], 'make_scripts_executable');
file_put_contents('./entities/phpweb.ent', '');

passthru('"' .$ac['PHP'] . '" ' . ' -c ' . $ac['INIPATH'] . ' -q ./scripts/file-entities.php');
passthru('rm -f entities/missing*');
passthru('rm -f entities/missing-ids.xml');
passthru('"' .$ac['PHP'] . '" ' . ' -c ' . $ac['INIPATH'] . ' -q ./scripts/missing-entities.php');

//print_r($ac);

$dom = new DOMDocument();
$dom->load("manual.xml", LIBXML_NOENT);
$dom->xinclude();
if ($dom->validate()) {
  echo "All good.\n";
  $dom->save(".manual.xml");
  echo "All you have to do now is run 'php build.php' in your phd checkout folder\n";
  exit(0); // Tell the shell that this script finished successfully.
} else {
  echo "eyh man. No worries. Happ shittens. Try again after fixing the errors above\n";
  if ($ac['FORCE_DOM_SAVE']) // Allow the .manual.xml file to be created, even if it is not valid.
    $dom->save(".manual.xml");
  exit(1); // Tell the shell that this script finished with an error.
}
  
