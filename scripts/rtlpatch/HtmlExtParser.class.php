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

class CHtmlExtParse extends CHtmlParse{
	
	function CHtmlExtParse($data){
		// make the html compatible to the parser:
		$data = str_replace("\r\n>",">",$data);
		$data = str_replace("\n>",">",$data);
		
		$this->CHtmlParse($data);
	}
	
	function fix_hebrew(){
		global $EHType,$HEType;
		$cnt = count($this->ATE);
		
		// fix functions '()':
		if($tmp = $this->get_element_id_by_rule(array("tag"=>"div","properties"=>array("class","refsect1")))){
			$this->ATE[$tmp+6]["data"] = "<span dir=ltr>".$this->ATE[$tmp+6]["data"];
			$this->ATE[$tmp+10]["data"] .= "</span>";
		}
		$b = $HEType["b"];
		if(isset($this->EBT[$b])){
			$ar = array("class","function");
			for($a=0;$a<count($this->EBT[$b]);$a++){
				$elem = &$this->ATE[$this->EBT[$b][$a]];
				if($this->comp_properties($elem,$ar)){
					$elem["dir"] = "ltr";
					if(isset($this->ATE[$this->EBT[$b][$a]+1]["data"])){
						$this->ATE[$this->EBT[$b][$a]+1]["data"] = "&nbsp;".$this->ATE[$this->EBT[$b][$a]+1]["data"];
					}
				}
			}
		}
		
		//fix consts:
		$tmp=0;
		do{
			if($tmp = $this->get_element_id_by_rule(array("tag"=>"tt","properties"=>array("class","constant"),"offset"=>($tmp+1)))){
				$this->ATE[$tmp-1]["dir"] = "ltr";
			}
		} while($tmp);
		
		//ltr literals:
		$tmp=0;
		do{
			if($tmp = $this->get_element_id_by_rule(array("tag"=>"tt","properties"=>array("class","literal"),"offset"=>($tmp+1)))){
				$this->ATE[$tmp]["dir"] = "ltr";
				if(isset($this->ATE[$tmp+1]["data"])){
					$this->ATE[$tmp+1]["data"] = "&nbsp;".$this->ATE[$tmp+1]["data"];
				}
			}
		} while($tmp);
		
		//fix configure options:
		$tmp=0;
		do{
			if($tmp = $this->get_element_id_by_rule(array("tag"=>"tt","properties"=>array("class","option"),"offset"=>($tmp+1)))){
				$this->ATE[$tmp]["dir"] = "ltr";
			}
		} while($tmp);
		
		/*
		//fix for TOC
		$tmp=0;
		do{
			if($tmp = $this->get_element_id_by_rule(array("tag"=>"div","properties"=>array("class","TOC"),"offset"=>($tmp+1)))){
				$this->ATE[$tmp]["dir"] = "ltr";
			}
		} while($tmp);
		*/
		
		//rtl all the divs
		$div = $HEType["div"];
		if(isset($this->EBT[$div])){
			for($a=0;$a<count($this->EBT[$div]);$a++){
				$this->ATE[$this->EBT[$div][$a]]["dir"] = "rtl";
			}
		}
		
		// fix the embeded php code:
		$pre = $HEType["pre"];
		if(isset($this->EBT[$pre])){
			for($a=0;$a<count($this->EBT[$pre]);$a++){
				$this->ATE[$this->EBT[$pre][$a]]["dir"] = "ltr";
			}
		}
		
		//fix the meta:
		$meta = $HEType["meta"];
		if(isset($this->EBT[$meta])){
			for($a=0;$a<count($this->EBT[$meta]);$a++){
				$elem = &$this->ATE[$this->EBT[$meta][$a]];
				if(isset($elem["http-equiv"]) && $elem["http-equiv"]=="Content-type")
					$elem["content"] = "text/html; charset=WINDOWS-1255";
			}
		}
	}
	
	function get(){
		global $EHType,$HEType;
		
		$cnt = count($this->ATE);
		
		for($a=0;$a<$cnt;$a++){
			$ret[$a] = "";	
		}
		
		for($a=1;$a<$cnt;$a++){
			$tg = array_shift($this->ATE[$a]);
			if($tg<0) continue;
			if($tg>9)  {
				$tag = $EHType[$tg];
				$ret[$a] .= "<$tag";
				foreach ($this->ATE[$a] as $key=>$value){
					$ret[$a].=" $key=\"$value\"";
				}
				$ret[$a].=">";
				
				if($this->ECE[$a]!=$a) $ret[$this->ECE[$a]] .= " </$tag>";
			} else if($tg == __HTML_PROCESS__){
					$ret[$a].="<".$this->ATE[$a]["data"]. "\n?>";
			} else if($tg < __HTML_UNKNOWN__){
					$ret[$a].=" ".$this->ATE[$a]["data"]." ";	
			}
//mysyslog($ret[$a]);
		}
		return implode($ret,"\r\n");
		
	}
	
	
	// return element ATE id by:
	// tag, property, offset on ATE and index (mean, offset on EBT[tag])
	function get_element_id_by_rule($param){
		global $EHType,$HEType;
		
		extract($param);
		
		$tag = $HEType[$tag];
		if(!isset($properties)) $properties = false;
		if(!isset($offset)) $offset = 0;
		if(!isset($index)) $index = 0;
		
		if(!isset($this->EBT[$tag])) return false;
		
		$cnt = count($this->EBT[$tag]);
		
		for($a=0;$a<$cnt;$a++){
			$elem = $this->EBT[$tag][$a];
			if($elem < $offset) continue;
			if($properties && !$this->comp_properties($this->ATE[$elem],$properties)) continue;

			if($index--) continue;
			return $elem;
		}
		
		return false;
	}
	
	function comp_properties(&$elem,&$properties){
		for($a=0;$a<count($properties);$a+=2){
			if(!isset($elem[$properties[$a]])
				|| ($elem[$properties[$a]]!=$properties[$a+1])){
				return false;
			}
		}
		return true;
	}
	
	//\/\/\/\/\/\/\/\/\/\
	function unsetme(){
		unset($this->data);
		unset($this->ATH);
		unset($this->EC);
		unset($this->EBT);
	}
}