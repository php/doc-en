<?xml version="1.0"?>
<!-- 

  HTML Help specific stylesheet

  $Id: htmlhelp.xsl,v 1.2 2004-10-18 20:28:10 techtonik Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:doc="http://nwalsh.com/xsl/documentation/1.0"
                xmlns:exsl="http://exslt.org/common"
                xmlns:set="http://exslt.org/sets"
		version="1.0"
                exclude-result-prefixes="doc exsl set">

<!-- - BASED ON 1.66.1 HTMLHELP.XSL DOCBOOK XSL STYLESHEET - -->

<xsl:import href="./docbook/html/chunk.xsl"/>
<xsl:import href="./docbook/htmlhelp/htmlhelp-common.xsl"/>

<!-- configure/able/ parameters -->
<xsl:include href="htmlhelp-config.xsl"/>


<xsl:param name="base.dir" select="'htmlhelp/html/'"/>
<!-- project files for HTML Help are written into base.dir instead of current directory -->
<xsl:param name="manifest.in.base.dir" select="1"/>
<xsl:param name="use.id.as.filename" select="1"/>

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
<xsl:param name="htmlhelp.button.jump1" select="1"/>
<xsl:param name="htmlhelp.button.jump1.url" select="'http://www.php.net'"/>
<xsl:param name="htmlhelp.button.jump1.title" select="'PHP.NET'"/>
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
  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($title)}"/>
    <param name="Local" value="_index.html"/>
  </object></li>
  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($title)}"/>
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

<UL>]]></xsl:text>
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
  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($title)}"/>
    <param name="Local" value="_index.html"/>
  </object></li>
  <xsl:text>
  </xsl:text>
  <li><object type="text/sitemap">
    <param name="Name" value="{normalize-space($title)}"/>
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


<xsl:param name="htmlhelp.only" select="1"/>
<!--
<xsl:param name="htmlhelp.only" select="1"/>
htmlhelp.autolabel - chapter and section numbers in ToC - off

 -->


<!-- *************** HH HTML FILES CUSTOMIZATIONS **************** -->
<xsl:param name="label.from.part" select="1"/>

<!-- *extra* slim HTML head from older templates to strip <link> tags -->
<xsl:template name="html.head">
  <head>
    <xsl:call-template name="system.head.content"/>
    <xsl:call-template name="head.content"/>
    <xsl:call-template name="user.head.content"/>
  </head>
</xsl:template>

<xsl:template name="user.head.content">
  <xsl:param name="node" select="."/>
  <script language="JavaScript1.2" src="_script.js"></script>
</xsl:template>

<!-- We need quite different body attributes than the defaults -->
<xsl:template name="body.attributes">
  <xsl:attribute name="onload">displayPage();</xsl:attribute>
  <xsl:attribute name="oncontextmenu">if(prefs_context_override){return false;}</xsl:attribute>
</xsl:template>

<!-- We need no header navigation, but we'll need footer --> 
<xsl:param name="suppress.navigation" select="0"/>
<xsl:param name="suppress.header.navigation" select="1"/>

<!-- Footer part with special table for our special needs ;) -->
<xsl:template name="footer.navigation">
  <xsl:param name="prev" select="/foo"/>
  <xsl:param name="next" select="/foo"/>
  
  <a name="_user_notes"></a>
  <div id="pageNotes"></div>
  <script language="JavaScript1.2">
   function displayNotes() { _displayNotes(); }
   loadNotes();
  </script>
  
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

<!-- output formal object titles enclosed in <h3> tags -->
<xsl:template name="formal.object.heading">
  <xsl:param name="object" select="."/>
  <h3 class="formalhead">
    <xsl:call-template name="anchor"/>
    <xsl:apply-templates select="$object" mode="object.title.markup">
      <xsl:with-param name="allow-anchors" select="1"/>
    </xsl:apply-templates>
  </h3>
</xsl:template>

</xsl:stylesheet>
