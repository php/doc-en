<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  html-common.xsl: Common HTML customizations

  $Id: html-common.xsl,v 1.26 2003-06-04 19:43:32 tom Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:include href="common.xsl"/>
<xsl:include href="param_html.xsl"/>

<!-- We do not want style="" atts to appear in HTML output -->
<xsl:param name="admon.style" select="''"/>


<!-- ==========================   TOC   =================================== -->

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


<!-- Make the TOC-depths like in the DSSSL-version -->
<xsl:param name="toc.max.depth" select="1"/>

<xsl:template name="subtoc">
  <xsl:param name="toc-context" select="."/>
  <xsl:param name="nodes" select="NOT-AN-ELEMENT"/>

  <xsl:variable name="subtoc">
    <xsl:element name="{$toc.list.type}">
      <xsl:apply-templates mode="toc" select="$nodes">
        <xsl:with-param name="toc-context" select="$toc-context"/>
      </xsl:apply-templates>
    </xsl:element>
  </xsl:variable>

  <xsl:variable name="depth">
    <xsl:choose>
      <xsl:when test="local-name(.) = 'section'">
        <xsl:value-of select="count(ancestor::section) + 1"/>
      </xsl:when>
      <xsl:when test="local-name(.) = 'sect1'">1</xsl:when>
      <xsl:when test="local-name(.) = 'sect2'">2</xsl:when>
      <xsl:when test="local-name(.) = 'sect3'">3</xsl:when>
      <xsl:when test="local-name(.) = 'sect4'">4</xsl:when>
      <xsl:when test="local-name(.) = 'sect5'">5</xsl:when>
      <xsl:when test="local-name(.) = 'refsect1'">1</xsl:when>
      <xsl:when test="local-name(.) = 'refsect2'">2</xsl:when>
      <xsl:when test="local-name(.) = 'refsect3'">3</xsl:when>
      <xsl:when test="local-name(.) = 'simplesect'">
        <!-- sigh... -->
        <xsl:choose>
          <xsl:when test="local-name(..) = 'section'">
            <xsl:value-of select="count(ancestor::section)"/>
          </xsl:when>
          <xsl:when test="local-name(..) = 'sect1'">2</xsl:when>
          <xsl:when test="local-name(..) = 'sect2'">3</xsl:when>
          <xsl:when test="local-name(..) = 'sect3'">4</xsl:when>
          <xsl:when test="local-name(..) = 'sect4'">5</xsl:when>
          <xsl:when test="local-name(..) = 'sect5'">6</xsl:when>
          <xsl:when test="local-name(..) = 'refsect1'">2</xsl:when>
          <xsl:when test="local-name(..) = 'refsect2'">3</xsl:when>
          <xsl:when test="local-name(..) = 'refsect3'">4</xsl:when>
          <xsl:otherwise>1</xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise>0</xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="depth.from.context" select="count(ancestor::*)-count($toc-context/ancestor::*)"/>

  <xsl:variable name="subtoc.list">
    <xsl:choose>
      <xsl:when test="$toc.dd.type = ''">
        <xsl:copy-of select="$subtoc"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="{$toc.dd.type}">
          <xsl:copy-of select="$subtoc"/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="my.toc.max.depth">
    <xsl:choose>
      <xsl:when test="local-name(..) = 'book'">2</xsl:when>
      <xsl:otherwise>
        <xsl:copy-of select="$toc.max.depth"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:element name="{$toc.listitem.type}">
    <xsl:variable name="label">
      <xsl:apply-templates select="." mode="label.markup"/>
    </xsl:variable>
    <xsl:copy-of select="$label"/>
    <xsl:if test="$label != ''">
      <xsl:value-of select="$autotoc.label.separator"/>
    </xsl:if>

    <a>
      <xsl:attribute name="href">
        <xsl:call-template name="href.target">
          <xsl:with-param name="context" select="$toc-context"/>
        </xsl:call-template>
      </xsl:attribute>
      <xsl:apply-templates select="." mode="title.markup"/>
    </a>
    <xsl:if test="$toc.listitem.type = 'li'
                  and $toc.section.depth > $depth and count($nodes)&gt;0
                  and $my.toc.max.depth > $depth.from.context">
      <xsl:copy-of select="$subtoc.list"/>
    </xsl:if>
  </xsl:element>
  <xsl:if test="$toc.listitem.type != 'li'
                and $toc.section.depth > $depth and count($nodes)&gt;0
                and $my.toc.max.depth > $depth.from.context">
    <xsl:copy-of select="$subtoc.list"/>
  </xsl:if>
</xsl:template>

<!-- =======================    END TOC   ================================= -->

<!-- =======================   TITLEPAGE  ================================= -->

<!-- Let authors, editor, and translators look similar to DSSSL-version -->
<xsl:template match="author|editor" mode="titlepage.mode">
  <xsl:call-template name="person.name"/><br/>
</xsl:template>

<xsl:template match="authorgroup" mode="book.titlepage.recto.auto.mode">
  <xsl:choose>
    <xsl:when test="@id='authors'">
      <xsl:call-template name="anchor"/>
      <h3><xsl:call-template name="gentext.by"/></h3>
      <p><xsl:apply-templates select="." mode="titlepage.mode"/></p>
    </xsl:when>
    <xsl:when test="@id='editors'">
      <h3><xsl:call-template name="gentext.edited.by"/></h3>
      <p><xsl:apply-templates mode="titlepage.mode"/></p>
    </xsl:when>
    <xsl:when test="@id='translators'">
      <h4><xsl:apply-templates select="collab[1]/collabname"/></h4>
      <p><xsl:apply-templates select="collab" mode="titlepage.mode"/></p>
    </xsl:when>
    <xsl:otherwise></xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- don't just show the date for pubdate -->
<xsl:template match="bookinfo/pubdate" mode="titlepage.mode">
  <xsl:call-template name="gentext">
    <xsl:with-param name="key" select="'published'"/>
  </xsl:call-template>
  <xsl:text>: </xsl:text>
  <xsl:apply-templates />
</xsl:template>

<!-- PREFACE/ABSTRACT: supress the autogenerated title,
     but make a blockquote like in DSSSL-result -->
<xsl:template match="preface/abstract">
  <div class="{name(.)}">
    <xsl:call-template name="anchor"/>
    <blockquote>
      <xsl:apply-templates />
    </blockquote>
  </div>
</xsl:template>

<!-- ======================== END TITLEPAGE =============================== -->

<!-- METHODNAMES are bold like in DSSSL -->
<xsl:template match="methodsynopsis/methodname">
  <b><xsl:apply-templates/></b>
</xsl:template>

<!-- We need a newline after methodsynopsis, as we have
     multiple methodsynopsys parts sometimes -->
<xsl:template match="methodsynopsis">
  <xsl:apply-templates/><br/>
</xsl:template>

<!-- Add LINKS to TYPES -->
<xsl:template match="type">
  <xsl:variable name="mytype">
    <xsl:apply-templates/>
  </xsl:variable>
  <xsl:variable name="name">
  <xsl:choose>
    <xsl:when test="$mytype='int'">language.types.integer</xsl:when>
    <xsl:when test="$mytype='bool'">language.types.boolean</xsl:when>
    <xsl:when test="$mytype='double'">language.types.float</xsl:when>
    <xsl:otherwise>
      <xsl:copy-of select="concat('language.types.',
                                  translate($mytype,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'))"/>
    </xsl:otherwise>
  </xsl:choose>
  </xsl:variable>

  <xsl:choose>
    <xsl:when test="count(/*/part[@id='langref']/chapter[@id='language.types']/sect1[@id=$name]) = 1
                    and not(ancestor::sect1[@id=$name])">
      <a>
        <xsl:attribute name="href">
          <xsl:call-template name="href.target">
            <xsl:with-param name="object" select="id($name)"/> 
          </xsl:call-template>
        </xsl:attribute>
        <xsl:copy-of select="$mytype"/>
      </a>
    </xsl:when>
    <xsl:otherwise>
      <xsl:copy-of select="$mytype"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>


<!-- Add VERSION INFORMATION below FUNCTION name -->
<xsl:template match="refnamediv">
  <div class="{name(.)}">
    <h1>
      <xsl:apply-templates select="refname[1]"/>
    </h1>
    <p>
      <xsl:text>(</xsl:text>
      <xsl:value-of select="$version/function[@name=string(current()/refname)]/@from"/>
      <xsl:text>)</xsl:text>
    </p>
    <p>
      <xsl:apply-templates/>
    </p>
  </div>
</xsl:template>


<!-- Let NOTES and WARNINGS look more similar to the dsssl-version -->
<xsl:template match="simpara|para|title" mode="note.single.entry">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="note">
  <xsl:variable name="inlinepara">
    <xsl:choose>
      <xsl:when test="count(title) = 0">
        <xsl:apply-templates select="*[1]" mode="note.single.entry"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates select="*[2]" mode="note.single.entry"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <blockquote>
    <p>
      <b>
        <xsl:choose>
          <xsl:when test="count(title) > 0">
            <xsl:apply-templates select="title" mode="note.single.entry"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:call-template name="gentext"/>
          </xsl:otherwise>
        </xsl:choose>
        <xsl:text>: </xsl:text>
      </b>
      <xsl:copy-of select="$inlinepara"/>
    </p>
    <xsl:choose>
      <xsl:when test="$inlinepara != '' and count(title) > 0">
        <xsl:apply-templates select="*[not(position() &lt; 3)]" />
      </xsl:when>
      <xsl:when test="$inlinepara != '' and count(title) = 0">
        <xsl:apply-templates select="*[not(position() &lt; 2)]" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:apply-templates/>
      </xsl:otherwise>
    </xsl:choose>
  </blockquote>
</xsl:template>

<xsl:template match="warning|caution">
  <xsl:variable name="pos">
    <xsl:choose>
      <xsl:when test="count(title[1])=1">2</xsl:when>
      <xsl:otherwise>1</xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <div class="{name(.)}">
    <p style="font-size:10px"></p>
    <table border="1" width="96%" align="center">
      <tr>
        <td align="center"><b>
          <xsl:choose>
            <xsl:when test="$pos=2">
              <xsl:apply-templates select="title" mode="note.single.entry"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:call-template name="gentext"/>
            </xsl:otherwise>
          </xsl:choose>
        </b></td>
      </tr>
      <tr><td>
        <xsl:apply-templates select="*[position()=$pos]" mode="note.single.entry" />
        <xsl:choose>
          <xsl:when test="(count(child::*)-$pos) > 1">
            <xsl:apply-templates select="*[position()>$pos and position()!=last()]" />
            <xsl:apply-templates select="*[position()=last()]" mode="note.single.entry" />
          </xsl:when>
          <xsl:otherwise>
            <p style="font-size=8px"></p>
            <xsl:apply-templates select="*[position()>$pos]" mode="note.single.entry" />
          </xsl:otherwise>
        </xsl:choose>
      </td></tr>
    </table>
    <p style="font-size:10px"></p>
  </div>
</xsl:template>


<!-- Ensure PARAGRAPHS: $html.cleanup is useful and should
     be used for e.g <span...>, but supresses the <p>-tags
     if something is inside a para (e.g. <para>bla<example>). -->
<xsl:template match="para">
  <xsl:variable name="p">
    <xsl:if test="position() = 1 and parent::listitem">
      <xsl:call-template name="anchor">
        <xsl:with-param name="node" select="parent::listitem"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:call-template name="anchor"/>
    <xsl:apply-templates/>
  </xsl:variable>
  <p>
    <xsl:choose>
      <xsl:when test="$html.cleanup != 0">
        <xsl:call-template name="unwrap.p">
          <xsl:with-param name="p" select="$p"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:copy-of select="$p"/>
      </xsl:otherwise>
    </xsl:choose>
  </p>
</xsl:template>



<!-- CHAPTER-TITLES: make them <h1> like in DSSSL -->
<xsl:template match="chapter/title" mode="titlepage.mode">
  <xsl:variable name="node" select="ancestor::chapter[1]"/>
  <h1>
    <xsl:call-template name="anchor">
      <xsl:with-param name="node" select="$node"/>
      <xsl:with-param name="conditional" select="0"/>
    </xsl:call-template>
    <xsl:apply-templates select="$node" mode="object.title.markup">
      <xsl:with-param name="allow-anchors" select="1"/>
    </xsl:apply-templates>
  </h1>
</xsl:template>


<!-- SECTION-TITLES: <sect1> -> <h1> ... <sect5> -> <h5>) as in DSSSL -->
<xsl:template name="section.heading">
  <xsl:param name="section" select="."/>
  <xsl:param name="level" select="1"/>
  <xsl:param name="allow-anchors" select="1"/>
  <xsl:param name="title"/>

  <xsl:variable name="id">
    <xsl:choose>
      <xsl:when test="contains(local-name(..), 'info')">
        <xsl:call-template name="object.id">
          <xsl:with-param name="object" select="../.."/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="object.id">
          <xsl:with-param name="object" select=".."/>
        </xsl:call-template>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:element name="h{$level}">
    <xsl:if test="$css.decoration != '0'">
      <xsl:if test="$level&lt;3">
        <xsl:attribute name="style">clear:both</xsl:attribute>
      </xsl:if>
    </xsl:if>
    <xsl:if test="$allow-anchors != 0">
      <xsl:call-template name="anchor">
        <xsl:with-param name="node" select="$section"/>
        <xsl:with-param name="conditional" select="0"/>
      </xsl:call-template>
    </xsl:if>
    <xsl:copy-of select="$title"/>
  </xsl:element>
</xsl:template>


<!-- Let FAQ-QUESTIONS look more similar to the dsssl-version -->
<xsl:template match="question">
  <xsl:variable name="deflabel">
    <xsl:choose>
      <xsl:when test="ancestor-or-self::*[@defaultlabel]">
        <xsl:value-of select="(ancestor-or-self::*[@defaultlabel])[last()]
                              /@defaultlabel"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="qanda.defaultlabel"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <tr>
    <td align="left" valign="top">
      <xsl:call-template name="anchor">
        <xsl:with-param name="node" select=".."/>
        <xsl:with-param name="conditional" select="0"/>
      </xsl:call-template>
      <xsl:call-template name="anchor">
        <xsl:with-param name="conditional" select="0"/>
      </xsl:call-template>
      <b>
        <xsl:apply-templates select="." mode="label.markup"/>
        <xsl:text>. </xsl:text>
      </b>
    </td>
    <td align="left" valign="top">
      <b><xsl:apply-templates select="*[name(.) != 'label']" mode="note.single.entry"/></b>
    </td>
  </tr>
</xsl:template>


<!-- make the TITLE "Table of contents" an HTML-HEADER, so that the
     size-diff to the headers in the reference-intro's isn't that big -->
<xsl:template name="make.toc">
  <xsl:param name="toc-context" select="."/>
  <xsl:param name="toc.title.p" select="true()"/>
  <xsl:param name="nodes" select="/NOT-AN-ELEMENT"/>

  <xsl:variable name="toc.title">
    <xsl:if test="$toc.title.p">
      <h2>
        <xsl:call-template name="gentext">
          <xsl:with-param name="key">TableofContents</xsl:with-param>
        </xsl:call-template>
      </h2>
    </xsl:if>
  </xsl:variable>

  <xsl:choose>
    <xsl:when test="$manual.toc != ''">
      <xsl:variable name="id">
        <xsl:call-template name="object.id"/>
      </xsl:variable>
      <xsl:variable name="toc" select="document($manual.toc, .)"/>
      <xsl:variable name="tocentry" select="$toc//tocentry[@linkend=$id]"/>
      <xsl:if test="$tocentry and $tocentry/*">
          <xsl:copy-of select="$toc.title"/>
          <xsl:element name="{$toc.list.type}">
            <xsl:call-template name="manual-toc">
              <xsl:with-param name="tocentry" select="$tocentry/*[1]"/>
            </xsl:call-template>
          </xsl:element>
      </xsl:if>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="$nodes">
        <xsl:copy-of select="$toc.title"/>
        <xsl:element name="{$toc.list.type}">
          <xsl:apply-templates select="$nodes" mode="toc">
            <xsl:with-param name="toc-context" select="$toc-context"/>
          </xsl:apply-templates>
        </xsl:element>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>



<!-- Add a Label (e.g. I.) to reference-title, suppress
     <hr> after reference-title, and save some <div>'s  -->
<xsl:template name="reference.titlepage">
  <xsl:apply-templates select="title" mode="reference.titlepage.mode"/>
</xsl:template>
<xsl:template match="title" mode="reference.titlepage.mode">
  <h1>
    <xsl:apply-templates select=".." mode="label.markup"/>
    <xsl:text>. </xsl:text>
    <xsl:apply-templates mode="titlepage.mode"/>
  </h1>
</xsl:template>

<xsl:template match="reference/partintro">
  <div class="{name(.)}">
    <xsl:apply-templates/>

    <xsl:variable name="toc.params">
      <xsl:call-template name="find.path.params">
        <xsl:with-param name="node" select="parent::*"/>
        <xsl:with-param name="table" select="normalize-space($generate.toc)"/>
      </xsl:call-template>
    </xsl:variable>
    <xsl:if test="contains($toc.params, 'toc')">
      <xsl:apply-templates select="parent::*" mode="make.part.toc"/>
    </xsl:if>
    <xsl:call-template name="process.footnotes"/>
  </div>
</xsl:template>

<xsl:template match="reference/partintro/section">
  <xsl:apply-templates mode="titlepage.mode"/>
</xsl:template>


<!-- Just to DECREASE FILESIZE (since we have no css-styles) -->
<xsl:template name="refentry.titlepage">
</xsl:template>

<xsl:template match="parameter">
  <i><tt><xsl:apply-templates/></tt></i>
</xsl:template>

<xsl:template match="filename|literal|option|varname">
  <tt><xsl:apply-templates/></tt>
</xsl:template>

<xsl:template match="constant">
 <b><tt><xsl:apply-templates/></tt></b>
</xsl:template>

<xsl:template match="refsection|refsect1|refsect2|refsect3">
  <xsl:apply-templates/>
</xsl:template>

<xsl:template name="formal.object.heading">
  <xsl:param name="object" select="."/>
  <p>
    <b>
      <xsl:apply-templates select="$object" mode="object.title.markup">
        <xsl:with-param name="allow-anchors" select="1"/>
      </xsl:apply-templates>
    </b>
  </p>
</xsl:template>

<xsl:template name="nongraphical.admonition">
    <xsl:if test="$admon.style">
      <xsl:attribute name="style">
        <xsl:value-of select="$admon.style"/>
      </xsl:attribute>
    </xsl:if>
    <h3>
      <xsl:call-template name="anchor"/>
      <xsl:apply-templates select="." mode="object.title.markup"/>
    </h3>
    <xsl:apply-templates/>
</xsl:template>

<xsl:template name="component.title">
  <xsl:param name="node" select="."/>
  <h2>
    <xsl:call-template name="anchor">
      <xsl:with-param name="node" select="$node"/>
      <xsl:with-param name="conditional" select="0"/>
    </xsl:call-template>
    <xsl:apply-templates select="$node" mode="object.title.markup">
      <xsl:with-param name="allow-anchors" select="1"/>
    </xsl:apply-templates>
  </h2>
</xsl:template>

<xsl:template match="programlisting|screen|synopsis">
  <xsl:param name="suppress-numbers" select="'0'"/>
  <table xsl:use-attribute-sets="shade.verbatim.style">
    <tr>
      <td>
        <pre><xsl:apply-templates/></pre>
      </td>
    </tr>
  </table>
</xsl:template>

</xsl:stylesheet>
