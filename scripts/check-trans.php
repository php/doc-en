#!/usr/bin/php -q
<?php

error_reporting(E_ALL);

/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
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
  | Authors : Yannick Torrès <yannick@php.net>                             |
  +----------------------------------------------------------------------+
*/

if ($argc != 2) {
?>

Check for translation error and print statistics

You can see an example output for french translation here :
http://php.keliglia.com/index_error.htm

Feel free to ask me some others checks.

  Usage:
  <?php echo $argv[0]; ?> <language-code>

  Authors:Yannick Torrès <yannick@php.net>

<?php
  exit;
}

// Long runtime
set_time_limit(0);

// Initializing variable from parameter
$LANG = $argv[1];

// Main directory of the PHP documentation (depends on the
// sapi used). We do need the trailing slash!
if ("cli" === php_sapi_name()) {
   if (isset($PHPDOCDIR) && is_dir($PHPDOCDIR)) { $DOCDIR = $PHPDOCDIR."/"; }
   else { $DOCDIR = "./"; }
} else { $DOCDIR = "../"; }

// =========================================================================
// Functions to get revision info and credits from a file
// =========================================================================

$final_result = array();

$nb_error['chapter'] = 0;
$nb_error['appendix'] = 0;
$nb_error['qandaentry'] = 0;
$nb_error['link'] = 0;
$nb_error['sect1'] = 0;
$nb_error['book'] = 0;
$nb_error['preface'] = 0;
$nb_error['section'] = 0;
$nb_error['varlistentry'] = 0;
$nb_error['reference'] = 0;
$nb_error['refentry'] = 0;
$nb_error['refsect1'] = 0;
$nb_error['methodsynopsis'] = 0;
$nb_error['refpurpose'] = 0;
$nb_error['seealsoMember'] = 0;
$nb_error['cdata'] = 0;
$nb_error['classsynopsis'] = 0;
$nb_error['table'] = 0;
$nb_error['para'] = 0;


function do_check($en_content, $lang_content) {
global $LANG;
global $nb_error;

$result_error = array();

// We remove comment in EN and lang content
$en_content = preg_replace("/<!--(.*?)?-->/s", "", $en_content);
$lang_content = preg_replace("/<!--(.*?)?-->/s", "", $lang_content);

$retour = false;

// chapter

$en_chapter = array();
preg_match_all("/<chapter(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)xmlns:xlink=\"(.*?)\">/s", $en_content, $match);
$en_chapter["xmlid"] = $match[2];
$en_chapter["xmlns"] = $match[4];
$en_chapter["xmlnsxlink"] = $match[6];

$lang_chapter = array();
preg_match_all("/<chapter(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)xmlns:xlink=\"(.*?)\">/s", $lang_content, $match);
$lang_chapter["xmlid"] = $match[2];
$lang_chapter["xmlns"] = $match[4];
$lang_chapter["xmlnsxlink"] = $match[6];

for( $i=0; $i < count($en_chapter["xmlid"]); $i ++ ) {
 if( $en_chapter["xmlid"][$i] != $lang_chapter["xmlid"][$i] ) {
  $nb_error['chapter'] ++;

  $result_error[] = array(
   "libel" => "Error in chapter, attribut : xmlid",
   "value_en" => $en_chapter["xmlid"][$i],
   "value_lang" => $lang_chapter["xmlid"][$i]
  );

 }
}

for( $i=0; $i < count($en_chapter["xmlns"]); $i ++ ) {
 if( $en_chapter["xmlns"][$i] != $lang_chapter["xmlns"][$i] ) {
  $nb_error['chapter'] ++;
  $result_error[] = array(
   "libel" => "Error in chapter, attribut : xmlns",
   "value_en" => $en_chapter["xmlns"][$i],
   "value_lang" => $lang_chapter["xmlns"][$i]
  );
 }
}

for( $i=0; $i < count($en_chapter["xmlnsxlink"]); $i ++ ) {
 if( $en_chapter["xmlnsxlink"][$i] != $lang_chapter["xmlnsxlink"][$i] ) {
  $nb_error['chapter'] ++;
  $result_error[] = array(
   "libel" => "Error in chapter, attribut : xmlns:xlink",
   "value_en" => $en_chapter["xmlnsxlink"][$i],
   "value_lang" => $lang_chapter["xmlnsxlink"][$i]
  );
 }
}

// END : chapter

// appendix

$en_appendix = array();
preg_match_all("/<appendix(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)xmlns:xlink=\"(.*?)\">/s", $en_content, $match);
$en_appendix["xmlid"] = $match[2];
$en_appendix["xmlns"] = $match[4];
$en_appendix["xmlnsxlink"] = $match[6];

$lang_appendix = array();
preg_match_all("/<appendix(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)xmlns:xlink=\"(.*?)\">/s", $lang_content, $match);
$lang_appendix["xmlid"] = $match[2];
$lang_appendix["xmlns"] = $match[4];
$lang_appendix["xmlnsxlink"] = $match[6];

for( $i=0; $i < count($en_appendix["xmlid"]); $i ++ ) {
 if( $en_appendix["xmlid"][$i] != $lang_appendix["xmlid"][$i] ) {
  $nb_error['appendix'] ++;
  $result_error[] = array(
   "libel" => "Error in appendix, attribut : xmlid",
   "value_en" => $en_appendix["xmlid"][$i],
   "value_lang" => $lang_appendix["xmlid"][$i]
  );

 }
}

for( $i=0; $i < count($en_appendix["xmlns"]); $i ++ ) {
 if( $en_appendix["xmlns"][$i] != $lang_appendix["xmlns"][$i] ) {
  $nb_error['appendix'] ++;
  $result_error[] = array(
   "libel" => "Error in appendix, attribut : xmlns",
   "value_en" => $en_appendix["xmlns"][$i],
   "value_lang" => $lang_appendix["xmlns"][$i]
  );

 }
}

for( $i=0; $i < count($en_appendix["xmlnsxlink"]); $i ++ ) {
 if( $en_appendix["xmlnsxlink"][$i] != $lang_appendix["xmlnsxlink"][$i] ) {
  $nb_error['appendix'] ++;
  $result_error[] = array(
   "libel" => "Error in appendix, attribut : xmlns:xlink",
   "value_en" => $en_appendix["xmlnsxlink"][$i],
   "value_lang" => $lang_appendix["xmlnsxlink"][$i]
  );

 }
}
// END : appendix

// qandaentry

$en_qandaentry = array();
preg_match_all("/<qandaentry(\s.*?)xml:id=\"(.*?)\">/s", $en_content, $match);
$en_qandaentry = $match[2];

$lang_qandaentry = array();
preg_match_all("/<qandaentry(\s.*?)xml:id=\"(.*?)\">/s", $lang_content, $match);
$lang_qandaentry = $match[2];

for( $i=0; $i < count($en_qandaentry); $i ++ ) {
 if( $en_qandaentry[$i] != $lang_qandaentry[$i] ) {
  $nb_error['qandaentry'] ++;
  $result_error[] = array(
   "libel" => "Error in qandaentry, attribut : xml:id",
   "value_en" => $en_qandaentry[$i],
   "value_lang" => $lang_qandaentry[$i]
  );

 }
}

// END : qandaentry

// linkend

$en_linkend = array();
preg_match_all("/<link(\s.*?)linkend=(\"|')(.*?)(\"|')(\s.*)?(\/)?>/s", $en_content, $match);
$en_linkend = $match[3];

$lang_linkend = array();
preg_match_all("/<link(\s.*?)linkend=(\"|')(.*?)(\"|')(\s.*)?(\/)?>/s", $lang_content, $match);
$lang_linkend = $match[3];

for( $i=0; $i < count($en_linkend); $i ++ ) {
 if( isset($lang_linkend[$i]) && $en_linkend[$i] != $lang_linkend[$i]  && $en_linkend[$i] != 'somethingelse' ) {
  $nb_error['link'] ++;
  $result_error[] = array(
   "libel" => "Error in link, attribut : linkend n°$i",
   "value_en" => $en_linkend[$i],
   "value_lang" => $lang_linkend[$i]
  );

 }
}
// END : linkend


// link:href

$en_linkhref = array();
preg_match_all("/<link(\s.*?)xlink:href=\"(.*?)\">/s", $en_content, $match);
$en_linkhref = $match[2];

$lang_linkhref = array();
preg_match_all("/<link(\s.*?)xlink:href=\"(.*?)\">/s", $lang_content, $match);
$lang_linkhref = $match[2];

for( $i=0; $i < count($en_linkhref); $i ++ ) {
 if( isset($lang_linkhref[$i]) && $en_linkhref[$i] != $lang_linkhref[$i] ) {
  $nb_error['link'] ++;
  $result_error[] = array(
   "libel" => "Error in link, attribut : linkend",
   "value_en" => $en_linkhref[$i],
   "value_lang" => $lang_linkhref[$i]
  );

 }
}
// END : link:href


// sect1

$en_sect1 = array();
preg_match_all("/<sect1(\s.*?)xml:id=\"(.*?)\"(\s.*?)?>/s", $en_content, $match);
$en_sect1 = $match[2];

$lang_sect1 = array();
preg_match_all("/<sect1(\s.*?)xml:id=\"(.*?)\"(\s.*?)?>/s", $lang_content, $match);
$lang_sect1 = $match[2];

for( $i=0; $i < count($en_sect1); $i ++ ) {
 if( isset($lang_sect1[$i]) && $en_sect1[$i] != $lang_sect1[$i] ) {
  $nb_error['sect1'] ++;
  $result_error[] = array(
   "libel" => "Error in sect1, attribut : xml:id",
   "value_en" => $en_sect1[$i],
   "value_lang" => $lang_sect1[$i]
  );

 }
}
// END : sect1



// book

$en_book = array();
preg_match_all("/<book(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)xmlns:xlink=\"(.*?)\">/s", $en_content, $match);
$en_book["xmlid"] = $match[2];
$en_book["xmlns"] = $match[4];
$en_book["xmlnsxlink"] = $match[6];

$lang_book = array();
preg_match_all("/<book(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)xmlns:xlink=\"(.*?)\">/s", $lang_content, $match);
$lang_book["xmlid"] = $match[2];
$lang_book["xmlns"] = $match[4];
$lang_book["xmlnsxlink"] = $match[6];

for( $i=0; $i < count($en_book["xmlid"]); $i ++ ) {
 if( $en_book["xmlid"][$i] != $lang_book["xmlid"][$i] ) {
  $nb_error['book'] ++;
  $result_error[] = array(
   "libel" => "Error in book, attribut : xmlid",
   "value_en" => $en_book["xmlid"][$i],
   "value_lang" => $lang_book["xmlid"][$i]
  );

 }
}

for( $i=0; $i < count($en_book["xmlns"]); $i ++ ) {
 if( $en_book["xmlns"][$i] != $lang_book["xmlns"][$i] ) {
  $nb_error['book'] ++;
  $result_error[] = array(
   "libel" => "Error in book, attribut : xmlns",
   "value_en" => $en_book["xmlns"][$i],
   "value_lang" => $lang_book["xmlns"][$i]
  );

 }
}

for( $i=0; $i < count($en_book["xmlnsxlink"]); $i ++ ) {
 if( $en_book["xmlnsxlink"][$i] != $lang_book["xmlnsxlink"][$i] ) {
  $nb_error['book'] ++;
  $result_error[] = array(
   "libel" => "Error in book, attribut : xmlns:xlink",
   "value_en" => $en_book["xmlnsxlink"][$i],
   "value_lang" => $lang_book["xmlnsxlink"][$i]
  );
 }
}

// END : book


// preface

$en_preface = array();
preg_match_all("/<preface(\s.*?)xml:id=\"(.*?)\"(\s.*?)?>/s", $en_content, $match);
$en_preface = $match[2];

$lang_preface = array();
preg_match_all("/<preface(\s.*?)xml:id=\"(.*?)\"(\s.*?)?>/s", $lang_content, $match);
$lang_preface = $match[2];

for( $i=0; $i < count($en_preface); $i ++ ) {
 if( $en_preface[$i] != $lang_preface[$i] ) {
  $nb_error['preface'] ++;
  $result_error[] = array(
   "libel" => "Error in preface, attribut : xml:id",
   "value_en" => $en_preface[$i],
   "value_lang" => $lang_preface[$i]
  );

 }
}
// END : preface

// section

$en_section = array();
preg_match_all("/<section(\s.*?)xml:id=(\"|')(.*?)(\"|')(\s.*?)?>/s", $en_content, $match);
$en_section["xmlid"] = $match[3];

$lang_section = array();
preg_match_all("/<section(\s.*?)xml:id=(\"|')(.*?)(\"|')(\s.*?)?>/s", $lang_content, $match);
$lang_section["xmlid"] = $match[3];

for( $i=0; $i < count($en_section["xmlid"]); $i ++ ) {
 if( $en_section["xmlid"][$i] != $lang_section["xmlid"][$i] ) {
  $nb_error['section'] ++;
  $result_error[] = array(
   "libel" => "Error in section, attribut : xmlid",
   "value_en" => $en_section["xmlid"][$i],
   "value_lang" => $lang_section["xmlid"][$i]
  );

 }
}


// END : section



// varlistentry

$en_varlistentry = array();
preg_match_all("/<varlistentry(\s.*?)xml:id=\"(.*?)\"(\s.*?)?>/s", $en_content, $match);
$en_varlistentry = $match[2];

$lang_varlistentry = array();
preg_match_all("/<varlistentry(\s.*?)xml:id=\"(.*?)\"(\s.*?)?>/s", $lang_content, $match);
$lang_varlistentry = $match[2];

for( $i=0; $i < count($en_varlistentry); $i ++ ) {
 if( $en_varlistentry[$i] != $lang_varlistentry[$i] ) {
  $nb_error['varlistentry'] ++;
  $result_error[] = array(
   "libel" => "Error in varlistentry, attribut : xml:id",
   "value_en" => $en_varlistentry[$i],
   "value_lang" => $lang_varlistentry[$i]
  );
 }
}
// END : varlistentry

// reference

$en_reference = array();
preg_match_all("/<reference(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)?>/s", $en_content, $match);
$en_reference["xmlid"] = $match[2];
$en_reference["xmlns"] = $match[4];

$lang_reference = array();
preg_match_all("/<reference(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)?>/s", $lang_content, $match);
$lang_reference["xmlid"] = $match[2];
$lang_reference["xmlns"] = $match[4];

for( $i=0; $i < count($en_reference["xmlid"]); $i ++ ) {
 if( $en_reference["xmlid"][$i] != $lang_reference["xmlid"][$i] ) {
  $nb_error['reference'] ++;
  $result_error[] = array(
   "libel" => "Error in reference, attribut : xmlid",
   "value_en" => $en_reference["xmlid"][$i],
   "value_lang" => $lang_reference["xmlid"][$i]
  );

 }
}

for( $i=0; $i < count($en_reference["xmlns"]); $i ++ ) {
 if( $en_reference["xmlns"][$i] != $lang_reference["xmlns"][$i] ) {
  $nb_error['reference'] ++;
  $result_error[] = array(
   "libel" => "Error in reference, attribut : xmlns",
   "value_en" => $en_reference["xmlns"][$i],
   "value_lang" => $lang_reference["xmlns"][$i]
  );
 }
}

// END : reference

// refentry

$en_refentry = array();
preg_match_all("/<refentry(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)?>/s", $en_content, $match);
$en_refentry["xmlid"] = $match[2];
$en_refentry["xmlns"] = $match[4];

$lang_refentry = array();
preg_match_all("/<refentry(\s.*?)xml:id=\"(.*?)\"(\s.*?)xmlns=\"(.*?)\"(\s.*?)?>/s", $lang_content, $match);
$lang_refentry["xmlid"] = $match[2];
$lang_refentry["xmlns"] = $match[4];

for( $i=0; $i < count($en_refentry["xmlid"]); $i ++ ) {
 if( $en_refentry["xmlid"][$i] != $lang_refentry["xmlid"][$i] ) {
  $nb_error['refentry'] ++;
  $result_error[] = array(
   "libel" => "Error in refentry, attribut : xmlid",
   "value_en" => $en_refentry["xmlid"][$i],
   "value_lang" => $lang_refentry["xmlid"][$i]
  );
 }
}

for( $i=0; $i < count($en_refentry["xmlns"]); $i ++ ) {
 if( $en_refentry["xmlns"][$i] != $lang_refentry["xmlns"][$i] ) {
  $nb_error['refentry'] ++;
  $result_error[] = array(
   "libel" => "Error in refentry, attribut : xmlns",
   "value_en" => $en_refentry["xmlns"][$i],
   "value_lang" => $lang_refentry["xmlns"][$i]
  );

 }
}


// END : refentry


// refsect1

$en_refsect1 = array();
preg_match_all("/<refsect1(\s.*?)role=\"(.*?)\"(\s.*?)?>/s", $en_content, $match);
$en_refsect1 = $match[2];

$lang_refsect1 = array();
preg_match_all("/<refsect1(\s.*?)role=\"(.*?)\"(\s.*?)?>/s", $lang_content, $match);
$lang_refsect1 = $match[2];

for( $i=0; $i < count($en_refsect1); $i ++ ) {
 if( isset($lang_refsect1[$i]) && $en_refsect1[$i] != $lang_refsect1[$i] ) {
  $nb_error['refsect1'] ++;
  $result_error[] = array(
   "libel" => "Error in refsect1, attribut : xml:id",
   "value_en" => $en_refsect1[$i],
   "value_lang" => $lang_refsect1[$i]
  );
 }
}
// END : refsect1



// methodsynopsis

$en_methodsynopsis = array();
preg_match_all("/<methodsynopsis>(\s.*?)<\/methodsynopsis>/s", $en_content, $match);

for( $i = 0; $i < count($match[1]); $i++) {

preg_match_all("/<type>(.*?)<\/type><methodname>(.*?)<\/methodname>/s", $match[1][$i], $match2);


if( isset($match2[2][0]) && isset($match2[1][0]) ) {

$en_methodsynopsis[$i]['methodname']['name'] = $match2[2][0];
$en_methodsynopsis[$i]['methodname']['type'] = $match2[1][0];

preg_match_all("/<methodparam(( choice=\"opt\")|( choice='opt'))?><type>(.*?)<\/type><parameter(( role=\"reference\")|( role='reference'))?>(.*?)<\/parameter><\/methodparam>/s", $match[1][$i], $match2);


$en_methodsynopsis[$i]['methodparam']['parameter'] = $match2[8];
$en_methodsynopsis[$i]['methodparam']['type']      = $match2[4];

for( $j=0; $j < count($match2[1]); $j++) {

 if( trim($match2[1][$j]) == 'choice="opt"' || trim($match2[1][$j]) == "choice='opt'" ) { $en_methodsynopsis[$i]['methodparam']['optionnel'][$j] = 1; }
 else { $en_methodsynopsis[$i]['methodparam']['optionnel'][$j] = 0; }

}

for( $j=0; $j < count($match2[5]); $j++) {

 if( trim($match2[5][$j]) == 'role="reference"' || trim($match2[5][$j]) == "role='reference'" ) { $en_methodsynopsis[$i]['methodparam']['role'][$j] = 1; }
 else { $en_methodsynopsis[$i]['methodparam']['role'][$j] = 0; }

}


}

}

$lang_methodsynopsis = array();
preg_match_all("/<methodsynopsis>(\s.*?)<\/methodsynopsis>/s", $lang_content, $match);

for( $i = 0; $i < count($match[1]); $i++) {

preg_match_all("/<type>(.*?)<\/type><methodname>(.*?)<\/methodname>/s", $match[1][$i], $match2);

if( isset($match2[2][0]) && isset($match2[1][0]) ) {

$lang_methodsynopsis[$i]['methodname']['name'] = $match2[2][0];
$lang_methodsynopsis[$i]['methodname']['type'] = $match2[1][0];

preg_match_all("/<methodparam(( choice=\"opt\")|( choice='opt'))?><type>(.*?)<\/type><parameter(( role=\"reference\")|( role='reference'))?>(.*?)<\/parameter><\/methodparam>/s", $match[1][$i], $match2);

$lang_methodsynopsis[$i]['methodparam']['parameter'] = $match2[8];
$lang_methodsynopsis[$i]['methodparam']['type']      = $match2[4];

for( $j=0; $j < count($match2[1]); $j++) {

 if( trim($match2[1][$j]) == 'choice="opt"' || trim($match2[1][$j]) == "choice='opt'" ) { $lang_methodsynopsis[$i]['methodparam']['optionnel'][$j] = 1; }
 else { $lang_methodsynopsis[$i]['methodparam']['optionnel'][$j] = 0; }

}


for( $j=0; $j < count($match2[5]); $j++) {

 if( trim($match2[5][$j]) == 'role="reference"' ) { $lang_methodsynopsis[$i]['methodparam']['role'][$j] = 1; }
 else { $lang_methodsynopsis[$i]['methodparam']['role'][$j] = 0; }

}

}

}


// Vérif
for( $i=0; $i < count($en_methodsynopsis); $i++) {

 if( isset($en_methodsynopsis[$i]['methodname']['name']) ) {

  if( !isset($lang_methodsynopsis[$i]['methodname']['name']) ) { $lang_methodsynopsis[$i]['methodname']['name'] = ''; }

  // methodname
  if( $en_methodsynopsis[$i]['methodname']['name'] != $lang_methodsynopsis[$i]['methodname']['name'] ) {
   $nb_error['methodsynopsis'] ++;
   $result_error[] = array(
    "libel" => "Error in methodsynopsis, methodname :",
    "value_en" => $en_methodsynopsis[$i]['methodname']['name'],
    "value_lang" => $lang_methodsynopsis[$i]['methodname']['name']
   );

  }

 }

 if( isset($en_methodsynopsis[$i]['methodname']['type']) ) {

   if( !isset($lang_methodsynopsis[$i]['methodname']['type']) ) { $lang_methodsynopsis[$i]['methodname']['type'] = ''; }

   if( $en_methodsynopsis[$i]['methodname']['type'] != $lang_methodsynopsis[$i]['methodname']['type'] ) {
    $nb_error['methodsynopsis'] ++;
    $result_error[] = array(
     "libel" => "Error in methodsynopsis, methodname, type :",
     "value_en" => $en_methodsynopsis[$i]['methodname']['type'],
     "value_lang" => $lang_methodsynopsis[$i]['methodname']['type']
    );

   }
 }

 if( isset($en_methodsynopsis[$i]['methodparam']['parameter']) ) {

 // methodparam
 if( !isset($en_methodsynopsis[$i]['methodparam']['parameter']) ) { $en_methodsynopsis[$i]['methodparam']['parameter'] = array(); }

 if( !isset($lang_methodsynopsis[$i]['methodparam']['parameter']) ) { $lang_methodsynopsis[$i]['methodparam']['parameter'] = array(); }

 if( count($en_methodsynopsis[$i]['methodparam']['parameter']) != count($lang_methodsynopsis[$i]['methodparam']['parameter']) ) {

    $nb_error['methodsynopsis'] ++;
    $result_error[] = array(
     "libel" => "Error : There is ".count($en_methodsynopsis[$i]['methodparam']['parameter'])." methodparam(s) in EN, ".count($lang_methodsynopsis[$i]['methodparam']['parameter'])." in $LANG",
     "value_en" => "N/A",
     "value_lang" => "N/A"
    );

 }


 for( $j = 0; $j < count($en_methodsynopsis[$i]['methodparam']['parameter']); $j++ ) {

   if( isset($en_methodsynopsis[$i]['methodparam']['parameter'][$j]) ) {

     if( !isset($lang_methodsynopsis[$i]['methodparam']['parameter'][$j]) ) { $lang_methodsynopsis[$i]['methodparam']['parameter'][$j] = ''; }

     // parameter name
     if( $en_methodsynopsis[$i]['methodparam']['parameter'][$j] != $lang_methodsynopsis[$i]['methodparam']['parameter'][$j] ) {
       $nb_error['methodsynopsis'] ++;
       $result_error[] = array(
        "libel" => "Error in methodsynopsis, methodparam, parameter :",
        "value_en" => $en_methodsynopsis[$i]['methodparam']['parameter'][$j],
        "value_lang" => $lang_methodsynopsis[$i]['methodparam']['parameter'][$j]
       );

     }

   }

   if( isset($en_methodsynopsis[$i]['methodparam']['type'][$j]) ) {

     if( !isset($lang_methodsynopsis[$i]['methodparam']['type'][$j]) ) { $lang_methodsynopsis[$i]['methodparam']['type'][$j] = ''; }

     // parameter type
     if( $en_methodsynopsis[$i]['methodparam']['type'][$j] != $lang_methodsynopsis[$i]['methodparam']['type'][$j] ) {
       $nb_error['methodsynopsis'] ++;
       $result_error[] = array(
        "libel" => "Error in methodsynopsis, methodparam, type :",
        "value_en" => $en_methodsynopsis[$i]['methodparam']['type'][$j],
        "value_lang" => $lang_methodsynopsis[$i]['methodparam']['type'][$j]
       );

     }

    }

    if( isset($en_methodsynopsis[$i]['methodparam']['optionnel'][$j]) ) {

     if( !isset($lang_methodsynopsis[$i]['methodparam']['optionnel'][$j]) ) { $lang_methodsynopsis[$i]['methodparam']['optionnel'][$j] = ''; }

     // is optionnel ?
     if( $en_methodsynopsis[$i]['methodparam']['optionnel'][$j] != $lang_methodsynopsis[$i]['methodparam']['optionnel'][$j] ) {

      $tmp1 = ($en_methodsynopsis[$i]['methodparam']['optionnel'][$j] == 0)? $en_methodsynopsis[$i]['methodparam']['parameter'][$j]." <strong>ISN'T</strong> optionnel":$en_methodsynopsis[$i]['methodparam']['parameter'][$j]." <strong>IS</strong> optionnel (choice=\"opt\")";
      $tmp2 = ($lang_methodsynopsis[$i]['methodparam']['optionnel'][$j] == 0)? $lang_methodsynopsis[$i]['methodparam']['parameter'][$j]." <strong>ISN'T</strong> optionnel":$lang_methodsynopsis[$i]['methodparam']['parameter'][$j]." <strong>IS</strong> optionnel (choice=\"opt\")";
       $nb_error['methodsynopsis'] ++;
       $result_error[] = array(
        "libel" => "Error in methodsynopsis, methodparam, is optionnel ?",
        "value_en" => $tmp1,
        "value_lang" => $tmp2
       );

     }

    }

   if( isset($en_methodsynopsis[$i]['methodparam']['role'][$j]) ) {

     if( !isset($lang_methodsynopsis[$i]['methodparam']['role'][$j]) ) { $lang_methodsynopsis[$i]['methodparam']['role'][$j] = ''; }

     // is reference ?
     if( $en_methodsynopsis[$i]['methodparam']['role'][$j] != $lang_methodsynopsis[$i]['methodparam']['role'][$j] ) {

      $tmp1 = ($en_methodsynopsis[$i]['methodparam']['role'][$j] == 0)? $en_methodsynopsis[$i]['methodparam']['parameter'][$j]." <strong>ISN'T</strong> reference":$en_methodsynopsis[$i]['methodparam']['parameter'][$j]." <strong>IS</strong> reference (role=\"reference\")";
      $tmp2 = ($lang_methodsynopsis[$i]['methodparam']['role'][$j] == 0)? $lang_methodsynopsis[$i]['methodparam']['parameter'][$j]." <strong>ISN'T</strong> reference":$lang_methodsynopsis[$i]['methodparam']['parameter'][$j]." <strong>IS</strong> reference (role=\"reference\")";
       $nb_error['methodsynopsis'] ++;
       $result_error[] = array(
        "libel" => "Error in methodsynopsis, methodparam, parameter, attribut : role ?",
        "value_en" => $tmp1,
        "value_lang" => $tmp2
       );

     }

   }
 }
 }
}

// END : methodsynopsis

// Check if tere is a space or a point at the end of refpurpose

preg_match_all("/<refpurpose>.*([^A-Za-z1-9 ])<\/refpurpose>/s", $lang_content, $match2);

if( isset($match2[1][0]) ) {

  if($match2[1][0] == '.')  {
       $nb_error['refpurpose'] ++;
       $result_error[] = array(
        "libel" => "WARNING in refpurpose : there is a point at the end of refpurpose.",
        "value_en" => "N/A",
        "value_lang" => "N/A"
       );
  }

}

// END : space or point at the end of refpurpose


// SeeAlso section : check member

$en_seeAlsoMember = 0;
preg_match("/<refsect1 role=\"seealso\">(.*)<\/refsect1>/s", $en_content, $match2);

if (isset($match2[1])) {
    preg_match_all("/<member>(.*?)<\/member>/s", $match2[1], $match3);
    if (isset($match3[1])) {
        $en_seeAlsoMember = count($match3[1]);
    }
}

$lang_seeAlsoMember = 0;
preg_match("/<refsect1 role=\"seealso\">(.*)<\/refsect1>/s", $lang_content, $match2);

if (isset($match2[1])) {
    preg_match_all("/<member>(.*?)<\/member>/s", $match2[1], $match3);
    if (isset($match3[1])) {
        $lang_seeAlsoMember = count($match3[1]);	
    }
}

 if( $en_seeAlsoMember != $lang_seeAlsoMember ) {
       $nb_error['seealsoMember'] ++;
       $result_error[] = array(
        "libel" => "Error in seeAlso section :  $en_seeAlsoMember member in EN, and $lang_seeAlsoMember member in $LANG.",
        "value_en" => "N/A",
        "value_lang" => "N/A"
       );

 }

// END : SeeAlso section : check member


// CDATA section

$en_cdataSection = 0;
preg_match_all("/<!\[CDATA\[(.*?)\]\]>/s", $en_content, $match2);
$en_cdataSection = count($match2[1]);


$lang_cdataSection = 0;
preg_match_all("/<!\[CDATA\[(.*?)\]\]>/s", $lang_content, $match2);
$lang_cdataSection = count($match2[1]);


 if( $en_cdataSection != $lang_cdataSection ) {
       $nb_error['cdata'] ++;
       $result_error[] = array(
        "libel" => "Error about CDATA section :  $en_cdataSection section in EN, and $lang_cdataSection section in $LANG.",
        "value_en" => "N/A",
        "value_lang" => "N/A"
       );

 }

// END : CDATA section

// classsynopsis

$en_classsynopsis = array();
preg_match_all("/<classsynopsis>(\s.*?)<\/classsynopsis>/s", $en_content, $match);

for( $i = 0; $i < count($match[1]); $i++) {

  preg_match_all("/<ooclass><classname>(.*?)<\/classname><\/ooclass>/s", $match[1][$i], $match2);

  $en_classsynopsis[$i]['ooclass']['classname'] = $match2[1][0];

  preg_match_all("/<fieldsynopsis><type>(.*?)<\/type><varname>(.*?)<\/varname><\/fieldsynopsis>/s", $match[1][$i], $match2);


  $en_classsynopsis[$i]['fieldsynopsis']['varname'] = $match2[2];
  $en_classsynopsis[$i]['fieldsynopsis']['type']      = $match2[1];

}

$lang_classsynopsis = array();
preg_match_all("/<classsynopsis>(\s.*?)<\/classsynopsis>/s", $lang_content, $match);

for( $i = 0; $i < count($match[1]); $i++) {

  preg_match_all("/<ooclass><classname>(.*?)<\/classname><\/ooclass>/s", $match[1][$i], $match2);

  $lang_classsynopsis[$i]['ooclass']['classname'] = $match2[1][0];

  preg_match_all("/<fieldsynopsis><type>(.*?)<\/type><varname>(.*?)<\/varname><\/fieldsynopsis>/s", $match[1][$i], $match2);


  $lang_classsynopsis[$i]['fieldsynopsis']['varname'] = $match2[2];
  $lang_classsynopsis[$i]['fieldsynopsis']['type']      = $match2[1];


}

// Vérif
for( $i=0; $i < count($en_classsynopsis); $i++) {

 if( !isset($lang_classsynopsis[$i]['ooclass']['classname']) ) { $lang_classsynopsis[$i]['ooclass']['classname'] = ''; }

 // fieldsynopsis
 if( $en_classsynopsis[$i]['ooclass']['classname'] != $lang_classsynopsis[$i]['ooclass']['classname'] ) {
    $nb_error['classsynopsis'] ++;
    $result_error[] = array(
     "libel" => "Error in ooclass, classname :",
     "value_en" => $en_classsynopsis[$i]['ooclass']['classname'],
     "value_lang" => $lang_classsynopsis[$i]['ooclass']['classname']
    );

 }


 // methodparam
 for( $j = 0; $j < count($en_classsynopsis[$i]['fieldsynopsis']['varname']); $j++ ) {

   if( !isset($lang_classsynopsis[$i]['fieldsynopsis']['varname'][$j]) ) { $lang_classsynopsis[$i]['fieldsynopsis']['varname'][$j] = ''; }

     // fieldsynopsis name
     if( $en_classsynopsis[$i]['fieldsynopsis']['varname'][$j] != $lang_classsynopsis[$i]['fieldsynopsis']['varname'][$j] ) {
       $nb_error['classsynopsis'] ++;
       $result_error[] = array(
        "libel" => "Error in fieldsynopsis, varname :",
        "value_en" => $en_classsynopsis[$i]['fieldsynopsis']['varname'][$j],
        "value_lang" => $lang_classsynopsis[$i]['fieldsynopsis']['varname'][$j]
       );

     }

   if( !isset($lang_classsynopsis[$i]['fieldsynopsis']['type'][$j]) ) { $lang_classsynopsis[$i]['fieldsynopsis']['type'][$j] = ''; }

     // fieldsynopsis type
     if( $en_classsynopsis[$i]['fieldsynopsis']['type'][$j] != $lang_classsynopsis[$i]['fieldsynopsis']['type'][$j] ) {
       $nb_error['classsynopsis'] ++;
       $result_error[] = array(
        "libel" => "Error in fieldsynopsis, type :",
        "value_en" => $en_classsynopsis[$i]['fieldsynopsis']['type'][$j],
        "value_lang" => $lang_classsynopsis[$i]['fieldsynopsis']['type'][$j]
       );

     }

 }
}

// END : classsynopsis


// table

$en_table = array();
preg_match_all("/<table(.*?)<\/table>/s", $en_content, $match2);
$en_table = $match2[1];

$lang_table = array();
preg_match_all("/<table(.*?)<\/table>/s", $lang_content, $match2);
$lang_table = $match2[1];

for( $i=0; $i < count($en_table); $i++) {

preg_match_all("/<row>(.*?)<\/row>/s", $en_table[$i], $match3);
preg_match_all("/<row>(.*?)<\/row>/s", $lang_table[$i], $match4);

$nb_en = count($match3[1]);
$nb_lang = count($match4[1]);

if( $nb_en != $nb_lang ) {
       $nb_error['table'] ++;
       $result_error[] = array(
        "libel" => "Error in table listing :  $nb_en row(s) in EN, and $nb_lang in $LANG.",
        "value_en" => "N/A",
        "value_lang" => "N/A"
       );

}

}

// END : table



// para

$en_para = 0;
preg_match_all("/<para(.*?)<\/para>/s", $en_content, $match2);
$en_para = count($match2[1]);


$lang_para = 0;
preg_match_all("/<para(.*?)<\/para>/s", $lang_content, $match2);
$lang_para = count($match2[1]);


 if( $en_para != $lang_para ) {
       $nb_error['para'] ++;
       $result_error[] = array(
        "libel" => "Error about para tag :  $en_para tag <para> in EN, and $lang_para in $LANG.",
        "value_en" => "N/A",
        "value_lang" => "N/A"
       );

 }

// END : para


return $result_error;
} // do_check

function check($lang_file, $en_file) {
 global $final_result;

 if( !is_file($en_file) ) {
  return false;
 }

$en_content = file_get_contents($en_file);
$lang_content = file_get_contents($lang_file);

$result = do_check($en_content, $lang_content);

 if( count($result) > 0 ) {

  $final_result[] = array(

   "file" => $lang_file,
   "error" => $result

  );

 }

} // check

// Grabs the revision tag and stores credits from the file given
function get_tags($file, $val = "en-rev") {
    // Read the first 500 chars. The comment should be at
    // the begining of the file

    if( !is_file($file) ) {
     return false;
    }

    $fp = @fopen($file, "r") or die ("Unable to read $file.");
    $line = fread($fp, 500);
    fclose($fp);

     // No match before the preg
     $match = array();

    // Check for English CVS revision tag (. is for $ in the preg!),
    // Return if this was needed (it should be there)
    if ($val == "en-rev") {
        preg_match("/<!-- .Revision: \d+\.(\d+) . -->/", $line, $match);
        return isset($match[1]) ? $match[1] : false;
    } else {

     // Check for the translations "revision tag"
     preg_match("/<!--\s*EN-Revision:\s*\d+\.(\d+)\s*/", $line, $match);

     // Return with found revision info (number, maint, status)
     return isset($match[1]) ? $match[1] : false;

    }

} // get_tags() function end


// =========================================================================
// A function to check directory status in translated directory
// =========================================================================

// Check the status of files in a diretory of phpdoc XML files
// The English directory is passed to this function to check
function get_dir_status($dir) {

global $LANG;

    //echo "Entrée dans le répertoire : ".$dir."\n";

    $en_dir = str_replace("/$LANG/", "/en/", $dir);

    // Collect files and diretcories in these arrays
    $directories = array();
    $files       = array();

    // Open the directory
    $handle = @opendir($dir);

    // Walk through all names in the directory
    while ($file = @readdir($handle)) {

      // If we found a file with one or two point as a name,
      // or a CVS directory, skip the file
      if (preg_match("/^\.{1,2}/",$file) || $file == 'CVS')
        continue;

      // JUST TEMPORARY TILL THE <TRANSLATION>/REFERENCE/FUNCTIONS.XML - ISSUE IS CLARIFIED
      // If we found a file functions.xml in the
      // <lang>/reference/ tree, skip the file
      if (
           $file == "rsusi.txt"
          || $file == "missing-ids.xml"
          || ($file == "extensions.xml" && strpos($dir, '/appendices/'))
          || $file == "README"
          || $file == "contributors.xml"
          || $file == "contributors.ent"
          || $file == "reserved.constants.xml"
          || $file == 'DO_NOT_TRANSLATE'
          || strpos($dir, '/internals/')
          || strpos($dir, '/internals2/')
          || strpos($file, 'entities.') === 0
           )
        continue;

      // Collect files and directories
      if (is_dir($dir.$file)) { $directories[] = $file; }
      else { $files[] = $file; }

    }

    // Close the directory
    @closedir($handle);

    // Sort files and directories
    sort($directories);
    sort($files);

    // Go through files first
    $dir_status = array();
    foreach ($files as $file) {

        $lang_tag = get_tags($dir.$file, "lang");
        $en_tag = get_tags($en_dir.$file, "en-rev");

        if ($lang_tag === false || $en_tag === false) {
            continue;
        }

        if( "$lang_tag" == "$en_tag" ) {
          check($dir.$file, $en_dir.$file);
        }
    }

    // Then go through subdirectories, merging all the info
    // coming from subdirs to one array
    foreach ($directories as $file) {
       get_dir_status($dir.$file.'/');
    }

    // Return with collected file info in
    // this dir and subdirectories [if any]
    return $dir_status;

} // get_dir_status() function end


// =========================================================================
// Start of the program execution
// =========================================================================

// Check for directory validity
if (!@is_dir($DOCDIR . $LANG)) {
    die("The $LANG language code is not valid");
}

// Figure out generation date
$date = date("r");


// =========================================================================
// Files table goes here
// =========================================================================

// Get all files status
get_dir_status($DOCDIR.$LANG."/");

$nb_error_all = 0;

while( list($key, $val) = each($nb_error) ) {

 $nb_error_all += $val;

}

$html = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<html>
<head>
<title>PHPDOC Translation-check-tools</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
h2,td,a,p,a.ref,th { font-family:Arial,Helvetica,sans-serif; font-size:14px; }
h2,th,a.ref, .close a { color:#FFFFFF; }
td,a,p { color:#000000; }
h2     { font-size:28px; }
th     { font-weight:bold; }
.blue  { background-color:#666699; }
.act   { background-color:#68D888; }
.norev { background-color:#f4a460; }
.old   { background-color:#eee8aa; }
.crit  { background-color:#ff6347; }
.wip   { background-color:#dcdcdc; }
.r     { text-align:right }
.rb    { text-align:right; font-weight:bold; }
.b     { font-weight:bold; }
.c     { text-align:center }
body   { margin:0px 0px 0px 0px; background-color:#F0F0F0; }
table.info  { border: 1px solid #000000; margin-top: 10px; margin-left: auto; margin-right: auto; width: 600px;}
.close { float: left; }
.close a { text-decoration: none; }
//-->
</style>
</head>
<body>

<script type="text/javascript">

function closeTable(id) {

  document.getElementById(id).style.display = "none";

}

</script>

<table style="width: 100%; background-color:#666699;" border="0" cellspacing="0">
<tr>
 <td>
   <table width="100%" border="0" cellspacing="1" bgcolor="#9999CC">
    <tr>
     <td><h2 class="c">PHP Manual : Tools for translator</h2>
     <p class="c" style="font-size:12px;">Generated: '.date("r").' &nbsp; / &nbsp; Language: '.$LANG.'<br></p>
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>

<p class="c">Info : Only files who are sync with EN version are checked.</p>

';

$nb_file = count($final_result);

$html.='

<table class="info">
<tr class="blue">
 <th>Error Libel</th>
 <th>Nb error</th>
</tr>
<tr class="old">
 <td><strong>Chapter</strong><br /><em>Check "chapter" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['chapter'] == 0)?'-':'<strong>'.$nb_error['chapter'].'</strong>').'</td>
</tr>
<tr>
 <td><strong>Appendix</strong><br /><em>Check "Appendix" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['appendix'] == 0)?'-':'<strong>'.$nb_error['appendix'].'</strong>').'</td>
</tr>
<tr class="old">
 <td><strong>qandaentry</strong><br /><em>Check "qandaentry" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['qandaentry'] == 0)?'-':'<strong>'.$nb_error['qandaentry'].'</strong>').'</td>
</tr>
<tr>
 <td><strong>link</strong><br /><em>Check "link" tag &amp; attribut consistency.<br/>Error can be just a different order of link in the page.</em></td>
 <td>'.(($nb_error['link'] == 0)?'-':'<strong>'.$nb_error['link'].'</strong>').'</td>
</tr>
<tr class="old">
 <td><strong>sect1</strong><br /><em>Check "sect1" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['sect1'] == 0)?'-':'<strong>'.$nb_error['sect1'].'</strong>').'</td>
</tr>
<tr>
 <td><strong>book</strong><br /><em>Check "book" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['book'] == 0)?'-':'<strong>'.$nb_error['book'].'</strong>').'</td>
</tr>
<tr class="old">
 <td><strong>preface</strong><br /><em>Check "preface" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['preface'] == 0)?'-':'<strong>'.$nb_error['preface']).'</td>
</tr>
<tr>
 <td><strong>section</strong><br /><em>Check "section" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['section'] == 0)?'-':'<strong>'.$nb_error['section'].'</strong>').'</td>
</tr>
<tr class="old">
 <td><strong>varlistentry</strong><br /><em>Check "varlistentry" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['varlistentry'] == 0)?'-':'<strong>'.$nb_error['varlistentry'].'</strong>').'</td>
</tr>
<tr>
 <td><strong>reference</strong><br /><em>Check "reference" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['reference'] == 0)?'-':'<strong>'.$nb_error['reference'].'</strong>').'</td>
</tr>
<tr class="old">
 <td><strong>refentry</strong><br /><em>Check "refentry" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['refentry'] == 0)?'-':'<strong>'.$nb_error['refentry'].'</strong>').'</td>
</tr>
<tr>
 <td><strong>refsect1</strong><br /><em>Check "refsect1" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['refsect1'] == 0)?'-':'<strong>'.$nb_error['refsect1'].'</strong>').'</td>
</tr>
<tr class="old">
 <td><strong>methodsynopsis</strong><br /><em>Check "methodsynopsis" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['methodsynopsis'] == 0)?'-':'<strong>'.$nb_error['methodsynopsis'].'</strong>').'</td>
</tr>
<tr>
 <td><strong>refpurpose</strong><br /><em>Check "refpurpose" tag &amp; warn if there is a point at the end.</em></td>
 <td>'.(($nb_error['refpurpose'] == 0)?'-':'<strong>'.$nb_error['refpurpose'].'</strong>').'</td>
</tr>
<tr class="old">
 <td><strong>seealsoMember</strong><br /><em>Check "SeeAlso" section &amp; warn if there is less member in translation than in EN version.</em></td>
 <td>'.(($nb_error['seealsoMember'] == 0)?'-':'<strong>'.$nb_error['seealsoMember'].'</strong>').'</td>
</tr>
<tr>
 <td><strong>cdata</strong><br /><em>Check "cdata" section &amp; warn if there is less cdata section in translation than in EN version.</em></td>
 <td>'.(($nb_error['cdata'] == 0)?'-':'<strong>'.$nb_error['cdata'].'</strong>').'</td>
</tr>
<tr class="old">
 <td><strong>classsynopsis</strong><br /><em>Check "classsynopsis" tag &amp; attribut consistency.</em></td>
 <td>'.(($nb_error['classsynopsis'] == 0)?'-':'<strong>'.$nb_error['classsynopsis'].'</strong>').'</td>
</tr>
<tr>
 <td><strong>row in Table</strong><br /><em>Check number of rows in table tag.</em></td>
 <td>'.(($nb_error['table'] == 0)?'-':'<strong>'.$nb_error['table'].'</strong>').'</td>
</tr>
<tr class="old">
 <td><strong>para</strong><br /><em>Check number of para tag.</em></td>
 <td>'.(($nb_error['para'] == 0)?'-':'<strong>'.$nb_error['para'].'</strong>').'</td>
</tr>

<tr class="crit">
 <td class="r">Total :<br/>In '.$nb_file.' file(s)</td>
 <td class="b c">'.$nb_error_all.'</td>
</tr>
</table>
';

for( $i = 0; $i < $nb_file; $i++ ) {

$html.= '
<table class="info" id="table_'.$i.'">
 <tr>
  <th colspan="2" class="blue"><div class="close"><a href="javascript:void(0)" title="Hide" onclick="javascript:closeTable(\'table_'.$i.'\');">[x]</a></div>'.$final_result[$i]['file'].'</th>
 </tr>
';

  for( $j=0; $j < count($final_result[$i]['error']); $j++) {

   $html.= '
    <tr class="norev">
     <td class="b c" colspan="2">'.$final_result[$i]['error'][$j]['libel'].'</td>
    </tr>
    <tr class="act">
     <td class="r" style="width:30%;">Value in EN : </td>
     <td>'.$final_result[$i]['error'][$j]['value_en'].'</td>
    </tr>
    <tr class="old">
     <td class="r">Value in '.strtoupper($LANG).' : </td>
     <td>'.$final_result[$i]['error'][$j]['value_lang'].'</td>
    </tr>
   ';

  }


$html.= '</table>';

}

$html.= '</body></html>';

echo $html;
?>
