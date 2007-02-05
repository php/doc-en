<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  common.xsl: Common customizations for all HTML formats

  $Id: common.xsl,v 1.3 2007-02-05 23:11:46 bjori Exp $

  What is done in this stylesheet as common to all HTML output formats:

  - shade programlistings and screens
  - function page title generated from function name
  - display PHP version information from version.xml
  - render functions prototypes in specific PHP way
  - preserve "role" attributes for programlistings
  - strip empty newlines around programlistings
  - TOC labels (like section numbers) are not included in TOC href
  - style admonitions and collab names
  - output "parameter" enclosed in vars like in DSSSL
  - link <function> blocks to appropriate function references
  - &raquo; HTML entity before the external ulink and CSS class for it
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">


<!-- Colorize background for programlisting and screens
     will go into CSS probably in next DocBook version --> 
<xsl:param name="shade.verbatim" select="1"/>



<!--                   Tune different PAGE TYPES                    -->

<!-- *************** TUNE FUNCTION REFERENCE PAGES **************** -->
<!-- Generate title from function name instead of word "NAME" in title -->
<xsl:param name="refentry.generate.name" select="'0'"/>
<xsl:param name="refentry.generate.title" select="'1'"/>

<!-- Turn off separators on reference and refentry pages -->
<xsl:param name="refentry.separator" select="'0'"/>
<xsl:template name="reference.titlepage.separator"/>

<!-- Load VERSION INFORMATION into variable -->
<xsl:param name="version" select="document('version.xml')/versions"/>

<!-- Add version information below function name. Use H1 tags to denote
     function title like in DSSSL -->
<xsl:template match="refnamediv">
  <div class="{name(.)}">
    <xsl:call-template name="anchor"/>
    <xsl:choose>
      <xsl:when test="$refentry.generate.name != 0">
        <h1>
          <xsl:call-template name="gentext">
            <xsl:with-param name="key" select="'RefName'"/>
          </xsl:call-template>
        </h1>
      </xsl:when>
      <xsl:when test="$refentry.generate.title != 0">
        <h1>
          <xsl:choose>
            <xsl:when test="../refmeta/refentrytitle">
              <xsl:apply-templates select="../refmeta/refentrytitle"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:apply-templates select="refname[1]"/>
            </xsl:otherwise>
          </xsl:choose>
        </h1>
      </xsl:when>
    </xsl:choose>
    <xsl:if test="ancestor::part/@id='funcref' or ancestor::part/@id='pecl-funcref'">
     <p>(<xsl:value-of select="$version/function[@name=string(current()/refname)]/@from"/>)</p>
    </xsl:if>
    <p>
      <xsl:apply-templates/>
    </p>
  </div>
</xsl:template>

<!-- Rendering of METHODSYNOPSIS. The output of this should look like:
     
     int preg_match_all ( string pattern, string subject, array matches [, int flags] )
     
     working from a structure like this:
     
     <methodsynopsis>
      <type>int</type><methodname>preg_match_all</methodname>
      <methodparam><type>string</type><parameter>pattern</parameter></methodparam>
      <methodparam><type>string</type><parameter>subject</parameter></methodparam>
      <methodparam><type>array</type><parameter role="reference">matches</parameter></methodparam>
      <methodparam choice="opt"><type>int</type><parameter>flags</parameter></methodparam>
     </methodsynopsis>

     Note, that this is DSSSL like version. htmlhelp.xsl uses another, span style
-->

<!-- We do not want semicolon at the end of prototype and our own style
     of square brackets for optional parameters. Make methodnames bold
     like in DSSSL -->
<xsl:template match="methodsynopsis">
  <div class="{name(.)}">
    <xsl:apply-templates />
  </div>
</xsl:template>

<xsl:template match="methodsynopsis/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodsynopsis/void|constructorsynopsis/void">
  <xsl:text> ( void )</xsl:text>
</xsl:template>

<xsl:template match="methodsynopsis/methodname">
  <b class="{local-name(.)}">
    <xsl:value-of select="."/>
  </b>
</xsl:template>

<xsl:template match="methodparam/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodparam/parameter">
  <xsl:if test="@role='reference'">
    <xsl:text>&amp;</xsl:text>
  </xsl:if>
  <xsl:text>$</xsl:text>
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="methodparam">
  <xsl:if test="preceding-sibling::methodparam=false()">
    <xsl:text> ( </xsl:text>
    <xsl:if test="@choice='opt'">
      <xsl:text>[</xsl:text>
    </xsl:if>
  </xsl:if>
  <xsl:apply-templates />
  <xsl:choose>
    <xsl:when test="following-sibling::methodparam">
      <xsl:choose>
        <xsl:when test="following-sibling::methodparam[position()=1]/@choice='opt'">
          <xsl:text> [, </xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>, </xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:for-each select="preceding-sibling::methodparam">
				<xsl:if test="@choice='opt'">
					<xsl:text>]</xsl:text>
				</xsl:if>
      </xsl:for-each>
      <xsl:if test="self::methodparam/@choice='opt'">
        <xsl:text>]</xsl:text>
      </xsl:if>
      <xsl:text> )</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>



<!--                  Tune different PAGE BLOCKS                  -->

<!-- *************** TUNE PROGRAMLISTING DISPLAY **************** -->
<!-- This is the same as in DocBook XSL verbatim.xsl, except
     that we preserve the role in programlisting and the like -->
<xsl:template match="programlisting|screen|synopsis">
  <xsl:param name="suppress-numbers" select="'0'"/>
  <xsl:variable name="id">
    <xsl:call-template name="object.id"/>
  </xsl:variable>

  <xsl:call-template name="anchor"/>

  <xsl:variable name="preclass">
    <xsl:choose>
      <xsl:when test="./@role">
        <xsl:value-of select="./@role"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="name(.)"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:variable name="content">
    <xsl:choose>
      <xsl:when test="$suppress-numbers = '0'
                      and @linenumbering = 'numbered'
                      and $use.extensions != '0'
                      and $linenumbering.extension != '0'">
        <xsl:variable name="rtf">
          <xsl:apply-templates/>
        </xsl:variable>
        <pre class="{$preclass}">
          <xsl:call-template name="number.rtf.lines">
            <xsl:with-param name="rtf" select="$rtf"/>
          </xsl:call-template>
        </pre>
      </xsl:when>
      <xsl:otherwise>
        <pre class="{$preclass}">
          <xsl:apply-templates/>
        </pre>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>

  <xsl:choose>
    <xsl:when test="$shade.verbatim != 0">
      <table xsl:use-attribute-sets="shade.verbatim.style">
        <tr>
          <td>
            <xsl:copy-of select="$content"/>
          </td>
        </tr>
      </table>
    </xsl:when>
    <xsl:otherwise>
      <xsl:copy-of select="$content"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>


<!-- Strip newlines before and after the contents of program listing and
     and screen tags used for PHP code and output examples preserve
     identation of the first line.
     
     Initial version by techtonik =p
     Thanks to Peter Kullman for an idea
-->
<xsl:template match="screen/text()|programlisting/text()">
 <xsl:variable name="before" select="count(preceding-sibling::*)"/>
 <xsl:variable name="after" select="count(following-sibling::*)"/>

 <xsl:choose>
  <xsl:when test="$before = 0 and $after = 0">
   <xsl:call-template name="trim_newlines"/>
  </xsl:when>
  <xsl:when test="$before = 0">
   <xsl:call-template name="trim_newlines">
    <xsl:with-param name="lttrim" select="true()"/>
   </xsl:call-template>
  </xsl:when>
  <xsl:when test="$after = 0">
   <xsl:call-template name="trim_newlines">
    <xsl:with-param name="rttrim" select="true()"/>
   </xsl:call-template>
  </xsl:when>
  <xsl:otherwise>
   <xsl:value-of select="."/>
  </xsl:otherwise>
 </xsl:choose>
</xsl:template>

<xsl:template name="trim_newlines">
  <xsl:param name="string" select="."/>
  <xsl:param name="lttrim" select="false()"/>
  <xsl:param name="rttrim" select="false()"/> <!-- looking for endstring -->

  <xsl:variable name="nl" select="'&#xA;'" />

  <xsl:choose>
    <xsl:when test="normalize-space($string) and contains($string,$nl)"><!-- prevent endless cycle on empty blocks -->
      <xsl:variable name="beforenl" select="substring-before($string,$nl)" />
      <xsl:variable name="afternl" select="substring-after($string,$nl)" />
      <xsl:variable name="nextnl" select="normalize-space(substring-before($afternl,$nl))" />
      <xsl:choose>
        <xsl:when test="not($rttrim) and string-length(normalize-space($beforenl)) = 0">
          <xsl:call-template name="trim_newlines">
            <xsl:with-param name="string" select="$afternl" />
            <xsl:with-param name="lttrim" select="$lttrim" />
            <xsl:with-param name="rttrim" select="$rttrim or $nextnl" />
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:copy-of select="concat($beforenl,$nl)"/>
          <xsl:if test="$lttrim">
            <xsl:copy-of select="$afternl"/>
          </xsl:if>
          <xsl:if test="not($lttrim)">
            <xsl:call-template name="trim_newlines">
              <xsl:with-param name="string" select="$afternl" />
              <xsl:with-param name="rttrim" select="true()" />
            </xsl:call-template>
          </xsl:if>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:if test="normalize-space($string)">
        <xsl:copy-of select="$string"/>
      </xsl:if>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>


<!-- Make the TOC line like in the DSSSL-version,
     i.e. label is not included in href           -->
<xsl:template name="toc.line">
  <xsl:param name="toc-context" select="."/>
  <xsl:param name="depth" select="1"/>
  <xsl:param name="depth.from.context" select="8"/>

  <span>
  <xsl:attribute name="class"><xsl:value-of select="local-name(.)"/></xsl:attribute>

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
    
    <xsl:apply-templates select="." mode="titleabbrev.markup"/>
  </a>
  </span>
</xsl:template>


<!-- **************** ADMONITIONS STYLE ****************** -->
<!-- We do not want style="" atts to appear in HTML output -->
<xsl:param name="admon.style" select="''"/>


<!-- *************** FOR TRANSLATORS LIST **************** -->
<xsl:template match="collab" mode="titlepage.mode">
  <xsl:choose>
    <xsl:when test="position()=last()">
      <xsl:apply-templates/>
    </xsl:when>
    <xsl:when test="position() &gt; 1">
      <xsl:apply-templates/><xsl:text>, </xsl:text>
    </xsl:when>
    <xsl:otherwise></xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="collabname">
  <xsl:apply-templates/>
</xsl:template>



<!--          Tune different INLINE ELEMENTS       -->

<!-- Display PARAMETER enclosed in VAR like in DSSSL 
     instead of inline.italicmonoseq -->
<xsl:template match="parameter">
  <var class="{local-name(.)}">
    <xsl:apply-templates />
  </var>
</xsl:template>

<!-- Enclose FUNCTION in links, add parenthesis, make 'em bold. Do not link if
     current page is description of current function or target is not available -->
<xsl:template match="function">
  <xsl:variable name="content">
      <b class="{local-name(.)}">
         <xsl:apply-templates/>
          <xsl:text>()</xsl:text>
      </b>
  </xsl:variable>

  <!-- replace '->', '::', '->__' and '::__' in method name with one minus
       sign '-' to proceed with transformation from name to ID -->
  <xsl:variable name="argument">
    <xsl:variable name="token">
      <xsl:choose>
        <xsl:when test="contains(current(), '->__')">
          <xsl:value-of select="'->__'"/>
        </xsl:when>
        <xsl:when test="contains(current(), '::__')">
          <xsl:value-of select="'::__'"/>
        </xsl:when>
        <xsl:when test="contains(current(), '->')">
          <xsl:value-of select="'->'"/>
        </xsl:when>
        <xsl:when test="contains(current(), '::')">
          <xsl:value-of select="'::'"/>
        </xsl:when>
      </xsl:choose>
    </xsl:variable>

    <xsl:choose>
      <xsl:when test="string-length($token) != 0">
        <xsl:value-of select="concat(substring-before(current(), $token), '-',
                                     substring-after(current(), $token))"/>
      </xsl:when>
      <xsl:otherwise>
         <xsl:value-of select="string(current())"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  
  <xsl:variable name="function.href">
    <xsl:call-template name="href.target">
      <xsl:with-param name="object" select="id(concat('function.', translate($argument,'_','-')))"/> 
    </xsl:call-template>
  </xsl:variable>

  <xsl:choose>
    <xsl:when test="ancestor::refentry/@id=concat('function.', translate($argument,'_','-'))">
       <xsl:copy-of select="$content"/>
    </xsl:when>
    <xsl:when test="string-length($function.href) != 0">
      <a>
        <xsl:attribute name="href">
          <xsl:value-of select="$function.href"/>
        </xsl:attribute>
        <xsl:copy-of select="$content"/>
      </a>
    </xsl:when>
    <xsl:otherwise>
        <xsl:copy-of select="$content"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- Do not add markup to FUNCTION when operating in no.anchor.mode (e.g. ToC) -->
<xsl:template match="function" mode="no.anchor.mode">
  <xsl:apply-templates/>
</xsl:template>


<!-- EXTERNAL LINKS are the same as in DocBook XSL, except that we print out 
     a &raquo; HTML entity before the link and add a CSS class to it -->
<xsl:template match="ulink" name="ulink">
    <a class="ulink">
      <xsl:if test="@id">
        <xsl:attribute name="name">
          <xsl:value-of select="@id"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:attribute name="href"><xsl:value-of select="@url"/></xsl:attribute>
      <xsl:if test="$ulink.target != ''">
        <xsl:attribute name="target">
          <xsl:value-of select="$ulink.target"/>
        </xsl:attribute>
      </xsl:if>

      <xsl:text disable-output-escaping="yes">&amp;raquo; </xsl:text>
 
      <xsl:choose>
        <xsl:when test="count(child::node())=0">
          <xsl:value-of select="@url"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates/>
        </xsl:otherwise>
      </xsl:choose>
    </a>
</xsl:template>

</xsl:stylesheet>
