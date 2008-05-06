<?php
//*-- The PHPDOC Online XML Editing Tool 
//*-- Developed by Salah Faya visualmind(@)php.net 
//*-- Version 1.0 - essentially developed for Arabic Translation of PHP Manual
//*-- Now updated to work with all phpdoc translations

//--- base file (all other files require this to start)

//------- Configuration 
// Root path of cvs
$cvsRootPath = '/path/to/cvs/';

// Path to english xml sources from cvs 
$enDocCVSPath = $cvsRootPath.'phpdoc/en/';

// Require User Login by email (needed for user file saving)
$requireLogin = false;

if ($requireLogin) {	
	// Users folder where their cached files are saved - must be writable
	$usersCachePath = '/path/to/editor/users/';
	// Files Permissions Mod
	$filesChMod = 0777;	
}


// Languages and paths: (ToDo Font should be defined for better display)

addLanguage('Arabic', $cvsRootPath.'phpdoc-ar/ar/', 'ar', 'utf-8', 'RTL', 'doc-ar@lists.php.net', 'doc-ar-subscribe@lists.php.net', 'Salah Faya visualmind(@)php.net');
addLanguage('Brazilian-PT', $cvsRootPath.'phpdoc-pt_BR/pt_BR/', 'pt_BR', 'iso-8859-1', 'LTR', 'doc-br@lists.php.net', 'doc-br-subscribe@lists.php.net', '');
addLanguage('Bulgarian', $cvsRootPath.'phpdoc-bg/bg/', 'bg', 'utf-8', 'LTR', 'doc-bg@lists.php.net', 'doc-bg-subscribe@lists.php.net', '');
addLanguage('Catalan', $cvsRootPath.'phpdoc-ca/ca/', 'ca', 'iso-8859-1', 'LTR', 'doc-ca@lists.php.net', 'doc-ca-subscribe@lists.php.net', '');
addLanguage('Chinese-HK', $cvsRootPath.'phpdoc-hk/hk/', 'hk', 'big5', 'LTR', 'doc-hk@lists.php.net', 'doc-hk-subscribe@lists.php.net', '');
addLanguage('Chinese-Simplified', $cvsRootPath.'phpdoc-zh/zh/', 'zh', 'gb2312', 'LTR', 'doc-zh@lists.php.net', 'doc-zh-subscribe@lists.php.net', '');
addLanguage('Chinese-Traditional', $cvsRootPath.'phpdoc-tw/tw/', 'tw', 'big5', 'LTR', 'doc-tw@lists.php.net', 'doc-tw-subscribe@lists.php.net', '');
addLanguage('Czech', $cvsRootPath.'phpdoc-cs/cs/', 'cs', 'iso-8859-2', 'LTR', 'doc-cs@lists.php.net', 'doc-cs-subscribe@lists.php.net', '');
addLanguage('Danish', $cvsRootPath.'phpdoc-da/da/', 'da', 'iso-8859-1', 'LTR', 'doc-da@lists.php.net', 'doc-da-subscribe@lists.php.net', '');
addLanguage('Dutch', $cvsRootPath.'phpdoc-nl/nl/', 'nl', 'iso-8859-1', 'LTR', 'doc-nl@lists.php.net', 'doc-nl-subscribe@lists.php.net', '');
addLanguage('Finnish', $cvsRootPath.'phpdoc-fi/fi/', 'fi', 'iso-8859-1', 'LTR', 'doc-fi@lists.php.net', 'doc-fi-subscribe@lists.php.net', '');
addLanguage('French', $cvsRootPath.'phpdoc-fr/fr/', 'fr', 'iso-8859-1', 'LTR', 'doc-fr@lists.php.net', 'doc-fr-subscribe@lists.php.net', '');
addLanguage('German', $cvsRootPath.'phpdoc-de/de/', 'de', 'iso-8859-1', 'LTR', 'doc-de@lists.php.net', 'doc-de-subscribe@lists.php.net', '');
addLanguage('Greek', $cvsRootPath.'phpdoc-el/el/', 'el', 'iso-8859-7', 'LTR', 'doc-el@lists.php.net', 'doc-el-subscribe@lists.php.net', '');
addLanguage('Hebrew', $cvsRootPath.'phpdoc-he/he/', 'he', 'windows-1255', 'RTL', 'doc-he@lists.php.net', 'doc-he-subscribe@lists.php.net', '');
addLanguage('Hungarian', $cvsRootPath.'phpdoc-hu/hu/', 'hu', 'iso-8859-2', 'LTR', 'doc-hu@lists.php.net', 'doc-hu-subscribe@lists.php.net', '');
addLanguage('Indonesian', $cvsRootPath.'phpdoc-id/id/', 'id', 'iso-8859-1', 'LTR', 'doc-id@lists.php.net', 'doc-id-subscribe@lists.php.net', '');
addLanguage('Italian', $cvsRootPath.'phpdoc-it/it/', 'it', 'iso-8859-1', 'LTR', 'doc-it@lists.php.net', 'doc-it-subscribe@lists.php.net', '');
addLanguage('Japanese', $cvsRootPath.'phpdoc-ja/ja/', 'ja', 'utf-8', 'LTR', 'doc-ja@lists.php.net', 'doc-ja-subscribe@lists.php.net', '');
addLanguage('Korean', $cvsRootPath.'phpdoc-kr/kr/', 'kr', 'utf-8', 'LTR', 'doc-kr@lists.php.net', 'doc-kr-subscribe@lists.php.net', '');
addLanguage('Lithuanian', $cvsRootPath.'phpdoc-lt/lt/', 'lt', 'iso-8859-1', 'LTR', 'doc-lt@lists.php.net', 'doc-lt-subscribe@lists.php.net', '');
addLanguage('Norwegian', $cvsRootPath.'phpdoc-no/no/', 'no', 'utf-8', 'LTR', 'doc-no@lists.php.net', 'doc-no-subscribe@lists.php.net', '');
addLanguage('Polish', $cvsRootPath.'phpdoc-pl/pl/', 'pl', 'iso-8859-2', 'LTR', 'doc-pl@lists.php.net', 'doc-pl-subscribe@lists.php.net', '');
addLanguage('Romanian', $cvsRootPath.'phpdoc-ro/ro/', 'ro', 'utf-8', 'LTR', 'doc-ro@lists.php.net', 'doc-ro-subscribe@lists.php.net', '');
addLanguage('Russian', $cvsRootPath.'phpdoc-ru/ru/', 'ru', 'utf-8', 'LTR', 'doc-ru@lists.php.net', 'doc-ru-subscribe@lists.php.net', '');
addLanguage('Slovak', $cvsRootPath.'phpdoc-sk/sk/', 'sk', 'iso-8859-2', 'LTR', 'doc-sk@lists.php.net', 'doc-sk-subscribe@lists.php.net', '');
addLanguage('Slovenian', $cvsRootPath.'phpdoc-sl/sl/', 'sl', 'iso-8859-1', 'LTR', 'doc-sl@lists.php.net', 'doc-sl-subscribe@lists.php.net', '');
addLanguage('Spanish', $cvsRootPath.'phpdoc-es/es/', 'es', 'iso-8859-1', 'LTR', 'doc-es@lists.php.net', 'doc-es-subscribe@lists.php.net', '');
addLanguage('Swedish', $cvsRootPath.'phpdoc-sv/sv/', 'sv', 'iso-8859-1', 'LTR', 'doc-sv@lists.php.net', 'doc-sv-subscribe@lists.php.net', '');
addLanguage('Turkish', $cvsRootPath.'phpdoc-tr/tr/', 'tr', 'utf-8', 'LTR', 'doc-tr@lists.php.net', 'doc-tr-subscribe@lists.php.net', '');
//..


// Hide files (ignore)
$ignoreListingFolders = array('.', '..', 'CVS');
$ignoreListingFiles = array('contributors.xml', 'contributors.ent', 'livedocs.ent');

//------- Base functions 

function addLanguage($lang, $cvspath, $id, $charset='utf-8', $direction='LTR', $mailing='', $mailsubscribe='', $coodinator='', $font='Fixedsys') {
	global $phpdocLangs;
	$phpdocLangs[$lang]['DocCVSPath'] = $cvspath;
	$phpdocLangs[$lang]['charset'] = $charset;
	$phpdocLangs[$lang]['direction'] = $direction;
	$phpdocLangs[$lang]['id'] = $id;
	$phpdocLangs[$lang]['mailing'] = $mailing;
	$phpdocLangs[$lang]['mailingSubscribe'] = $mailsubscribe;
	$phpdocLangs[$lang]['coordinator'] = $coordinator;
	$phpdocLangs[$lang]['font'] = $font;
}

function sessionCheck($arguments='') {
	session_start();
	if (!isset($_SESSION['user'])) {
		if ($_SERVER['REQUEST_METHOD']=='GET') {			
			$_SESSION['redo'] = $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
		}
		header('location: login.php'.$arguments);
		exit('<a href="login.php">Login first</a>');
	}
	return $_SESSION['user'];
}

function getDateTimeToLog() {
	return date('d/m H:i [').time().']';
}

function getUserIP() {
	return $_SERVER['REMOTE_ADDR'];
}

function getCacheName($filename) {
	// change reference/ext/file.xml  to reference~ext~file.xml	
	$filename = str_replace(array('/', '\\'), '~', $filename);
       return $filename;
}

function stripslashes2($text) {
	if (get_magic_quotes_gpc()) {
		$text = stripslashes($text);
	}
	return $text;
}

function getRevision($file, $en=false) {
	$f = fopen($file, "rb");
	$data = fread($f, 200);
	fclose($f);

	// Use en-revision
	if ($en) {
		preg_match("#<!-- *EN-Revision: +([0-9\.]+)#i", $data, $result);
		$rev = $result[1];
	} else {
		preg_match("#<!-- *\\$"."Revision: +([0-9\.]+)#i", $data, $result);
		$rev = $result[1];
	}
	return $rev;
}

function getTranslationStatus($file) {
	global $user, $phpdocLangs, $enDocCVSPath;

	$lang = $user['phpdocLang'];
	$translationPath = $phpdocLangs[$lang]['DocCVSPath'];	
	$trFile = $translationPath.$file;
	$enFile = $enDocCVSPath.$file;

	$status['lastEnRevision'] = getRevision($enFile);
	if (file_exists($trFile)) {
		$status['translated'] = true;
		$status['fileRevision'] = getRevision($trFile);
		$status['fileEnRevision'] = getRevision($trFile, true);
		if ($status['fileEnRevision'] && $status['lastEnRevision']) {
			$distance = round((float) $status['lastEnRevision'], 3) - round((float) $status['fileEnRevision'], 3) ; 
			$backward = 0;
			if ($distance<0) {
				$backward = -1;
			} elseif ($distance && $distance<.03) {
				$backward = 1;
			} elseif ($distance && $distance<.2) {
				$backward = 2;
			} elseif ($distance && $distance<.4) {
				$backward = 3;
			} elseif ($distance && $distance<.6) {
				$backward = 4;
			} elseif ($distance && $distance>=.6) {
				$backward = 5;
			}
			$status['backward'] = $backward;
			$status['distance'] = $distance;			
		}
	} else {
		$status['translated'] = false;
	}	
	return $status;
}

?>
