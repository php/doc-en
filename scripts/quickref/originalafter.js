'; //'

// Initialisation --------------------------------------------------------------

// how many matches to show at most (must be even!)
fh_showmatches=29;

var _d=document;
var isnotopera=true;

fh_matches=new Array();
fh_inmenu=fh_menupos=0;
fh_matchesjoined="";
fh_currenttext="";

var f_p=_d.forms[0].pattern;
var f_s=_d.forms[0].show;

// Layer setup -----------------------------------------------------------------

if (_d.all && (isnotopera=(navigator.userAgent.toLowerCase().indexOf("opera")==-1))) {
    width="width:165px";
} else {
    isnotopera=true;
    width="min-width:155px";
}
_d.write("<div id=\"funchelper\" style=\"background-color: white; border: 1px solid black; top: 90px;"+width+"; padding: 4px; font-size: 9px; display:none; position:absolute;\"></div>");

// Decompression ---------------------------------------------------------------

dcp=dcp.split("}");
for(a=0; a<dcp.length; a++) {
    cpd=cpd.split(dcp[a].charAt(0)).join(dcp[a].substring(1,9));
}
function l(a) { return a[a.length-1]; }

fcl=new Array(); pst=new Array(""); ac="";
for (pos=0; pos<cpd.length; pos++) {
    switch (ch=cpd.charAt(pos)) {
        case ',': fcl.push(l(pst)+ac); ac=""; break;
        case '(': pst.push(l(pst)+ac); ac=""; break;
        case ')': case ']': if(ac.length)fcl.push(l(pst)+ac); ac=""; pst.pop(); break;
        case '[': fcl.push(a=l(pst)+ac); pst.push(a); ac=""; break;
        default: ac=ac+ch;
    }
}

// Functions -------------------------------------------------------------------

function ElLeft(eE)
{
    var DL_bIE=_d.all?true:false;
    var nLP=eE.offsetLeft;
    var ePE=eE.offsetParent;
    while (ePE!=null) {
        if (DL_bIE) {
            if (ePE.tagName=="TD") nLP+=ePE.clientLeft;
        } else {
            if (ePE.tagName=="TABLE") {
                var nPB=parseInt(ePE.border);
                if (isNaN(nPB)) {
                    var nPF=ePE.getAttribute('frame');
                    if (nPF!=null) nLeftPos+=1;
                } else if (nPB>0) nLP+=nPB;
            }
        }
        nLP+=ePE.offsetLeft;
        ePE=ePE.offsetParent;
    }
    return nLP;
}

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
        if (fcl[m]<pr) f=m; else l=m;
        nm=(f+l+1)>>1;
        if (m==nm) break;
        m=nm;
    }
    if (m&&fh_IsMatch(m-1,pr)) m--;
    if (!fh_IsMatch(m,pr) && m<(fcl.length-1) && fh_IsMatch(m+1,pr)) m++;
    res=new Array;
    while (m<fcl.length && fh_IsMatch(m,pr)) res.push(fcl[m++]);
    return res;
}

function fh_Show(what)
{
    tdv=_d.getElementById("funchelper");
    ts=tdv.style;
    if (what=="") {
        if (ts.display!="none") ts.display="none";
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
    fh_inmenu=fh_menupos=0;
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
        beforedots=first=afterdots=0;
        last=flen-1;
    } else {
        if (fh_inmenu) {
            mid=fh_showmatches>>1;
            if (fh_menupos<=mid) beforedots=first=0;
            else {
                beforedots=1;
                first=fh_menupos-mid+1;
                if (first>(flen-fh_showmatches+1)) first=flen-fh_showmatches+1;
            }
            if (fh_menupos>=(flen-mid-1)) {
                afterdots=0;
                last=flen-1;
            } else {
                afterdots=1;
                last=fh_menupos+mid-1;
                if (last<(fh_showmatches-2)) last=fh_showmatches-2;
            }
        } else {
            first=beforedots=0;
            last=fh_showmatches-2;
            afterdots=1;
        }
    }
    zh="";
    if (beforedots) zh=zh+"...<br />";
    for (pos=first; pos<=last; pos++) {
        f=fh_matches[pos];
        zh=zh+"<a href=\"/"+f+
              "\" style=\"text-decoration:none;"+
              (fh_inmenu&&fh_menupos==pos?"background-color:rgb(204,204,255);":"")+"\">"+f+"</a><br />";
    }
    if (afterdots) zh=zh+"...";
    fh_Show(zh);
    if (fh_inmenu) f_p.value=fh_matches[fh_menupos];
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
    fh_inmenu=fh_menupos=0;
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

function fh_EBlur() { setTimeout("fh_EBlurT()", 200); }
function fh_EBlurT() { f_p.autocomplete="on"; fh_HideAll(); }

function fh_EKeyPress(ev)
{
    ev=ev||event||null;
    if (f_s.value=="quickref"&&ev) {
        var cc=(ev.charCode||ev.charCode==0)?ev.charCode:ev.keyCode||ev.which;
        if (cc==32) { // spacebar autocomplete 
            if ((p=f_p.value)=="") return false;
            matches=fh_FindMatches(p);
            if (matches.length==0) return false;
            if (matches.length==1) { // full autocomplete in case of single match
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
        //if ((cc>=97&&cc<=122)||(cc>=65&&cc<=90)||(cc>=48&&cc<=57)||cc==95) return true; // a-z A-Z 0-9 _
    }
    return true;
}

function fh_EKeyDown(ev)
{
    ev=ev||event||null;
    if (f_s.value=="quickref"&&ev) {
        var cc=ev.charCode||ev.keyCode||ev.which;
        if (cc==38||cc==57385) { // up
            if (fh_inmenu) {
                if (--fh_menupos<0) fh_menupos=fh_matches.length-1;
            } else {
                fh_inmenu=1;
                fh_menupos=fh_matches.length-1;
            }
            fh_UpdateMenu();
            return false;
        }
        if (cc==40||cc==57386) { // down
            if (fh_inmenu) {
                if (++fh_menupos>=fh_matches.length) fh_menupos=0;
            } else {
                fh_inmenu=1;
                fh_menupos=0;
            }
            fh_UpdateMenu();
            return false;
        }
    }
    return true;
}

function fh_EKeyUp(ev)
{
    ev=ev||event||null;
    if (f_s.value=="quickref"&&ev) {
        var cc=ev.charCode||ev.keyCode||ev.which;
        if (cc==38||cc==40||cc==57385||cc==57386) return false;
        if (f_p.value!=fh_currenttext) {
            fh_currenttext=f_p.value;
            fh_NewText();
        }
    }
    return true;
}

// Event listener setup --------------------------------------------------------

f_p.onkeypress=fh_EKeyPress;
f_p.onfocus=fh_EFocus;
f_p.onblur=fh_EBlur;
f_p.onkeydown=fh_EKeyDown;
f_p.onkeyup=fh_EKeyUp;
