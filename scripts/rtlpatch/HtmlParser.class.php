<?
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 2.02 of the PHP licience,     |
  | that is bundled with this package in the file LICENCE and is         |
  | avalible through the world wide web at                               |
  | http://www.php.net/license/2_02.txt.                                 |
  | If uou did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world wide web, please send a note to          |
  | license@php.net so we can mail you a copy immediately                |
  +----------------------------------------------------------------------+
  | Authors:    Moshe Doron <momo@php.net>                          	 |
  +----------------------------------------------------------------------+
  
 $Id$
*/

define("__HTML_FREE_ENGLISH__",1);
define("__HTML_FREE_HEBREW__",2);
define("__HTML_UNKNOWN__",5);
define("__HTML_COMMENT__",6);
define("__HTML_PROCESS__",7);


$HEType["a"] =				10;
$HEType["acronym"] =		20;
$HEType["address"] =		30;
$HEType["applet"] =			40;
$HEType["area"] =			50;
$HEType["b"] =				60;
$HEType["base"] =			70;
$HEType["basefont"] =		80;
$HEType["bdo"] =			90;
$HEType["bgsound"] =		100;
$HEType["big"] =			110;
$HEType["blockquote"] =		120;
$HEType["body"] =			130;
$HEType["br"] =				140;
$HEType["button"] =			150;
$HEType["caption"] =		160;
$HEType["cite"] =			170;
$HEType["code"] =			180;
$HEType["col"] =			190;
$HEType["colgroup"] =		200;
$HEType["dd"] =				210;
$HEType["del"] =			220;
$HEType["dfn"] =			230;
$HEType["div"] =			240;
$HEType["dl"] =				250;
$HEType["dt"] =				260;
$HEType["em"] =				270;
$HEType["embed"] =			280;
$HEType["fieldset"] =		290;
$HEType["font"] =			300;
$HEType["form"] =			310;
$HEType["frame"] =			320;
$HEType["frameset"] =		330;
$HEType["h1"] =				340;
$HEType["h2"] =				350;
$HEType["h3"] =				360;
$HEType["h4"] =				370;
$HEType["h5"] =				380;
$HEType["h6"] =				390;
$HEType["h7"] =				400;
$HEType["hr"] =				410;
$HEType["i"] =				420;
$HEType["iframe"] =			430;
$HEType["ilayer"] =			440;
$HEType["img"] =			450;
$HEType["input"] =			460;
$HEType["ins"] =			470;
$HEType["kbd"] =			480;
$HEType["label"] =			490;
$HEType["layer"] =			500;
$HEType["legend"] =			510;
$HEType["li"] =				520;
$HEType["link"] =			530;
$HEType["mailto"] =			540;
$HEType["map"] =			550;
$HEType["marquee"] =		560;
$HEType["meta"] =			570;
$HEType["multicol"] =		580;
$HEType["noframes"] =		590;
$HEType["noscript"] =		600;
$HEType["object"] =			610;
$HEType["ol"] =				620;
$HEType["option"]=			630;
$HEType["p"] =				640;
$HEType["param"] =			650;
$HEType["pre"] =			660;
$HEType["q"] =				670;
$HEType["samp"] =			680;
$HEType["script"] =			690;
$HEType["select"] =			700;
$HEType["small"] =			710;
$HEType["sound"] =			720;
$HEType["spacer"] =			730;
$HEType["span"] =			740;
$HEType["strong"] =			750;
$HEType["style"] =			760;
$HEType["sub"] =			770;
$HEType["sup"] =			780;
$HEType["table"] =			790;
$HEType["tbody"] =			800;
$HEType["td"] =				810;
$HEType["textarea"] =		820;
$HEType["tfoot"] =			830;
$HEType["th"] =				840;
$HEType["thead"] =			850;
$HEType["tr"] =				860;
$HEType["tt"] =				870;
$HEType["ul"] =				880;
$HEType["var"] =			890;

$HEType["blink"] =			900;
$HEType["center"] =			910;
$HEType["dir"] =			920;
$HEType["head"] =			930;
$HEType["html"] =			940;
$HEType["menu"] =			950;
$HEType["nobr"] =			960;
$HEType["noembed"] =		970;
$HEType["nolayer"] =		980;
$HEType["plaintext"] =		990;
$HEType["s"] =				1000;
$HEType["server"] =			1010;
$HEType["strike"] =			1020;
$HEType["title"] =			1030;
$HEType["u"] =				1040;
$HEType["uc"] =				1045;
$HEType["wbr"] =			1050;
$HEType["xmp"] =			1060;


foreach ($HEType as $key=>$value){
	$EHType[$value] = $key;	
}


class CHtmlParse{
	
	/*
 	* Parser functions:
 	*
 	*   html_parse()                       - top level entry point.			  
	*     parse_start()               - deals with start tags           <A...>
	*		parse_script			  - deals with <script tag				  
	*     parse_end()                 - deals with end tags             </...>
 	*     plaintext()														  
	*		plaintext_english()												  
	*		plaintext_hebrew()												  
 	*     parse_decl()                - deals with declarations         <!...>
 	*       parse_comment()           - deals with <!-- ... -->				  
 	*       parse_marked_section      - deals with <![ ... [ ... ]]> 		  
 	*     parse_process()             - deals with process instructions <?...>
 	*     parse_null()                - deals with anything else        <....>
 	*/
	
	var $len;
	var $data;
	var $pos;
	var $encoding = "hebrew";
	var $ATE;  //AllTheElements ordering by location into the document.
	var $EBT;  //ElementsByType.
	var $ECE;   //Elements close by $ATE id.
	var $NOE; //NumberOfElements.
	
	
	function CHtmlParse($data){
		$this->len = strlen($data);
		$this->data = $data;
		$this->pos = 0;
		$this->ATE[] = false;
		$this->NOE = 0;
		
		$this->map_all();
		$this->end_map();
//		var_dump($this);
	}
	
	function map_all(){
		
		$this->move_to_next_notrim();
		while($this->pos < $this->len){
//debuginfo("pos is: ". $this->pos. ", character is: " .$this->data{$this->pos});
			if($this->data{$this->pos} != "<"){
				if ($x = $this->plaintext()) $this->map($x);
			}else if(++$this->pos < $this->len){
				$p = $this->data{$this->pos};
				if($p == "!"){
					$this->parse_decl();
					//$this->map($this->parse_decl());
				}else if($p == "/"){
					$this->map($this->parse_end());
					$this->pos++;
				}else if($p == "?"){
					$this->map($this->parse_process());
				}else{
//debuginfo("opening");
					$this->map($this->parse_start($endfl),$endfl);
					$this->pos++;
				}
			}
			$this->move_to_next_notrim();
		}
	}
	
	function parse_start(&$endfl){
		global $HEType;
		
		//fining the <html> tag type:
		$last = $this->pos;
		$this->move_to_prop_end();
		$return["w4htype"] = $HEType[strtolower(substr($this->data,$last,$this->pos-$last))];
		// looping till getting all the elements:
		while(1){
//debuginfo("pos is: ". $this->pos. ", character is: " .$this->data{$this->pos});
//debuginfo($return);
			$offset = $this->move_to_prop_start();
			if($offset < 0) {
				$endfl = $offset+1;
//debuginfo("endfl: $endfl");
				return $return;
			}
			
			$last = $this->pos;
			$offset = $this->move_to_propname_end();
			if($offset < 0){
				$return[strtolower(substr($this->data,$last,$this->pos-$last-$offset))]="nop";
				$endfl = $offset+1;
//debuginfo("endfl: $endfl");
				return $return;
			}
			
			$prop = strtolower(substr($this->data,$last,$this->pos-$last));
			$this->move_to_next_notrim();
			if($this->data{$this->pos} == '='){ // if property have value:
				$this->move_to_next_notrim();
				$last=++$this->pos;
				$pq = $this->data{$this->pos};
				if($pq =="\'"||$pq =="\""){//on quotation
					$last = ++$this->pos;
					if(!$ret = $this->move_to_character($pq)){
						$this->add_error("tag property have no close -$pq- quetation");
					}
					$return[$prop] = substr($this->data,$last,$this->pos-$last);
					$this->pos++;
				}else{ //on no quotation
					$offset = $this->move_to_prop_end();
					if($offset < 0){ // '>' found:
						$return[$prop] = substr($this->data,$last,$this->pos-$last+$offset+1);
						$endfl = $offset+1; //tag have '/' close.
						return $return;
					}
					$return[$prop] = substr($this->data,$last,$this->pos-$last);
				}
			}
		}
	}
	
	function parse_end(){
		global $HEType;
		
		$last = ++$this->pos;
		$this->move_to_character(">");
		$tmp = strtolower(substr($this->data,$last,$this->pos-$last));
		if(isset($HEType[$tmp])){
			$return["w4htype"] = -$HEType[$tmp];
		}else{
			$return["w4htype"] = -__HTML_UNKNOWN__;
		}
		return $return;
	}
	
	function parse_decl(){
		$this->pos++;
//debuginfo("pos is: ". $this->pos. ", character is: " .$this->data{$this->pos});
//debuginfo(substr($this->data,$this->pos-10,20));
		if($this->pos <$this->len){
			if($this->data{$this->pos} == "-" && $this->data{$this->pos+1} == "-"){
				return $this->parse_comment();
			}else if($this->data{$this->pos} == "["){
				return $this->map($this->parse_marked_section());
			}else{
				$this->parse_null();
			}
		}else{
			$this->add_error("broken remark",0);
			return false;
		}
	}
	
		function parse_comment(){
			$last = $this->pos;
			while ($this->pos+1 < $this->len){
//debuginfo("yep: ".$this->data{$this->pos}.$this->data{$this->pos+1}.$this->data{$this->pos+2});
				if($this->data{$this->pos} == "-" 
				 && $this->data{$this->pos+1} == "-"
				 && $this->data{$this->pos+2} == ">"){
					
					$oldlen = $this->len;
					$this->len = $this->pos;
					$this->pos = $last;
					$tmp = array("w4htype"=>__HTML_COMMENT__);
					$this->map($tmp);
					$this->map_all();
					$this->len = $oldlen;
					$tmp = array("w4htype"=>__HTML_COMMENT__*(-1));
					$this->map($tmp);
					$this->pos+=3;
					return 1;
				}
				$this->pos++;
			}
			$this->add_error("comment on $last char haven't closed");
			return array("w4htype"=>__HTML_COMMENT__,"data"=>substr($this->data,$last,$this->pos-$last));
		}
		
		
		function parse_marked_section(){
			$last = $this->pos;
			while ($this->pos <= $this->len){
				if($this->data{$this->pos} == ">"){
					$return = array("w4htype"=>__HTML_MARKED__,"data"=>substr($this->data,$last,$this->pos-$last));
					$this->pos++;
					return $return;
				}
				$this->pos++;
			}
			$this->add_error("marked on $last char haven't closed");
			return array("w4htype"=>__HTML_MARKED__,"data"=>substr($this->data,$last,$this->pos-$last));
		}
	
	function parse_process(){
		$last = $this->pos;
		while ($this->pos < $this->len){
			if($this->data{$this->pos} == "?" && $this->data{$this->pos+1} == ">"){
				$return = array("w4htype"=>__HTML_PROCESS__,"data"=>substr($this->data,$last,$this->pos-$last));
				$this->pos+=3;
				return $return;
			}
			$this->pos++;
		}
		$this->add_error("process on $last char haven't closed");
		return array("w4htype"=>__HTML_PROCESS__,"data"=>substr($this->data,$last,$this->pos-$last));
	}
 	
	function parse_null(){
		$this->add_error("NULL found",-1);
		$this->move_to_character(">");
		$this->pos++;
//debuginfo("pos is: ". $this->pos. ", character is: " .$this->data{$this->pos});
	}
	
	function plaintext(){
		$this->move_to_next_notrim();
		return @call_user_method("plaintext_".$this->encoding,$this);
	}
		
		function plaintext_english(){
			$last =  $this->pos;
			$encoding = __HTML_FREE_ENGLISH__;
			
			while($this->pos < $this->len && $this->data{$this->pos} != '<'){
				$this->pos++;
			}
			if($last ==  $this->pos) return false;
			return array("w4htype"=>$encoding,"data"=>substr($this->data,$last,$this->pos-$last));
		}
		
		function plaintext_hebrew(){
			$last = $this->pos;
			$encoding= __HTML_FREE_ENGLISH__;
			
			while($this->pos < $this->len && $this->data{$this->pos} != '<'){
				if(ord($this->data{$this->pos}) >= 224 && ord($this->data{$this->pos}) <= 250)
					$encoding = __HTML_FREE_HEBREW__; 
				$this->pos++;
			}
			if($last ==  $this->pos) return false;
			return array("w4htype"=>$encoding,"data"=>substr($this->data,$last,$this->pos-$last));
		}
		
		
	function map(&$element,$endfl=0){
		$type = $element["w4htype"];
//debuginfo("<font color=blue>type: $type</font>");
//debuginfo($element);
		$this->NOE++;
		$this->ATE[$this->NOE] = $element;
		if($type>0) $this->EBT[$type][] = $this->NOE;
		if($endfl){
//echo "<font color=blue>type: $type</font>";
			$this->ECE[$this->NOE] = $this->NOE;
		}else if($type >= 10){
//echo "<font color=blue>type: $type</font>";
			$this->maptmp[$type][] = $this->NOE;
//debuginfo($this->maptmp[$type]);
		}else if($type <= -10){
//mysyslog($type);
//print_r($this->maptmp[-$type]);

			if($id = @array_pop($this->maptmp[-$type])){
				$this->ECE[$id] = $this->NOE;
			}else{ //if more close 
				$this->ECE[$this->NOE] = $this->NOE;
				$this->add_error("more  close tag (code $type) then open");
			}
		}
//echo "<font color=green>";var_dump($this->maptmp);echo "</font>";
	}
	
	function end_map(){
		for($a=1;$a<=$this->NOE;$a++){
			if(!isset($this->ECE[$a])) $this->ECE[$a] = $a;
		}
		unset($this->maptmp);
	}
	
	
	
	
	
	function add_error($msg,$level=0){
		$this->errors[] = array("msg"=>$msg,"level"=>$level);
		if($level > 1000){
			$this->show_errors();
//var_dump($this);
		}
		//if($level >= -1) exit;
	}
	
	function show_errors(){
		$count = count($this->errors);
		for($a=0;$a<$count;$a++){
			echo $this->errors[$a]["msg"]."\r\n<br>";
		}
	}
	
	
	
	
	
	
	
	// ----------------------------------------------------------
	// pointer checking & moving function to convert into macroz:
	// ----------------------------------------------------------
	
	function move_to_character($char){
		while($this->pos++ < $this->len){
			if($this->data{$this->pos} == $char){
				return $this->pos;
			}
		}
		$this->add_error("no $char ascii:".ord($char).": found");
		return 0;
	}
	
	
	function move_to_prop_start(){
		while($this->pos < $this->len){
//debuginfo($this->data{$this->pos});
			if (!is_html_trim($this->data{$this->pos})){
//debuginfo($this->data{$this->pos});
				if($this->data{$this->pos}==">"){
//debuginfo("ahe be chigale me");
					return -1; // standard html tag.
				}else if($this->data{$this->pos} == "/"){
					if (($this->pos+1 < $this->len) && ($this->data{$this->pos+1} == ">")){
						$this->pos++;
						return -2; // close xhtml\xml tag.
					}else{
						$this->add_error("tag property cant start with '/'");
					}
				}
				return $this->data{$this->pos};
			}
			$this->pos++;
		}
		$this->add_error("string end before prop start");
		return 0;
	}
	
	
	// this function is used to find out if the property name\value have closed
	// return -2 on simple close
	// return -1 on '/' style (xhtml\xml) close
	// return 0 on unexpected string end & generating an error.
	// return character ascii code else.
	function move_to_prop_end(){
		while($this->pos < $this->len){
//debuginfo($this->data{$this->pos});
			if (is_html_trim($this->data{$this->pos})){
				return $this->data{$this->pos};
			}else if($this->data{$this->pos}==">"){
				if($this->data{$this->pos-2} == "/") return -1; // close xhtml\xml tag.
				else return -1; // standard html tag.
			}
			$this->pos++;
		}
		$this->add_error("string end before prop end");
		return 0;
	}
	
	function move_to_propname_end(){
		while($this->pos < $this->len){
//debuginfo($this->data{$this->pos});
			if (is_html_trim($this->data{$this->pos})||$this->data{$this->pos}=="="){
				return $this->data{$this->pos};
			}else if($this->data{$this->pos}==">"){
				if($this->data{$this->pos-1} == "/") return -2; // close xhtml\xml tag.
				else return -1; // standard html tag.
			}
			$this->pos++;
		}
		$this->add_error("string end before prop end");
		return 0;
	}
	
	function move_to_next_notrim(){
		while($this->pos < $this->len){
			if (!is_html_trim($this->data{$this->pos})){
				return $this->data{$this->pos};
			}
			$this->pos++;
		}
		return ($this->pos < $this->len)?$this->data{$this->pos}:false;
	}
	
	function move_to_next_trim(){
		while($this->pos < $this->len){
			if (is_html_trim($this->data{$this->pos})){
				return $this->data{$this->pos};
			}
			$this->pos++;
		}
		$this->add_error("string end before trim");
		return 0;
	}
	
}

//convert to macro
function is_html_trim($p){
	if ($p==" "||$p=="\n"||$p=="\r"||$p=="\t"||$p=="\v"|| $p=="\0") return true;
	else return false;
}
?>