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
	
	function get_hebrew(){
		global $EHType,$HEType;
		
		$cnt = count($this->ATE);
		
		for($a=0;$a<$cnt;$a++){
			$ret[$a] = "";	
		}
		
		// if there is hebrew on the page, we make rtl default
		$wrong_mode = "rtl";
		$deflang = __HTML_FREE_ENGLISH__;
		$html = $HEType["html"];
		if(isset($this->EBT[__HTML_FREE_HEBREW__])
				&& isset($this->EBT[$html])){
			$this->ATE[$this->EBT[$html][0]]["dir"] = "rtl";
			$wrong_mode = "ltr";
			$deflang = __HTML_FREE_HEBREW__;
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
				$this->ATE[$this->EBT[$meta][$a]]["content"] = "text/html; charset=WINDOWS-1255";
			}
		}
		
		for($a=1;$a<$cnt;$a++){
			$tg = array_shift($this->ATE[$a]);
			if($tg<0) continue;
			if($tg>4)  {
				$tag = $EHType[$tg];
				$ret[$a] .= "<$tag";
				foreach ($this->ATE[$a] as $key=>$value){
					$ret[$a].=" $key='$value'";
				}
				$ret[$a].=">";
				
				if($this->ECE[$a]!=$a) $ret[$this->ECE[$a]] .= " </$tag>";
			} else {
				if($tg != $deflang) {
//					if($a>1 && isset($ret[$a+1]) && ($this->ECE[$a-1] = $a+1)) {
//						//hack; remove later try to encapsulate the parent:
//						$ret[$a-1]="<span dir=$wrong_mode>".$ret[$a-1];
//						$ret[$a].= $this->ATE[$a]["data"];
//						$ret[$a+1]="</span>".$ret[$a+1];
//					} else {
						// regular case:
						$ret[$a].="<span dir=$wrong_mode>{$this->ATE[$a]["data"]}</span>";
//					}
					
				} else {
					$ret[$a].=$this->ATE[$a]["data"];	
				}			
			}
			
			
		}
		return implode($ret,"\r\n");
		
	}
	
	function unsetme(){
		unset($this->data);
		unset($this->ATH);
		unset($this->EC);
		unset($this->EBT);
	}
}