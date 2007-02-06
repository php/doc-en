<?php
/*
+----------------------------------------------------------------------+
| Functable Generator                                                  |
+----------------------------------------------------------------------+
| Copyright (c) 2006-2007 The PHP Group                                |
+----------------------------------------------------------------------+
| This source file is subject to version 3.0 of the PHP license,       |
| that is bundled with this package in the file LICENSE, and is        |
| available through the world-wide-web at the following url:           |
| http://www.php.net/license/3_0.txt.                                  |
| If you did not receive a copy of the PHP license and are unable to   |
| obtain it through the world-wide-web, please send a note to          |
| license@php.net so we can mail you a copy immediately.               |
+----------------------------------------------------------------------+
| Authors:    Sean Coates <sean@php.net>                               |
+----------------------------------------------------------------------+
$Id$
*/

// direct the output of this file into phpdoc/phpbook/phpbook-xsl/version.xml
// errors go to STDERR

// To do:
//  - TEST, please
//  - someone should really make this code parse the source and not just look
//    at the protos

// You'll need to set up an SQLite DB (see PATH_DB, below) with the following:
// CREATE TABLE func_tag (func_name VARCHAR(50), tag_name VARCHAR(50),
//   has_proto INT, is_alias INT, unicode_safe INT, source VARCHAR(255));

// set $_ENV['DO_CVS'] to use php-src
// set $_ENV['DO_PECL'] to use PECL

// set up config:

// set $_ENV['PATH_TMP'] to change the temporary directory
define('PATH_TMP', getenv('PATH_TMP') ? getenv('PATH_TMP') : '/tmp/functable');
fwrite(STDERR, 'PATH_TMP=' . PATH_TMP ."\n");

// set $_ENV['PATH_DB'] to change the sqlite db file
define('PATH_DB', getenv('PATH_DB') ? getenv('PATH_DB') : dirname(__FILE__) .'/functable.sqlite');
fwrite(STDERR, 'PATH_DB=' . PATH_DB ."\n");

// set $_ENV['PATH_CVS'] to change the path to your cvs binary
define('PATH_CVS', getenv('PATH_CVS') ? getenv('PATH_CVS') : '/usr/bin/cvs');
fwrite(STDERR, 'PATH_CVS=' . PATH_CVS ."\n");

// set $_ENV['FUNCTABLE_TAGS'] if you'd like to only grab specific tags

function rm_recursive($path)
{
    if (!is_dir($path)) {
        return;
    }
    $dir = new DirectoryIterator($path);
    while($dir->valid()) {
        if(!$dir->isDot()) {
            if ($dir->isDir()) {
                rm_recursive($path . DIRECTORY_SEPARATOR . $dir->current());
            } else {
                unlink($path . DIRECTORY_SEPARATOR . $dir->current());
            }
        }
        $dir->next();
    }
    rmdir($path);
}

function get_php_release_tags()
{
    rm_recursive(PATH_TMP);
    mkdir(PATH_TMP);
    chdir(PATH_TMP);

    $cmd = PATH_CVS .' -q -d :pserver:cvsread@cvs.php.net:/repository co php-src/ChangeLog';
    exec($cmd);
    chdir(PATH_TMP . '/php-src');
    $cmd = PATH_CVS .' log ChangeLog';

    $log = explode("\n", `$cmd`);
    do {
        $l = array_shift($log);
        if ($l == 'symbolic names:') {
            break;
        }
    } while (1);

    if (!$standalone) {
        $tags = array();
    }

    foreach ($log as $l) {
        if (substr($l, 0, 1) != "\t") {
            break;
        }
        list($tag,) = explode(': ', trim($l));
        if (preg_match('/^PHP_[456]_[0-9]+_[0-9]+$/i', $tag)) {
            $tags[] = $tag;
        }
    }
    return array_reverse($tags);
}

function get_pecl_packages()
{
    $packages = array();
    $XE = @new SimpleXMLElement('http://pecl.php.net/rest/p/packages.xml', NULL, true); //@ sucks, but the XML doesn't like me
    foreach ($XE as $Element) {
        if ($Element->getName() == 'p') {
            $packages[] = strtolower((string) $Element);
        }
    }
    return $packages;
}

function get_pecl_releases($package)
{
    $releases = array();
    try {
        $XE = @new SimpleXMLElement("http://pecl.php.net/rest/r/$package/allreleases.xml", NULL, true); //@ sucks, but the XML doesn't like me
        foreach ($XE as $Element) {
            if ($Element->getName() == 'r') {
                if (preg_match('/[0-9]+\.[0-9]+(\.[0-9]+)?$/', (string) $Element->v)) {
                    $releases[] = (string) $Element->v;
                }
            }
        }
        sort($releases);
        return $releases;    
    } catch (Exception $e) {
        fwrite(STDERR, " WARN: NO RELEASES\n");
        return false;
    }
}

function grab_pecl_release($package, $release)
{
    rm_recursive(PATH_TMP);
    mkdir(PATH_TMP);
    chdir(PATH_TMP);
    
    $remoteTarball = 'http://pecl.php.net/get/'. urlencode($package) . '-' . urlencode($release);
    file_put_contents(PATH_TMP . '/pecl_tarball.tgz', file_get_contents($remoteTarball));
    
    // should probably do this as natively as possible.. @@@fixme
    exec('/bin/tar zxf pecl_tarball.tgz');
}

function checkout_tag($tag)
{
    rm_recursive(PATH_TMP);
    mkdir(PATH_TMP);
    chdir(PATH_TMP);

    $cmd = PATH_CVS .' -d :pserver:cvsread@cvs.php.net:/repository co -r '.
        escapeshellarg($tag) . ' php-src';
    exec($cmd);
}

function get_src_files($path, $depth=0)
{
    if ($depth > 100) {
        die("TOO DEEP\n");
    }
    $files = array();
    $dir = scandir($path);
    foreach ($dir AS $f) {
        if ($f != '.' && $f != '..') {
            $p = $path . DIRECTORY_SEPARATOR . $f;
            if (is_dir($p)) {
                $files = array_merge($files, get_src_files($p, $depth+1));
            } elseif (substr($f, -2) == '.c') {
                $files[] = $p;
            }
        }
    }
    return $files;
}

function parse_protos($path)
{
    $funcs = array();
    $protoFuncs = array();

    $protoRegex = '/
        {{{\s*proto\s+              # proto identifier
        (.*?)\s+                    # type
        ([^(\s]+?)\s?               # functon name
        \((.*)\)\s*                 # params
        ([^*;{]*)                   # suffix
        /ix';                           // }}} annoying folding
    $ZendFB_regex  = "`^[ \t]*(?:static)?[ \t]*(?:zend_)?function_entry\s*(?!php_hw_api_)\w+(?<!_class_functions)\s*\[\]\s*=\s*\{(.*)(?:\{\s*NULL\s*,\s*NULL\s*,\s*NULL\s*\}|\{0\})`msU";
    $FB_instance_regex = "`^[ \t]*(?:($macronames)\s*\(|\{)\s*\"?(\w+)`im";
    $macronames = "ZEND_FE|ZEND_FALIAS|PHP_FE|PHP_FALIAS|ZEND_NAMED_FE|PHP_NAMED_FE|PHP_STATIC_FE";

    $files = get_src_files($path);

    foreach ($files AS $f) {

        // protos are more verbose than source, check them first
        $protos = preg_grep($protoRegex, file($f));
        if ($protos) {
            foreach ($protos AS $line => $p) {
                preg_match($protoRegex, $p, $m);
                $thisProto = array(
                    'file' => substr($f, strlen(SRC_DIR) + 1),
                    'type' => $m[1],
                    'func' => strtolower($m[2]),
                    'params' => $m[3],
                    'suffix' => $m[4],
                    'line' => $line + 1
                );
                if ($m[4] && trim($m[4]) == 'U') {
                    $thisProto['unicode'] = true;
                } else {
                    $thisProto['unicode'] = false;
                }

                $funcs[] = $thisProto;
                $protoFuncs[] = $m[2];

            }
        }

        // check source
        // this section from phpdoc/scripts/genfunclist.php, see that file for (c) info
        // same goes for ZendFB_regex, above
        // macronames and FB_instance_regex

        // this could probably be optimized -- please feel free

        // function blocks
        $file_contents = file_get_contents($f);
        if (preg_match_all($ZendFB_regex, $file_contents, $matches)) {
            foreach ($matches[0] as $mk => $mv) {
                $block_titles[$mk] = strtok($mv, "\n");
            }
            $tok = strtok($matches[1], "\n");
            while ($tok) {
                if (preg_match($FB_instance_regex, $tok, $matches)) {
                    if (!in_array($matches[2], $protoFuncs)) {
                        $funcs[] = array(
                            'file' => substr($f, strlen(SRC_DIR) + 1),
                            'func' => strtolower($matches[2]),
                        );
                    }
                }
                $tok = strtok("\n");
            }
        }
    }
    return $funcs;
}

function convert_array_to_words($func, $tags4, $tags5, $tagsPECL)
{
    // this functions should be refactored
    
    static $phpRegex = '/PHP_([45])_([0-9])_([0-9])/';
    static $rep = '$1.$2.$3';
    
    // function exists in PHP 4?
    $text4 = '';
    $ft4 = array();
    foreach ($tags4 as $t) {
        if (isset($func[$t])) {
            $ft4[] = $t;
        }
    }    
    if (!$ft4) {
        $text4 = '';
    } elseif ($ft4 == $tags4) {
        $text4 = "PHP 4";
    } else {
        if ($ft4[0] == $tags4[0]) {
            $text4 = "PHP 4 <= " . preg_replace($phpRegex, $rep, $ft4[count($ft4) - 1]);
        } else {
            $text4 = "PHP 4 >= " . preg_replace($phpRegex, $rep, $ft4[0]);
        }
    }
    
    // function exists in PHP 5?
    $text5 = '';
    $ft5 = array();
    foreach ($tags5 as $t) {
        if (isset($func[$t])) {
            $ft5[] = $t;
        }
    }
    if (!$ft5) {
        $text5 = '';
    } elseif ($ft5 == $tags5) {
        $text5 = "PHP 5";
    } else {
        if ($ft5[0] == $tags5[0]) {
            $text5 = "PHP 5 <= " . preg_replace($phpRegex, $rep, $ft5[count($ft5) - 1]);
        } else {
            $text5 = "PHP 5 >= " . preg_replace($phpRegex, $rep, $ft5[0]);
        }
    }
    
    // function exists in PECL?
    $textPECL = '';
    if ($tagsPECL) {
        $pkgPECL = array();
        // determine pacakges
        foreach ($tagsPECL as $tag) {
            list($pkg, $ver) = explode('-', $tag);
            if (isset($func[$tag])) {
                if (!isset($pkgPECL[$pkg])) {
                    $pkgPECL[$pkg] = array();
                }
                $pkgPECL[$pkg][] = $ver;
            }
        }
        foreach ($pkgPECL as $pkg => $vers) {
            if ($textPECL) {
                $textPECL .= ' ';   // if there's something in the text, prepend a space
            }
            $textPECL .= strtolower($pkg) .':'. $vers[0];
            if (count($vers) > 1) {
                $textPECL .= '-'. $vers[count($vers) - 1];
            }
        }
        if ($textPECL) {
            $textPECL = 'PECL ' . $textPECL; // prepend PECL
        }
    }

    // output
    $texts = array_filter(array($text4, $text5, $textPECL)); // ignore empty classes
    return implode(', ', $texts);
}

function store_protos($tag, $protos, $source = 'php-src')
{
    $dbh = new PDO('sqlite:' . PATH_DB);

    $s = $dbh->prepare('DELETE FROM func_tag WHERE tag_name = ?');
    $s->bindParam(1, $tag);
    $s->execute();

    foreach ($protos as $p) {
        $s = $dbh->prepare("INSERT INTO "
                 . "func_tag (func_name, tag_name, has_proto, unicode_safe, source) "
                 . "VALUES (?, ?, 1, ?, ?)");
        $s->bindParam(1, $p['func']);
        $s->bindParam(2, $tag);
        $s->bindParam(3, $p['unicode']);
        $s->bindParam(4, $source);
        $s->execute();
    }
}

///////////////
///////////////

if (!getenv('DO_CVS')) {
	fwrite(STDERR, "Skipping CVS\n");
} else {
	fwrite(STDERR, "Using CVS\n");    
    if (getenv('FUNCTABLE_TAGS')) {
        $tags = explode(' ', getenv('FUNCTABLE_TAGS'));
    } else {
        $tags = get_php_release_tags();
    }
    
	fwrite(STDERR, "PHP Tags: " . implode(' ', $tags) ."\n");
    
    foreach ($tags as $tag) {
		fwrite(STDERR, "Getting tag: $tag\n");
        checkout_tag($tag);

        $protos = parse_protos(PATH_TMP . DIRECTORY_SEPARATOR . 'php-src');
        store_protos($tag, $protos);
    }
}

if (!getenv('DO_PECL')) {
	fwrite(STDERR, "Skipping PECL\n");
} else {
	fwrite(STDERR, "Using PECL\n");
    $releases = array();
    if ($envReleases = getenv('PECL_RELEASES')) {
        $envReleases = explode(' ', $envReleases);
        sort($envReleases);
        foreach ($envReleases as $envR) {
            list($pkg, $ver) = explode('-', $envR);
            if (isset($ver)) { // (allow for "runkit" instead of "runkit-0.8"
                $vers = array($ver);
            } else {
                $vers = get_pecl_releases($pkg);
            }
            if ($vers) {
                foreach ($vers as $ver) {
                    if (!isset($releases[$pkg])) {
                        $releases[$pkg] = array();
                    }
                    if (!in_array($ver, $releases[$pkg])) {
                        $releases[$pkg][] = $ver;
                    }
                }
            }
        }
    } else {
        foreach (get_pecl_packages() as $pkg) {
           	fwrite(STDERR, "Fetching releases for: $pkg\n");
            if ($peclReleases = get_pecl_releases($pkg)) {
                foreach ($peclReleases as $ver) {
                    if (!isset($releases[$pkg])) {
                        $releases[$pkg] = array();
                    }
                    $releases[$pkg][] = $ver;
                }
            }
        }
    }
    
    foreach ($releases as $pkg => $versions) {
        foreach ($versions as $ver) {
            $pkgName = $pkg . '-' . $ver;
            fwrite(STDERR, "Grabbing PECL Release: " . $pkgName ."\n");
            grab_pecl_release($pkg, $ver);
            fwrite(STDERR, "Parsing protos ...\n");
            // sometimes PECL package name case is broken:
            $dirName = false;
            if (is_dir(PATH_TMP . DIRECTORY_SEPARATOR . $pkgName)) {
                $dirName = PATH_TMP . DIRECTORY_SEPARATOR . $pkgName;
            } elseif (is_dir(PATH_TMP . DIRECTORY_SEPARATOR . strtolower($pkgName))) {
                $dirName = PATH_TMP . DIRECTORY_SEPARATOR . strtolower($pkgName);
            } elseif (is_dir(PATH_TMP . DIRECTORY_SEPARATOR . strtoupper($pkgName))) {
                $dirName = PATH_TMP . DIRECTORY_SEPARATOR . strtoupper($pkgName);
            } else {
                fwrite(STDERR, "ERROR: unable to determine package directory.\n");
            }
            if ($dirName) {
                $protos = parse_protos($dirName);
                fwrite(STDERR, "Storing protos ...\n");
                store_protos($pkgName, $protos, 'PECL');
            }
        }
    }
}

// generate versions.xsl

echo "<?xml version='1.0' encoding='iso-8859-1'?>\n"; // short tags )-:
?>
<!-- DO NOT EDIT THIS FILE !!!
;; It was generated programmatically
;; see cvs:phpdoc/scripts/functable.php
;; -->

<versions>
 <function name='array' from='PHP 4, PHP 5'/>
 <function name='die' from='PHP 4, PHP 5'/>
 <function name='echo' from='PHP 4, PHP 5'/>
 <function name='empty' from='PHP 4, PHP 5'/>
 <function name='eval' from='PHP 4, PHP 5'/>
 <function name='exit' from='PHP 4, PHP 5'/>
 <function name='isset' from='PHP 4, PHP 5'/>
 <function name='list' from='PHP 4, PHP 5'/>
 <function name='print' from='PHP 4, PHP 5'/>
 <function name='unset' from='PHP 4, PHP 5'/>
<?php
$dbh = new PDO('sqlite:' . PATH_DB);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$tags = array();
$funcs = array();

$s = $dbh->prepare("
	SELECT
		func_name
	FROM
		func_tag
	WHERE
		UPPER(tag_name) = ?
        AND
        source = ?
	ORDER BY
		tag_name
");

$tq = $dbh->query("
	SELECT
		DISTINCT UPPER(tag_name) AS tag_name,
        source
	FROM
		func_tag
	WHERE
        (
            tag_name LIKE 'PHP\\__\\__\\__' ESCAPE '\\'
            AND
            source = 'php-src'
        )
        OR
        source = 'PECL'
	ORDER BY
		UPPER(tag_name)
");

foreach ($tq as $tr) {
	$tags[] = $tr['tag_name'];
	if ($s->execute(array($tr['tag_name'], $tr['source']))) {
		while ($fr = $s->fetch()) {
			if ($fr['func_name']) {
                $funcs[$fr['func_name']][$tr['tag_name']] = 1;
			}
		}
	}
}
ksort($funcs);

$tags4 = array();
$tags5 = array();
$tagsPECL = array();
foreach ($tags as $t) {
	if (substr($t,0,5) == 'PHP_4') {
		$tags4[] = $t;
	} elseif (substr($t,0,5) == 'PHP_5') {
		$tags5[] = $t;
	} else { // must be PECL
        $tagsPECL[] = $t;
	}
}

foreach ($funcs as $funcname => $func) {
	$text = htmlspecialchars(convert_array_to_words($func, $tags4, $tags5, $tagsPECL));
	echo " <function name='{$funcname}' from='{$text}'/>\n";
}
?>
</versions>

