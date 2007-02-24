<?xml version="1.0"?>
<!-- 

  HTML Help specific stylesheet

  $Id: htmlhelp.xsl,v 1.3 2007-02-24 22:59:43 philip Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:doc="http://nwalsh.com/xsl/documentation/1.0"
                xmlns:exsl="http://exslt.org/common"
                xmlns:set="http://exslt.org/sets"
		version="1.0"
                exclude-result-prefixes="doc exsl set">
<!-- 
  xCHM HTMLHELP customizations include:
  - output directory for HTML and project Help files is 'htmlhelp/html/'
  - open ulinks in _blank window
  - CHM buttons settings
  - custom .hhk file with index.html split in two files (titlepage and toc)
  - custom .hhc file with same changes
  - .hhk and .hhc are processed in html mode unlike native DocBook XSL templates
  - proper escaping in .hhk and .hhc to reflect transition from text to html mode

  - DOCTYPE in output HTML defines DOM standard for browser to handle JS correctly
  - strip <link> tags from HTML headers
  - add javascript handlers in body attributes
  - add root DHTML div with id ="PageContent" for skinning purposes
  - header off, footer on (also custom with some js handlers and custom ids)

  - turn on function index page building (in appendices) and turn off ToC for it
  - output formal object titles enclosed in <h3> tags 
    (abstract title, examples, tables, ...?)
  - simple bold text for admonitions (note|important|warning|caution|tip)
  - custom tables attributes
  - special reference page - drop out "description" title, proper rendering for 
    "seealso", move <refname> along with function prototype to page header and
    render it appropriately
    TODO: need convenient reference page skeleton

    NOTE: custom HTML xCHM layout described in phpdoc/en/chmonly/skins.xml or 
          http://wiki.phpdoc.info/xCHM
-->

<!-- - BASED ON 1.66.1 HTMLHELP.XSL DOCBOOK XSL STYLESHEET - -->

<xsl:import href="../../docbook/docbook-xsl/html/chunk.xsl"/>
<xsl:import href="../../docbook/docbook-xsl/htmlhelp/htmlhelp-common.xsl"/>
<xsl:import href="common.xsl"/>

<!-- configure/able/ parameters -->
<xsl:include href="htmlhelp-config.xsl"/>

<!-- we use CSS styling to make verbatim sections look nicy -->
<xsl:param name="shade.verbatim" select="0"/>

<xsl:param name="chunker.output.doctype-system" select="'http://www.w3.org/TR/html4/loose.dtd'"/>
<xsl:param name="chunker.output.doctype-public" select="'-//W3C//DTD HTML 4.01 Transitional//EN'"/>

<xsl:param name="base.dir" select="'htmlhelp/html/'"/>
<!-- project files for HTML Help are written into base.dir instead of current directory -->
<xsl:param name="manifest.in.base.dir" select="1"/>
<xsl:param name="use.id.as.filename" select="1"/>
<xsl:param name="chunk.quietly" select="1"/>

<!-- Supress the ",figure,example,equation" (like DSSSL output). -->
<xsl:param name="generate.toc">
appendix  toc,title
article   toc
book      toc,title
chapter   toc,title
part      toc,title
preface   toc
qandadiv  toc
qandaset  toc
reference toc,title
sect1     toc
sect2     toc
sect3     toc
sect4     toc
sect5     toc
section   toc
set       toc
</xsl:param>

<!-- Target window for external ulinks -->
<xsl:param name="ulink.target" select="'_blank'"/>


<!-- *************** HTML HELP PROJECT PARAMETERS **************** -->

<!-- Turn off Binary TOC used Prev/Next buttons on toolbar. Files with binary TOC can't be merged -->
<xsl:param name="htmlhelp.hhc.binary" select="0"/>
<xsl:param name="htmlhelp.generate.index" select="1"/>
<xsl:param name="htmlhelp.use.hhk" select="1"/>
<!-- <xsl:param name="htmlhelp.use.hhk" select="1"/> doesn't work -->

<xsl:param name="htmlhelp.display.progress" select="0"/>
<xsl:param name="htmlhelp.default.topic" select="'_index.html'"/>
<xsl:param name="htmlhelp.hhp.window" select="'phpdoc'"/>
<xsl:param name="htmlhelp.enhanced.decompilation" select="1"/>

<xsl:param name="htmlhelp.hhc.folders.instead.books" select="0"/>
<xsl:param name="htmlhelp.hhc.show.root" select="0"/>


<!-- <xsl:with-param name="xnavigation" select="'0x23520'"/> -->
<xsl:param name="htmlhelp.show.menu" select="0"/>
<xsl:param name="htmlhelp.show.toolbar.text" select="1"/>
<xsl:param name="htmlhelp.show.advanced.search" select="1"/>
<xsl:param name="htmlhelp.show.favorities" select="1"/>

<!-- <xsl:with-param name="xbuttons" select="'0x386e'"/> -->
<!-- 0x4387E with additional stop and php.net buttons    -->
<xsl:param name="htmlhelp.button.hideshow" select="1"/>
<xsl:param name="htmlhelp.button.locate" select="1"/>
<xsl:param name="htmlhelp.button.back" select="1"/>
<xsl:param name="htmlhelp.button.forward" select="1"/>
<!-- next two buttons are questionable -->
<xsl:param name="htmlhelp.button.stop" select="1"/>
<xsl:param name="htmlhelp.button.refresh" select="1"/>
<xsl:param name="htmlhelp.button.home" select="1"/>
<xsl:param name="htmlhelp.button.options" select="1"/>
<xsl:param name="htmlhelp.button.print" select="1"/>
<!-- next jump button can be discussed too -->
<!-- <xsl:param name="htmlhelp.button.jump1" select="1"/>
<xsl:param name="htmlhelp.button.jump1.url" select="'http://www.php.net'"/>
<xsl:param name="htmlhelp.button.jump1.title" select="'PHP.NET'"/>
FIX: hhc.exe chokes on this link.. need additional redirection page -->
<xsl:param name="htmlhelp.button.next" select="0"/>
<xsl:param name="htmlhelp.button.prev" select="0"/>
<xsl:param name="htmlhelp.button.zoom" select="0"/>



<!-- *************** HTML HELP INDEX CUSTOMIZINGS (HHK) **************** -->

<!-- compile custom index file (.hhk) and insert two additional files into
     index structure. These will be created later by splitting result title
     page with contents in two -->
<xsl:template match="book" mode="hhk">
  <xsl:variable name="title">
    <xsl:apply-templates select="." mode="title.markup"/>
  </xsl:variable>
  <xsl:variable name="bookhref">
    <xsl:call-template name="href.target"/>
  </xsl:variable>
  <xsl:variable name="toctitle">
    <xsl:call-template name="gentext">
      <xsl:with-param name="key" select="'TableofContents'"/>
    </xsl:call-template>
  </xsl:variable>
  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($title)}"/>
    <param name="Local" value="_index.html"/>
  </object></li>
  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($toctitle)}"/>
    <param name="Local" value="{$bookhref}"/>
  </object></li>
  <xsl:apply-templates select="part|preface|chapter|appendix|article|reference|bibliography|colophon"
                       mode="hhk"/>
</xsl:template>

<xsl:template match="part|preface|chapter|appendix|article|reference|refentry
                     |sect1|sect2|sect3|sect4|sect5
                     |section
                     |book/glossary|article/glossary
                     |book/bibliography|article/bibliography
                     |colophon"
              mode="hhk">
  <xsl:variable name="ischunk"><xsl:call-template name="chunk"/></xsl:variable>
  <xsl:if test="$ischunk='1'">
    <xsl:variable name="title">
      <xsl:apply-templates select="." mode="title.markup"/>
    </xsl:variable>
    <xsl:variable name="filename">
      <xsl:call-template name="make-relative-filename">
        <xsl:with-param name="base.dir" select="''"/>
        <xsl:with-param name="base.name">
          <xsl:apply-templates mode="chunk-filename" select="."/>
        </xsl:with-param>
      </xsl:call-template>
    </xsl:variable>
  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($title)}"/>
    <param name="Local" value="{$filename}"/>
  </object></li>
  </xsl:if>

  <xsl:apply-templates select="*" mode="hhk"/>
</xsl:template>

<xsl:template name="hhk">
  <xsl:call-template name="write.chunk">
    <xsl:with-param name="filename">
      <xsl:if test="$manifest.in.base.dir != 0">
        <xsl:value-of select="$base.dir"/>
      </xsl:if>
      <xsl:value-of select="$htmlhelp.hhk"/>
    </xsl:with-param>
    <xsl:with-param name="indent" select="'yes'"/>
    <xsl:with-param name="content"><xsl:text disable-output-escaping="yes"><![CDATA[<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<HTML>
<HEAD>
<meta name="GENERATOR" content="Microsoft&reg; HTML Help Workshop 4.1">
<!-- Sitemap 1.0 -->
</HEAD><BODY>
<OBJECT type="text/site properties">
        <param name="Window Styles" value="0x800227">
</OBJECT>
<UL>]]>
</xsl:text>
<xsl:if test="($htmlhelp.use.hhk != 0) and $htmlhelp.generate.index">
  <xsl:choose>
    <xsl:when test="$rootid != ''">
      <xsl:apply-templates select="key('id',$rootid)" mode="hhk"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates select="/" mode="hhk"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:if>
<xsl:text disable-output-escaping="yes"><![CDATA[</UL>
</BODY></HTML>]]></xsl:text>
    </xsl:with-param>
    <xsl:with-param name="encoding" select="$htmlhelp.encoding"/>
  </xsl:call-template>
</xsl:template>

<!-- escape double quotes in titles to correctly generate .hhk entry
     for example: <title><literal>emply("0")... -->
<xsl:template match="title/literal">
  <xsl:variable name="apos" select="&quot;'&quot;" />
  <xsl:variable name="quot" select='&apos;"&apos;' />
  <xsl:value-of select="translate(.,'&quot;',$apos)"/>
</xsl:template>



<!-- *************** HTML HELP TOC CUSTOMIZINGS (HHC) **************** -->

<!-- compile custom TOC file (.hhc) and insert two additional files into
     contents structure. These will be created later by splitting result title
     page with contents in two -->
<!-- Setup style for TOC window -->
<xsl:template name="hhc-main">
  <xsl:text disable-output-escaping="yes"><![CDATA[<HTML>
<HEAD></HEAD>
<BODY>

<OBJECT type="text/site properties">
      <param name="Window Styles" value="0x800227"/>
]]></xsl:text>
  <xsl:if test="$htmlhelp.hhc.folders.instead.books != 0">
      <param name="ImageType" value="Folder"/>
  </xsl:if>
  <xsl:text disable-output-escaping="yes"><![CDATA[</OBJECT>

<UL>]]></xsl:text>

  <xsl:choose>
    <xsl:when test="$rootid != ''">
      <xsl:apply-templates select="key('id',$rootid)" mode="hhc"/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates select="/" mode="hhc"/>
    </xsl:otherwise>
  </xsl:choose>

  <xsl:text disable-output-escaping="yes"><![CDATA[</UL></BODY>
</HTML>]]></xsl:text>
</xsl:template>

<xsl:template match="book" mode="hhc">
  <xsl:variable name="title">
    <xsl:if test="$htmlhelp.autolabel=1">
      <xsl:variable name="label.markup">
        <xsl:apply-templates select="." mode="label.markup"/>
      </xsl:variable>
      <xsl:if test="normalize-space($label.markup)">
        <xsl:value-of select="concat($label.markup,$autotoc.label.separator)"/>
      </xsl:if>
    </xsl:if>
    <xsl:apply-templates select="." mode="title.markup"/>
  </xsl:variable>
  <xsl:variable name="href">
    <xsl:call-template name="href.target"/>
  </xsl:variable>
  <xsl:variable name="toctitle">
    <xsl:call-template name="gentext">
      <xsl:with-param name="key" select="'TableofContents'"/>
    </xsl:call-template>
  </xsl:variable>

  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($title)}"/>
    <param name="Local" value="_index.html"/>
  </object></li>
  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($toctitle)}"/>
    <param name="Local" value="{$href}"/>
  </object></li>
  <xsl:text>
  </xsl:text>

  <xsl:apply-templates select="part|reference|preface|chapter|bibliography|appendix|article|colophon|glossary"
    		   mode="hhc"/>
</xsl:template>


<!-- [Next template is temporarily until patch to 1.66.1 is approved] -->
<!-- https://sourceforge.net/tracker/index.php?func=detail&aid=1048856&group_id=21935&atid=373749 -->
<xsl:template name="hhc">
  <xsl:call-template name="write.chunk">
    <xsl:with-param name="filename">
      <xsl:if test="$manifest.in.base.dir != 0">
        <xsl:value-of select="$base.dir"/>
      </xsl:if>
      <xsl:value-of select="$htmlhelp.hhc"/>
    </xsl:with-param>
    <xsl:with-param name="indent" select="'yes'"/>
    <xsl:with-param name="content">
      <xsl:call-template name="hhc-main"/>
    </xsl:with-param>
    <xsl:with-param name="encoding" select="$htmlhelp.encoding"/>
  </xsl:call-template>
</xsl:template>

<!-- [Temporarily also until it will be discussed in docbook-apps about features operating
     in html mode - is it neccesary to escape attributes which are already escaped in sources] -->
<!-- Do not escape titles since we are operating in html mode -->
<xsl:template match="part|reference|preface|chapter|bibliography|appendix|article|glossary"
              mode="hhc">
  <xsl:variable name="title">
    <xsl:if test="$htmlhelp.autolabel=1">
      <xsl:variable name="label.markup">
        <xsl:apply-templates select="." mode="label.markup"/>
      </xsl:variable>
      <xsl:if test="normalize-space($label.markup)">
        <xsl:value-of select="concat($label.markup,$autotoc.label.separator)"/>
      </xsl:if>
    </xsl:if>
    <xsl:apply-templates select="." mode="title.markup"/>
  </xsl:variable>
  <xsl:variable name="href">
    <xsl:call-template name="href.target.with.base.dir"/>
  </xsl:variable>

  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($title)}"/>
    <param name="Local" value="{$href}"/>
  </object></li>
  <xsl:text>
  </xsl:text>

  <xsl:if test="reference|preface|chapter|appendix|refentry|section|sect1|bibliodiv">
    <ul>
      <xsl:apply-templates
	select="reference|preface|chapter|appendix|refentry|section|sect1|bibliodiv"
	mode="hhc"/>
    </ul>
  </xsl:if>
</xsl:template>

<!--
<xsl:param name="htmlhelp.only" select="1"/>
htmlhelp.autolabel - chapter and section numbers in ToC - off
-->


<!-- *************** HH HTML FILES CUSTOMIZATIONS **************** -->

<xsl:param name="label.from.part" select="1"/>

<!-- custom HTML xCHM layout described in phpdoc/en/chmonly/skins.xml or 
     http://wiki.phpdoc.info/xCHM -->
<!-- Add "pageContent" div for skinning support -->
<xsl:template name="chunk-element-content">
  <xsl:param name="prev"/>
  <xsl:param name="next"/>
  <xsl:param name="nav.context"/>
  <xsl:param name="content">
    <xsl:apply-imports/>
  </xsl:param>

  <html>
    <xsl:call-template name="html.head">
      <xsl:with-param name="prev" select="$prev"/>
      <xsl:with-param name="next" select="$next"/>
    </xsl:call-template>

    <body>
      <xsl:call-template name="body.attributes"/>
      <div id="pageContent">
            <xsl:call-template name="user.header.navigation"/>

            <xsl:call-template name="header.navigation">
      	<xsl:with-param name="prev" select="$prev"/>
      	<xsl:with-param name="next" select="$next"/>
      	<xsl:with-param name="nav.context" select="$nav.context"/>
            </xsl:call-template>

            <xsl:call-template name="user.header.content"/>

            <xsl:copy-of select="$content"/>

            <xsl:call-template name="user.footer.content"/>

            <xsl:call-template name="footer.navigation">
      	<xsl:with-param name="prev" select="$prev"/>
      	<xsl:with-param name="next" select="$next"/>
      	<xsl:with-param name="nav.context" select="$nav.context"/>
            </xsl:call-template>

            <xsl:call-template name="user.footer.navigation"/>
      </div>
    </body>
  </html>
</xsl:template>

<!-- *extra* slim HTML head to strip <link> tags -->
<xsl:template name="html.head">
  <head>
    <xsl:call-template name="system.head.content"/>
    <xsl:call-template name="head.content"/>
    <xsl:call-template name="user.head.content"/>
  </head>
</xsl:template>

<xsl:template name="user.head.content">
  <xsl:param name="node" select="."/>
<xsl:text disable-output-escaping="yes"><![CDATA[
  <style type="text/css">
      #pageContent {display:none}
      @media print {
          #pageContent {display:block}
      }
  </style>
  <script type="text/javascript" language="JavaScript1.2" src="_script.js"></script>
]]></xsl:text>
</xsl:template>

<!-- We need quite different body attributes than the defaults -->
<xsl:template name="body.attributes">
  <xsl:attribute name="onload">
     if (typeof displayPage == 'function') {
        displayPage(); 
     } else if (typeof document.all['pageContent'].style != 'undefined') {
         document.all['pageContent'].style.display = 'block';
     }
  </xsl:attribute>
  <xsl:attribute name="oncontextmenu">if(prefs_context_override){return false;}</xsl:attribute>
</xsl:template>

<!-- We need no header navigation, but we'll need footer --> 
<xsl:param name="suppress.navigation" select="0"/>
<xsl:param name="suppress.header.navigation" select="1"/>

<!-- DIV place for user notes to be inserted dynamically -->
<xsl:template name="user.footer.content">
  <a id="user_notes"></a>
  <div id="pageNotes"></div>
  <script type="text/javascript" language="JavaScript1.2">
   function displayNotes() { _displayNotes(); }
   if (typeof loadNotes == "function") { loadNotes(); }
  </script>
</xsl:template>

<!-- Footer part with special table for our special needs ;) -->
<xsl:template name="footer.navigation">
  <xsl:param name="prev" select="/foo"/>
  <xsl:param name="next" select="/foo"/>
  
  <div id="pageNav">
  <table width="100%" border="0" cellspacing="10" cellpadding="0" class="navigation">
    <tr align="left" valign="middle"> 
      <td width="30%">
        <xsl:if test="count($prev)>0">
          <span id="navPrev">
          <a accesskey="p">
            <xsl:attribute name="href">
              <xsl:call-template name="href.target">
                <xsl:with-param name="object" select="$prev"/>
              </xsl:call-template>
            </xsl:attribute>
            <xsl:text>&lt;&lt; </xsl:text>
            <xsl:apply-templates select="$prev" mode="title.markup"/>
          </a>
          </span>
        </xsl:if>
      </td>
      <td align="center" width="40%">
        <span id="navPath">
        <xsl:apply-templates select="." mode="path.to.this.page">
          <xsl:with-param name="actpage" select="true()"/>
        </xsl:apply-templates>
        </span>
      </td>
      <td align="right" width="30%">
        <xsl:if test="count($next)>0">
          <span id="navNext">
          <a accesskey="n">
            <xsl:attribute name="href">
              <xsl:call-template name="href.target">
                <xsl:with-param name="object" select="$next"/>
              </xsl:call-template>
            </xsl:attribute>
            <xsl:apply-templates select="$next" mode="title.markup"/>
            <xsl:text> &gt;&gt;</xsl:text>
          </a>
          </span>
        </xsl:if>
      </td>
    </tr>
    <tr align="center" valign="middle"> 
      <td colspan="3">
        <span id="navOnline">
          <span id="navThisOnline"><a href="javascript:thisPageOnline();">This page online</a></span>
          <xsl:text disable-output-escaping="yes"> &amp;nbsp; </xsl:text>
          <span id="navReportBug"><a href="javascript:bugOnPage();">Report a bug</a></span>
        </span>
      </td>
    </tr>
  </table>
  </div>
</xsl:template>

<!-- Building path to this page from the main page -->
<xsl:template match="*" mode="path.to.this.page">
  <xsl:param name="actpage" select="false()"/>
  <xsl:variable name="up" select="parent::*"/>

  <!-- Call this recursively for the parent -->
  <xsl:if test="count($up)>0">
    <xsl:apply-templates select="parent::*" mode="path.to.this.page">
      <xsl:with-param name="actpage" select="false()"/>
    </xsl:apply-templates>
  </xsl:if>
  
  <!-- Choose our own title, different from the default if this
       is the main page -->
  <xsl:variable name="object.title">
    <xsl:choose>
      <xsl:when test="count($up)>0">
        <xsl:apply-templates select="." mode="title.markup"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="'Main'"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <!-- Do not link if actual page, put " : " between links -->
  <xsl:choose>
    <xsl:when test="$actpage = true()">
      <xsl:value-of select="$object.title"/>
    </xsl:when>
    <xsl:otherwise>
      <a>
        <xsl:attribute name="href">
          <xsl:call-template name="href.target">
            <xsl:with-param name="object" select="."/>
          </xsl:call-template>
        </xsl:attribute>
        <xsl:value-of select="$object.title"/>
      </a>
      <xsl:text> : </xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- Remove block in ./docbook/docbook-xsl/htmlhelp/htmlhelp-common.xsl for building 
     functions index -->
<xsl:template match="index">
  <xsl:apply-templates/>
</xsl:template>

<!-- Do not generate ToC for index page -->
<!-- Copy of native DocBook template with "if" inserted -->
<xsl:template name="component.toc">
  <xsl:param name="toc-context" select="."/>
  <xsl:param name="toc.title.p" select="true()"/>

  <xsl:if test="@id!='indexes'">
  <xsl:call-template name="make.toc">
    <xsl:with-param name="toc-context" select="$toc-context"/>
    <xsl:with-param name="toc.title.p" select="$toc.title.p"/>
    <xsl:with-param name="nodes" select="section|sect1|refentry
                                         |article|bibliography|glossary
                                         |appendix|index
                                         |bridgehead[not(@renderas)
                                                     and $bridgehead.in.toc != 0]
                                         |.//bridgehead[@renderas='sect1'
                                                        and $bridgehead.in.toc != 0]"/>
  </xsl:call-template>
  </xsl:if>
</xsl:template>



<!-- *************** HH HTML MARKUP CUSTOMIZATIONS **************** -->

<!-- output formal object titles enclosed in <h3> tags -->
<!-- formalhead was seen in abstract title, examples, tables -->
<xsl:template name="formal.object.heading">
  <xsl:param name="object" select="."/>
  <h3 class="formalhead">
    <xsl:call-template name="anchor"/>
    <xsl:apply-templates select="$object" mode="object.title.markup">
      <xsl:with-param name="allow-anchors" select="1"/>
    </xsl:apply-templates>
  </h3>
</xsl:template>

<!-- Use simple bold text for admonitions (note|important|warning|caution|tip) -->
<xsl:template name="nongraphical.admonition">
  <div class="{name(.)}">
    <xsl:if test="$admon.style">
      <xsl:attribute name="style">
        <xsl:value-of select="$admon.style"/>
      </xsl:attribute>
    </xsl:if>

    <b>
      <xsl:call-template name="anchor"/>
      <xsl:apply-templates select="." mode="object.title.markup"/>
      <xsl:text>: </xsl:text>
    </b>

    <xsl:apply-templates/>
  </div>
</xsl:template>

<!-- Tune table cellpadding and cellspacing -->
<xsl:param name="html.cellspacing" select="'1'"/>
<xsl:param name="html.cellpadding" select="'2'"/>
<!-- Tune table borders -->
<xsl:param name="table.borders.with.css" select="1"/>
<xsl:param name="table.cell.border.thickness" select="''"/>
<xsl:param name="table.cell.border.style" select="''"/>



<!-- Special REFERENCE PAGE formatting for HH -->

<!-- REFERENCE PAGE TITLE or REFERENCE TITLEPAGE -->
<!-- We need <refname> to be in page title (page header which also called
     titlepage in templates).
     Native <refentry> template calls "refentry.titlepage" template to display
     page header. "refentry.titlepage" is generated automatically as described
     in http://www.sagehill.net/docbookxsl/HtmlCustomEx.html#HTMLTitlePage

     Default template doesn't produce titlepage content for <refname>, but
     <refname> can be rendered with "refentry.title" in html/refentry.xsl

     FIX: replace autogenerated template with ours custom as we don't have 
          template for automatic generation of titlepage templates
-->
<xsl:template name="refentry.titlepage">
  <div class="titlepage">
    <xsl:call-template name="refentry.title"/>
  </div>
</xsl:template>

<!--  Function page sample:
<h2 class="subheader">Format a local time/date. (PHP 3, PHP 4 &gt;= 4.0.0)<br>
Usage: string date (string format, int [timestamp])<br></h2>
-->
<xsl:template match="refnamediv">
  <div class="{name(.)}">
    <xsl:call-template name="anchor"/>
    <h2 class="subheader">
      <span id="funcPurpose"><xsl:value-of select="./refpurpose"/></span>
      <xsl:if test="ancestor::part/@id='funcref' or ancestor::part/@id='pecl-funcref'">
        (<span id="funcAvail"><xsl:value-of select="$version/function[@name=string(current()/refname)]/@from"/></span>)
      </xsl:if>
      <br/>
      <span id="funcUsage"><xsl:apply-templates select="../refsect1/methodsynopsis" mode="php"/></span>
    </h2>
  </div>
</xsl:template>

<!-- Rendering of METHODSYNOPSIS with span tags. The output of this should look like:
     
     int preg_match_all ( string pattern, string subject, array matches [, int flags] )
     
     working from a structure like this:
     
     <methodsynopsis>
      <type>int</type><methodname>preg_match_all</methodname>
      <methodparam><type>string</type><parameter>pattern</parameter></methodparam>
      <methodparam><type>string</type><parameter>subject</parameter></methodparam>
      <methodparam><type>array</type><parameter role="reference">matches</parameter></methodparam>
      <methodparam choice="opt"><type>int</type><parameter>flags</parameter></methodparam>
     </methodsynopsis>

     This overrides common.xsl templates
-->
<!-- Print out the return type, the method name, then the parameters.
     Close all the optional signs opened and close the prentheses -->
<xsl:template match="methodsynopsis" mode="php">
  <div class="{name(.)}">
    <span class="funcreturntype">
     <xsl:value-of select="concat(./type/text(), ' ')"/>
    </span>
    <span class="funcname">
     <xsl:value-of select="./methodname/text()"/>
    </span>
    <xsl:text> ( </xsl:text>
    <xsl:apply-templates select="./methodparam" mode="php"/>
    <xsl:for-each select="./methodparam[@choice = 'opt']">
      <xsl:text>]</xsl:text>
    </xsl:for-each>
    <xsl:text> )</xsl:text>
  </div>
</xsl:template>

<!-- Print out optional sign if needed, then a comma if this is
     not the first param, then the type and the parameter name -->
<xsl:template match="methodsynopsis/methodparam" mode="php">
  <xsl:if test="@choice = 'opt'">
    <xsl:text> [</xsl:text>
  </xsl:if>
  <xsl:if test="position() != 1">
    <xsl:text>, </xsl:text>
  </xsl:if>
  <span class="funcparamtype">
   <xsl:value-of select="./type/text()"/>
  </span>
  <xsl:text> </xsl:text>
  <span class="funcparamname">
    <xsl:if test="./parameter/@role='reference'">
      <xsl:text>&amp;</xsl:text>
    </xsl:if>
   <xsl:value-of select="./parameter/text()"/>
  </span>
</xsl:template>


<!-- REFERENCE PAGE CONTENTS -->

<!-- Drop out constant "Description" -->
<xsl:template match="refsect1[@role = 'description']/title"/>
<!-- FIX: try to drop out "Description" in old format until we move to a
          new format completely (will not work for other language) -->
<xsl:template match="refsect1/title[.='Description']"/>

<!-- Render "See also section" -->
<!-- FIX: old format by the same reason -->
<xsl:template match="refsect1[@role = 'seealso']/title | refsect1/title[.='See also']">
   <xsl:value-of select="."/>
</xsl:template>

<!-- Do not process methodsynopsis node as we've rendered it explicitly in
     reference titlepage above -->
<xsl:template match="methodsynopsis"/>

</xsl:stylesheet>
