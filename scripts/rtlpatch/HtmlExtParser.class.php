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
			if(isset($this->ATE[$tmp+6]["data"])){
				$this->ATE[$tmp+6]["data"] = "<span dir=ltr>".$this->ATE[$tmp+6]["data"];
				$this->ATE[$tmp+10]["data"] .= "</span>";
			} else {
				//TODO: find exceptions (on the stream part there are some)	
			}
		}
        $tmp=0;
        while ($tmp = $this->get_element_id_by_rule(array("tag"=>"b","properties"=>array("class","function"),"offset"=>($tmp+1)))){
            $this->ATE[$tmp]["dir"] = "ltr";
            $this->add_nbsp($tmp,$tmp+3);
        }

        //fix systemitem:
        $tmp=0;
        while($tmp = $this->get_element_id_by_rule(array("tag"=>"span","properties"=>array("class","systemitem"),"offset"=>($tmp+1)))){
              $this->ATE[$tmp-1]["dir"] = "ltr";
        } while($tmp);

		//fix filenames,varnames,userinput,configure options:
		$tmp=0;
		while($tmp = $this->get_element_id_by_rule(array("method"=>"prop_or_comp",
                                                           "tag"=>"tt",
                                                           "properties"=>array("class","filename",
                                                                                "class","varname",
                                                                                "class","userinput",
                                                                                "class","literal",
                                                                                "class","option"),
                                                           "offset"=>($tmp+1)))){
			$this->ATE[$tmp]["dir"] = "ltr";
            $this->add_nbsp($tmp,$tmp+3);
		}

        //fix consts, parameter:
        $tmp=0;
        while($tmp = $this->get_element_id_by_rule(array("method"=>"prop_or_comp",
                                                          "tag"=>"tt",
                                                          "properties"=>array("class","constant",
                                                                               "class","parameter"),
                                                          "offset"=>($tmp+1)))){
            $this->ATE[$tmp]["dir"] = "ltr";
            $this->add_nbsp($tmp,$tmp+5);
        }

		//fix warnning boxes:
		$tmp=0;
		while($tmp = $this->get_element_id_by_rule(array("tag"=>"div","properties"=>array("class","warning"),"offset"=>($tmp+1)))){
			$cond = array("tag"=>"td","properties"=>array("align","LEFT"),"offset"=>($tmp+1));
			if (($td = $this->get_element_id_by_rule($cond)) && ($td<$tocend = $this->ECE[$tmp])){
				$this->ATE[$td]["align"] = "right";
			}
		}

        //fix th:
        $tmp=0;
        while($tmp = $this->get_element_id_by_rule(array("tag"=>"th","offset"=>($tmp+1)))){
              $this->ATE[$tmp]["align"] = "right";
        }

		//fix caution boxes:
		$tmp=0;
		while($tmp = $this->get_element_id_by_rule(array("tag"=>"div","properties"=>array("class","caution"),"offset"=>($tmp+1)))){
			$cond = array("tag"=>"td","properties"=>array("align","LEFT"),"offset"=>($tmp+1));
			if (($td = $this->get_element_id_by_rule($cond)) && ($td<$tocend = $this->ECE[$tmp])){
				$this->ATE[$td]["align"] = "right";
			}
		}

		//fix for TOC
		if($tmp = $this->get_element_id_by_rule(array("tag"=>"div","properties"=>array("class","TOC"),"offset"=>(0)))){
			$tocend = $this->ECE[$tmp];
			while (($tmp = $this->get_element_id_by_rule(array("tag"=>"a","offset"=>($tmp+1))))&&($tmp<$tocend)){
				$this->ATE[$tmp]["dir"] = "rtl";
			}
		}

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

    //parent is the element that open before current location and close after:
    function get_parent($id){
         for($a=$id-1;$a>0;$a--){
             if($this->ECE[$a]>$id) {
                return $a;
             }
         }
         return false;
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
				$chaintoend = $chaintoclose = "";
				foreach ($this->ATE[$a] as $key=>$value){
					if($key == "chaintoend") $chaintoend = $value;
					else if ($key == "chaintoclose") $chaintoclose = $value;
					else $ret[$a].=" $key=\"$value\"";
				}
				$ret[$a].=">$chaintoend";

				if($this->ECE[$a]!=$a) $ret[$this->ECE[$a]] .= " </$tag>$chaintoclose";
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
        if(!isset($method)) $method = "prop_and_comp";

		if(!isset($this->EBT[$tag])) return false;

		$cnt = count($this->EBT[$tag]);

		for($a=0;$a<$cnt;$a++){
			$elem = $this->EBT[$tag][$a];
			if($elem < $offset) continue;
			if($properties && !$this->$method($this->ATE[$elem],$properties)) continue;

			if($index--) continue;
			return $elem;
		}

		return false;
	}

    //compare properties with and rule
    function prop_and_comp(&$elem,&$properties){
        for($a=0;$a<count($properties);$a+=2){
            if(!isset($elem[$properties[$a]])
                || ($elem[$properties[$a]]!=$properties[$a+1])){
                return false;
            }
        }
        return true;
    }

    //compare properties with or rule
    function prop_or_comp(&$elem,&$properties){
        for($a=0;$a<count($properties);$a+=2){
            if(isset($elem[$properties[$a]]) && ($elem[$properties[$a]]==$properties[$a+1])){
                return true;
            }
        }
        return false;
    }

	function change_tag_type($id,$newtag){
		$this->ATE[$id]["w4htype"] = $newtag;
		$this->ATE[$this->ECE[$id]]["w4htype"] = $newtag;

		if($newtag < __HTML_UNKNOWN__){
			if(!isset($this->ATE[$id]["data"])) $this->ATE[$id]["data"] = "";
		}

		//TODO: update the EBT
	}

    //this function used to add &nbsp; before texts to avoid joining of ltr const funcname etc, to the follow text:
    function add_nbsp($id, $ordid){
          if(isset($this->ATE[$ordid]["data"]{0})){
                $ord = ord($this->ATE[$ordid]["data"]{0});
                //if text not before punctuation marks (without space, and brackets), add &nbsp; to eliminate the align issue came with dir=rtl:
                if($ord>65||$ord==32||$ord==40){
                    $this->ATE[$id]["chaintoclose"] = "&nbsp;";
               }
          }
    }

	//\/\/\/\/\/\/\/\/\/\
	function unsetme(){
		unset($this->data);
		unset($this->ATH);
		unset($this->EC);
		unset($this->EBT);
	}
}