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
*/

// direct the output of this fine into phpdoc/phpbook/phpbook-xsl/version.xml
// errors go to STDERR

// To do:
//  - TEST, please
//  - PECL support

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

function get_release_tags()
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
        if (preg_match('/^PHP_[456]_[0-9]+_[0-9+]$/i', $tag)) {
            $tags[] = $tag;
        }
    }
    return array_reverse($tags);
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

    $files = get_src_files($path);
    foreach ($files AS $f) {

        $protoRegex = '/
            {{{\s*proto\s+              # proto identifier
            (.*?)\s+                    # type
            ([^(\s]+?)\s?               # functon name
            \((.*)\)\s*                 # params
            ([^*;{]*)                   # suffix
        /ix';
    
        $protos = preg_grep($protoRegex, file($f));
        if ($protos) {
            foreach ($protos AS $line => $p) {
                preg_match($protoRegex, $p, $m);
                $thisProto = array(
                    'file' => substr($f, strlen(SRC_DIR) + 1),
                    'type' => $m[1],
                    'func' => $m[2],
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
    }
    return $funcs;
}

function tag2ver($tag, $vertical)
{
    if ($vertical) {
        return chunk_split(str_replace('_', ' ', $tag), 1, "\n");
    }
}

function convert_array_to_words($func)
{
    // globals suck )-:
    global $tags4, $tags5;
    
    static $regex = '/PHP_([45])_([0-9])_([0-9])/';
    static $rep = '$1.$2.$3';
    
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
            $text4 = "PHP 4 <= " . preg_replace($regex, $rep, $ft4[count($ft4) - 1]);
        } else {
            $text4 = "PHP 4 >= " . preg_replace($regex, $rep, $ft4[0]);
        }
    }
    
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
            $text5 = "PHP 5 <= " . preg_replace($regex, $rep, $ft5[count($ft5) - 1]);
        } else {
            $text5 = "PHP 5 >= " . preg_replace($regex, $rep, $ft5[0]);
        }
    }

    if ($text4 && $text5) {
        return "$text4, $text5";
    } elseif ($text4) {
        return "$text4";
    } elseif ($text5) {
        return "$text5";
    } else {
        die("Error.");
    }
}

if (getenv('SKIP_CVS')) {
	fwrite(STDERR, "Skipping CVS\n");
} else {
	fwrite(STDERR, "Using CVS\n");
	
    if (getenv('FUNCTABLE_TAGS')) {
        $tags = explode(' ', getenv('FUNCTABLE_TAGS'));
    } else {
        $tags = get_release_tags();
    }
    
	fwrite(STDERR, "Tags: " . implode(' ', $tags) ."\n");
    
    foreach ($tags as $tag) {
		fwrite(STDERR, "Getting tag: $tag\n");
        checkout_tag($tag);
        $protos = parse_protos(PATH_TMP . DIRECTORY_SEPARATOR . 'php-src');
    
        $dbh = new PDO('sqlite:' . PATH_DB);
    
        $s = $dbh->prepare('DELETE FROM func_tag WHERE tag_name = ?');
        $s->bindParam(1, $tag);
        $s->execute();
    
        foreach ($protos as $p) {
            $s = $dbh->prepare("INSERT INTO "
                     . "func_tag (func_name, tag_name, has_proto, unicode_safe, source) "
                     . "VALUES (?, ?, 1, ?, 'php-src')");
            $s->bindParam(1, $p['func']);
            $s->bindParam(2, $tag);
            $s->bindParam(3, $p['unicode']);
            $s->execute();
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
	ORDER BY
		tag_name
");

$tq = $dbh->query("
	SELECT
		DISTINCT UPPER(tag_name) AS tag_name
	FROM
		func_tag
	WHERE
		tag_name LIKE 'PHP\\__\\__\\__' ESCAPE '\\'
	ORDER BY
		UPPER(tag_name)
");

foreach ($tq as $tr) {
	$tags[] = $tr['tag_name'];
	if ($s->execute(array($tr['tag_name']))) {
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
foreach ($tags as $t) {
	if (substr($t,0,5) == 'PHP_4') {
		$tags4[] = $t;
	} elseif (substr($t,0,5) == 'PHP_5') {
		$tags5[] = $t;
	} else {
		die('ERROR: '. $t);
	}
}

foreach ($funcs as $funcname => $func) {
	$text = htmlspecialchars(convert_array_to_words($func));
	echo " <function name='{$funcname}' from='{$text}'/>\n";
}
?>
</versions>

?>