<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2011 The PHP Group                                |
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
  |                                                                      |
  | Description: This file spell checks the manual. You must have the    |
  |              pspell extension installed for it to work.              |
  |                                                                      |
  | Personal Word List: http://erigol.com/php/custom.pws                 |
  |                     Put it in your home directory, ~                 |
  +----------------------------------------------------------------------+

  $Id$
*/

/* path to phpdoc CVS checkout. if this file is in the scripts/ directory
 * then the value below will be correct!
 */
$phpdoc = '../';

/* english! */
$lang = 'en';

/* Where to store information to fix later */
$file_fixlater = '/tmp/fix-me-later.txt';

/* (immediate) tags to check for spelling mistakes */
$check_tags = array(    'para',
                        'simpara',
                        'title',
                    );

$element = '';
$current_file = '';
$word_count = 0;

/* prompt a user and return the response */
function read_line($prompt) {
    echo $prompt;
    $in = chop(fgets(STDIN, 1024));
    return $in;
}

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

function set_current_element($xml, $name, $attrs)
{
    global $element;

    $element = strtolower($name);
}

function end_current_element($xml, $name)
{
    return;
}

/* spell check a chunk of data */
function check_data($xml, $data)
{
    global $element, $dict, $check_tags, $current_file, $word_count;

    if (!in_array($element, $check_tags))
        return;

    if (trim($data) == '')
        return;

    $words = preg_split('/\W+/', trim($data));
    if (is_array($words)) {
        foreach ($words as $word) {
            if (trim($word) == '' || is_numeric($word) || preg_match('/[^a-z]/', $word))
                continue;

            $word_count++;
            $word = strtolower($word);

            if (!pspell_check($dict, $word)) {
                /* known bug: due to trim()ing and whitespace removal, the
                 * line number shown here might not match the actual line
                 * number in the file, but it's usually pretty close
                 */
                $note = "$current_file:" . xml_get_current_line_number($xml) . ": $word   (in element $element)\n";
                echo $note;
                echo "================\nContext:\n$data\n================\n";
                do {
                    $response = read_line("Add this word to personal wordlist? (yes/no/save/later): ");
                    if ($response[0] == 's') {
                        pspell_save_wordlist($dict);
                        echo "Wordlist saved.\n";
                    }
                } while ($response[0] != 'y' && $response[0] != 'n' && $response[0] != 'l');

                if ($response[0] == 'y') {
                    pspell_add_to_personal($dict, $word);
                    echo "Added '$word' to personal wordlist.\n";
                }
                if ($response[0] == 'l') {
                    file_put_contents('/tmp/fix-me-later.txt', $note, FILE_APPEND); 
                    echo "You will deal with '$word' later.\n";
                }
            }
        }
    }

    return;
}

function check_file($filename)
{
    global $phpdoc, $lang, $current_file;

    if (!fnmatch('*.xml', $filename) || fnmatch("$phpdoc$lang/functions/*", $filename))
        return;

    echo "checking $filename...\n";
    $current_file = $filename;

    $file = file_get_contents($filename);

    if (!$file)
        return;

    $file = preg_replace('/&(.*?);/', '', $file);
    if (trim($file) == '')
        return;

    $xml = xml_parser_create();
    xml_set_element_handler($xml, 'set_current_element', 'end_current_element');
    xml_set_character_data_handler($xml, 'check_data');

    if (!xml_parse($xml, $file, true)) {
        printf("%s: XML error: %s at line %d\n",
            $filename,
            xml_error_string(xml_get_error_code($xml)),
            xml_get_current_line_number($xml)
        );
    }

    xml_parser_free($xml);
}

if (!function_exists('pspell_new_personal')) {
    echo "You must compile PHP with pspell support for this script to work.\n";
    return;
}

$dict = pspell_new_personal('custom.pws', 'en');

globbetyglob("$phpdoc$lang", 'check_file');
pspell_save_wordlist($dict);
echo "Wordlist saved.\n";
echo "Processed $word_count words.\n";
?>
