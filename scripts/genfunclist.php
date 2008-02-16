<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2008 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Ariel Shkedi <ars@ziplink.net> or <as@altavista.net>     |
  |             Rasmus Lerdorf <rasmus@lerdorf.on.ca>                    |
  |                                                                      |
  |             anatoly techtonik <techtonik@users.sourceforge.net>      |
  +----------------------------------------------------------------------+

  $Id$
*/

/**
 * Builds function list from PHP sources
 *
 * Finds all source files, which contain Zend Function Block and extracts
 * information from them to build a list. Replacement for both funcparse.awk
 * and genfunclist.sh
 *
 * Known limitations:
 * - can't handle multiline comments and precompiler directives
 *
 * TODO: 
 * - add PHPUnit regression tests
 */

/**
 * include function block headers in output
 */
$show_block_names = true;
/**
 * sort function names (disable this to output directive and comments stuff)
 */
$sort_names = true;
/**
 * if sorting disabled, output directives
 */
$show_directives = true;
/**
 * if sorting disabled, output comments and other stuff
 */
$show_stuff = true;

if ($argc != 2 ||
    in_array($argv[1], array('--help', '-help', '-h', '-?')) ||
    !is_dir($argv[1])) {

    echo "Builds function list from PHP sources\n\n";
    echo "Usage:\n";
    echo "      $argv[0] <php source dir>\n\n";
    echo "      --help, -help, -h, -?\n";
    echo "          to get this help\n";

} else {

    // check for PHP3 sources
    $PHP3_lex = is_file($argv[1]."/language-scanner.lex")  // only in PHP3 sources
                ? $argv[1]."/language-scanner.lex"
                : NULL;

    // find all source files recursively - returns array with filenames
    function get_parsefiles($srcpath) {
       $parsefiles = array();
       $srcdir = dir($srcpath);
       while (false !== ($file = $srcdir->read())) {
           $filepath = $srcpath."/".$file;
           if (is_dir($filepath) && $file !== "." && $file !== "..") {
               $parsefiles = array_merge($parsefiles, get_parsefiles($filepath));
               continue;
           }
           if (preg_match('/\.(c|cpp|h|ec)$/i', $file)) {
               $parsefiles[] = $filepath;
           }
       }
       $srcdir->close();
       return $parsefiles;
    }

    $parsefiles = get_parsefiles($argv[1]);

    // make unified directory separator - /
    if (DIRECTORY_SEPARATOR == '\\') {
        $parsefiles = array_map( create_function('$a', 'return str_replace("\\\\", "/", $a);'), $parsefiles );
    }

    //$FB_exclude = "_class_functions";
    $ZendFB_regex  = "`^[ \t]*(?:(?:static|const))?[ \t]*(?:zend_)?function_entry\s*(?!php_hw_api_)\w+(?<!_class_functions)\s*\[\]\s*=\s*\{(.*)(?:\{\s*NULL\s*,\s*NULL\s*,\s*NULL\s*\}|\{0\})`msU";

    // function blocks
    $blocks = array();
    $block_titles = array();
    foreach ($parsefiles as $key => $file) {
        $file_contents = file_get_contents($file);
        if (strpos($file_contents, 'function_entry') === FALSE) {
            unset($parsefiles[$key]);
        } else {
            $m = preg_match_all($ZendFB_regex, $file_contents, $matches);
            if ($m) {
                foreach ($matches[0] as $mk => $mv) {
                    $block_titles[$key][$mk] = strtok($mv, "\n");
                }
                $blocks[$key] = $matches[1];
            } else {
                unset($parsefiles[$key]);
            }
        }
    }

    array_multisort($parsefiles, $blocks, $block_titles);
//    print_r($parsefiles);
//    print_r($block_titles);

    $macronames = "ZEND_FE|ZEND_FALIAS|PHP_FE|PHP_FALIAS|ZEND_NAMED_FE|PHP_NAMED_FE|PHP_STATIC_FE|PHP_HASH_FE";
    if ($PHP3_lex) $macronames .= "|UODBC_FE(?:_ALIAS)?";
    $FB_instance_regex = "`^[ \t]*(?:($macronames)\s*\(|\{)\s*\"?(\w+)`im";

    foreach ($parsefiles as $key => $file) {
        // output source file name
        echo preg_replace("|^[./]+|", "# ", $file)."\n";
        foreach ($blocks[$key] as $bk => $bv) {
            // output function block title
            if ($show_block_names) {
                echo "# ".str_replace('/* {{{ */', '', $block_titles[$key][$bk])."\n";
            }
            $resultecho = array();
            $tok = strtok($bv, "\n");
            while ($tok) {
                if (preg_match($FB_instance_regex, $tok, $matches)) {
                    if ($PHP3_lex && ereg("^UODBC_FE",$matches[1]))
                        $matches[2] = "odbc_".$matches[2];
                    // output function name
                    if (!$sort_names):
                        echo $matches[2]."\n";
                    else:
                        $resultecho[] = $matches[2]."\n";
                    endif;
                } elseif (!$sort_names) {
                    // to use this turn off sorting
                    // output stuff
                    if ($show_stuff && $tok{0} != '#') echo $tok."\n";
                    // output compiler directives
                    if ($show_directives && $tok{0} == '#') echo $tok."\n";
                }
                $tok = strtok("\n");
            }
            if ($sort_names) {
                sort($resultecho);
                echo implode("", $resultecho);
            }
        }
        echo "\n";
    }

    if ( $PHP3_lex ) {
        echo preg_replace("|^[./]+|", "# ", $PHP3_lex)."\n";

        // worst case <IN_PHP>"highlight_file"|"show_source" {
        $LEX_regex = "|<IN_PHP>\"([\w\d_]+)\"(?:\|\"([\w\d_]+)\")? \{|";
        preg_match_all($LEX_regex, file_get_contents($PHP3_lex), $matches);

        $names = $matches[2][0] ? array_merge($matches[1], $matches[2]) : $matches[1];

        sort($names);
        echo implode("\n",$names);
    }
}

/****[ Original genfunclist.sh ] *****/

# | Authors:    Ariel Shkedi <ars@ziplink.net> or <as@altavista.net>     |
# |             Rasmus Lerdorf <rasmus@lerdorf.on.ca>                    |

/*
for i in `find $1 -name "*.[c]" -print -o -name "*.ec" -print | xargs egrep -li function_entry | sort` ; do
 echo $i | sed -e "s|$1|# php-src|"
 if test -f funcparse.awk ; then
  awk -f funcparse.awk < $i | sort
 elif test -f scripts/funcparse.awk; then
  awk -f scripts/funcparse.awk < $i | sort
 else
  echo 1>&2 funcparse.awk not found
  exit
 fi
done
if test -f $1/language-scanner.lex # only in PHP3 sources
then
 echo $1/language-scanner.lex | sed -e 's/\.\.\//# /'
 awk -f funcparse.awk < $1/language-scanner.lex | sort
fi
*/

/****[ Original funcparse.awk ] *****/
/*
BEGIN { parse=0; FS="[\"(,]"; }
/^.*function_entry.*$/ { parse=1; }
/^.*function_entry.*_class_functions.*$/ { parse=0; }
/^.*function_entry.*OrbitStruct.*$/ { parse=0; }
/^.*function_entry.*OrbitObject.*$/ { parse=0; }
/^.*shutdown_function_entry.*$/ { parse=0; }
/^.*\(function_entry.*$/ { parse=0; }
/^.*function_entry \*ptr.*$/ { parse=0; }
/^.*\(zend_function_entry.*$/ { parse=0; }
/^.*zend_function_entry \*ptr.*$/ { parse=0; }
/NULL.*?NULL.*?NULL/ { parse=0; }
/^[[:space:]]{0}/ { parse=0; }
/^[[:space:]]*{/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_FE/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_FALIAS/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_NAMED_FE/ { if(parse) { print $2; } }
/^[[:space:]]*PHP_STATIC_FE/ { if(parse) { print $3; } }
/^[[:space:]]*ZEND_FE/ { if(parse) { print $2; } }
/^[[:space:]]*ZEND_FALIAS/ { if(parse) { print $2; } }
/^[[:space:]]*ZEND_NAMED_FE/ { if(parse) { print $2; } }
/^[[:space:]]*cybercash_functions/ { if (parse) { print $2; } }
/^[[:space:]]*UODBC_FE/ { if(parse) { print "odbc_"$2; } }
/^<IN_PHP>/ { if(match($2,"^[A-Za-z0-9_]+$")) print $2; }
*/

?>
