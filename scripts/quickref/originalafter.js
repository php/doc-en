';

// CONFIGURATION

// how many matches to show at most, must be even
fh_showmatches=29;

// END CONFIGURATION

var _d=document;
var isnotopera=true;

if (_d.all && (isnotopera=(navigator.userAgent.toLowerCase().indexOf("opera")==-1))) {
	width="width:165px";
} else {
	isnotopera=true;
	width="min-width:155px";
}
_d.write("<div id=funchelper style=\"background-color: white; border: 1px solid black; top: 90px;"+width+"; padding: 4px; font-size: 9px; display:none; position:absolute;\"></div>");

// DECOMPRESSION
dcp=dcp.split("}");for(a=0;a<dcp.length;a++){cpd=cpd.split(dcp[a].charAt(0)).join(dcp[a].substring(1,9));}
function l(a) { return a[a.length-1]; }

fcl=new Array();pst=new Array("");ac="";
for (pos=0;pos<cpd.length;pos++){switch(ch=cpd.charAt(pos)){case ',':fcl.push(l(pst)+ac);ac="";break;
case '(':pst.push(l(pst)+ac);ac="";break;case ')':case ']':if(ac.length)fcl.push(l(pst)+ac);ac="";pst.pop();break;
case '[':fcl.push(a=l(pst)+ac);pst.push(a);ac="";break;default:ac=ac+ch;}}
// END DECOMPRESSION

function ElLeft(eE)
{var DL_bIE=_d.all?true:false;var nLP=eE.offsetLeft;var ePE=eE.offsetParent;
while(ePE!=null){if(DL_bIE){if(ePE.tagName=="TD")nLP+=ePE.clientLeft;}else{if(ePE.tagName=="TABLE"){
var nPB=parseInt(ePE.border);if(isNaN(nPB)){var nPF=ePE.getAttribute('frame');if(nPF!=null)nLeftPos+=1;}else
if(nPB>0)nLP+=nPB;}}nLP+=ePE.offsetLeft;ePE=ePE.offsetParent;}return nLP;}

fh_matches=new Array();
fh_inmenu=0;
fh_menupos=0;
fh_matchesjoined="";
fh_currenttext="";

var f_p=_d.forms[0].pattern;
var f_s=_d.forms[0].show;

function fh_IsMatch(idx,pr)
{
	if (fcl[idx].substring(0,pr.length)==pr) return true;
	return false;
}

function fh_FindMatches(pr)
{
	f=0;
	l=fcl.length-1;
	m=(f+l)>>1;
	while(f<l) {
		if (fcl[m]==pr) break;
		if (fcl[m]<pr) {
			f=m;
		} else {
			l=m;
		}
		nm=(f+l+1)>>1;
		if (m==nm) break;
		m=nm;
	}
	if (m&&fh_IsMatch(m-1,pr)) m--;
	if (!fh_IsMatch(m,pr) && m<(fcl.length-1) && fh_IsMatch(m+1,pr)) m++;
	res=new Array;
	while (m<fcl.length && fh_IsMatch(m,pr)) {
		res.push(fcl[m++]);
	}
	return res;
}

function fh_Show(what)
{
	tdv=_d.getElementById("funchelper");
	ts=tdv.style;
	if (what=="") {
		if (ts.display!="none")
			ts.display="none";
	} else {
		ts.display="block";
		ts.left=ElLeft(f_p)+"px";
		tdv.innerHTML=what;
	}
}

function fh_HideAll()
{
	fh_matches=new Array();
	fh_matchesjoined="";
	fh_inmenu=0;
	fh_menupos=0;
	fh_Show("");
}


function fh_ShowNoMatch()
{
	fh_Show("<font color=\"gray\">No such function</font>");
}

function fh_UpdateMenu()
{
	flen=fh_matches.length;
	if (flen<=fh_showmatches) {
		first=0;
		last=flen-1;
		beforedots=0;
		afterdots=0;
	} else {
		if (fh_inmenu) {
			mid=fh_showmatches>>1;
			if (fh_menupos<=mid) {
				beforedots=0;
				first=0;
			} else {
				beforedots=1;
				first=fh_menupos-mid+1;
				if (first>(flen-fh_showmatches+1))
					first=flen-fh_showmatches+1;
			}
			if (fh_menupos>=(flen-mid-1)) {
				afterdots=0;
				last=flen-1;
			} else {
				afterdots=1;
				last=fh_menupos+mid-1;
				if (last<(fh_showmatches-2))
					last=fh_showmatches-2;
			}
		} else {
			first=0;
			last=fh_showmatches-2;
			beforedots=0;
			afterdots=1;
		}
	}

	zh="";
	if (beforedots)
		zh=zh+"...<br />";
	for (pos=first; pos<=last; pos++) {
		f=fh_matches[pos];
		zh=zh+"<a href=\"/"+f+"\" style=\"text-decoration:none;"+(fh_inmenu&&fh_menupos==pos?"background-color:rgb(204,204,255);":"")+"\">"+f+"</a><br />";
	}
	if (afterdots)
		zh=zh+"...";

	fh_Show(zh);
	if (fh_inmenu) {
		f_p.value=fh_matches[fh_menupos];
	}
}

function fh_NewText()
{
	t=f_p.value;
	if (t=="") {
		fh_HideAll();
		return;
	}
	tmpmatches=fh_FindMatches(t);
	if (tmpmatches.length==0) {
		fh_matchesjoined="";
		fh_ShowNoMatch();
		return;
	}
	if (tmpmatches.join(",")==fh_matchesjoined) return; // do nothing
	fh_inmenu=0;
	fh_menupos=0;
	fh_matchesjoined=tmpmatches.join(",");
	fh_matches=tmpmatches;

	fh_UpdateMenu();
}

function fh_EFocus()
{
	if (f_s.value=="quickref") {
		f_p.autocomplete="off";
		fh_NewText();
	}
}

function fh_EBlur()
{
	f_p.autocomplete="on";
	fh_HideAll();
}

function fh_EKeyPress(evt)
{
	if (f_s.value!="quickref") return true;
	evt=(evt)?evt:((event)?event:null);
	if (!evt) return true;
	var charCode=(evt.charCode || evt.charCode==0)?evt.charCode:((evt.keyCode)?evt.keyCode:evt.which);
	if (charCode==32) {
		p=f_p.value;
		if (p=="") return false;
		matches=fh_FindMatches(p);
		if (matches.length==0) return false;
		if (matches.length==1) {
			f_p.value=matches[0];
			return false;
		}
		if (isnotopera) {
			len=0;
			first=matches[0];
				last=matches.pop();
			while (len<first.length && first.substring(0,len+1)==last.substring(0,len+1)) len++;
			if (f_p.value!=first.substring(0,len)) {
				f_p.value=first.substring(0,len);
			}
		}
		return false;
	}
	if ((charCode>=97 && charCode<=122) || (charCode>=65 && charCode<=90) || (charCode>=48 && charCode<=57) || charCode==95)
		return true; // a-z A-Z 0-9 _

	return true;
}


function fh_EKeyDown(evt)
{
	if (f_s.value!="quickref") return true;
	evt=(evt)?evt:((event)?event:null);
	if (!evt) return true;
	var charCode=evt.charCode?evt.charCode:((evt.keyCode)?evt.keyCode:evt.which);

	if (charCode==38 || charCode==57385) { // up
		if (fh_inmenu) {
			fh_menupos--;
			if (fh_menupos<0) {
				fh_menupos=fh_matches.length-1;
			}
		} else {
			fh_inmenu=1;
			fh_menupos=fh_matches.length-1;
		}
		fh_UpdateMenu();
		return false;
	} else
	if (charCode==40 || charCode==57386) { // down
		if (fh_inmenu) {
			fh_menupos++;
			if (fh_menupos>=fh_matches.length)
				fh_menupos=0;
		} else {
			fh_inmenu=1;
			fh_menupos=0;
		}
		fh_UpdateMenu();
		return false;
	}
	return true;
}

function fh_EKeyUp(evt)
{
	if (f_s.value!="quickref") return true;
	evt=(evt)?evt:((event)?event:null);
	if (!evt) return true;
	var charCode=evt.charCode?evt.charCode:((evt.keyCode)?evt.keyCode:evt.which);
	if (charCode==38 || charCode==40 || charCode==57385 || charCode==57386) return false;
	if (f_p.value!=fh_currenttext) {
		fh_currenttext=f_p.value;
		fh_NewText();
	}
	return true;
}

f_p.onkeypress=fh_EKeyPress;
f_p.onfocus=fh_EFocus;
f_p.onblur=fh_EBlur;
f_p.onkeydown=fh_EKeyDown;
f_p.onkeyup=fh_EKeyUp;
