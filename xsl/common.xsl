<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  common.xsl: Common customizations for all formats

  $Id: common.xsl,v 1.13 2004-11-01 12:16:55 techtonik Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">


<!-- Colorize background for programlisting and screens
     will go into CSS probably in next DocBook version --> 
<xsl:param name="shade.verbatim" select="1"/>


<!-- *************** TUNE FUNCTION REFERENCE PAGES **************** -->
<!-- Generate title from function name instead of word "NAME" in title -->
<xsl:param name="refentry.generate.name" select="'0'"/>
<xsl:param name="refentry.generate.title" select="'1'"/>

<!-- Turn off separators on reference and refentry pages -->
<xsl:param name="refentry.separator" select="'0'"/>
<xsl:template name="reference.titlepage.separator"/>
<!-- FIX: temporary till also this is in std.-distrib. -->
<xsl:template match="reference/titleabbrev"/>


<!-- Load VERSION INFORMATION into variable -->
<xsl:param name="version" select="document('version.xml')/versions"/>


<!-- We do not want semicolon at the end of prototype and our own style
     of square brackets for optional parameters -->
<xsl:template match="methodsynopsis">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="methodsynopsis/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodsynopsis/void">
  <xsl:text> (void)</xsl:text>
</xsl:template>

<xsl:template match="methodparam/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodparam/parameter">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="methodparam">
  <xsl:if test="preceding-sibling::methodparam=false()">
    <xsl:text> (</xsl:text>
    <xsl:if test="@choice='opt'">
      <xsl:text>[</xsl:text>
    </xsl:if>
  </xsl:if>
  <xsl:apply-templates />
  <xsl:choose>
    <xsl:when test="following-sibling::methodparam">
      <xsl:choose>
        <xsl:when test="following-sibling::methodparam[position()=1]/attribute::choice[.='opt']">
          <xsl:text> [, </xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>, </xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:for-each select="preceding-sibling::methodparam">
				<xsl:if test="attribute::choice[.='opt']">
					<xsl:text>]</xsl:text>
				</xsl:if>
      </xsl:for-each>
      <xsl:if test="self::methodparam/attribute::choice[.='opt']">
        <xsl:text>]</xsl:text>
      </xsl:if>
      <xsl:text>)</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>


<!-- Remove whitespace before and after the contents of
     programlisting and screen tags used for PHP code and
     output examples
     
     Thanks to Peter Kullman for the initial version of this code
-->
<xsl:template match="screen/text()|programlisting/text()">
 <xsl:variable name="before" select="preceding-sibling::node()"/>
 <xsl:variable name="after" select="following-sibling::node()"/>

 <xsl:variable name="conts" select="."/>

 <xsl:variable name="contsl">
  <xsl:choose>
   <xsl:when test="count($before) = 0">
    <xsl:call-template name="remove-ws-left">
     <xsl:with-param name="astr" select="$conts"/>
    </xsl:call-template>
   </xsl:when>
   <xsl:otherwise>
    <xsl:value-of select="$conts"/>
   </xsl:otherwise>
  </xsl:choose>
 </xsl:variable>

 <xsl:variable name="contslr">
  <xsl:choose>
   <xsl:when test="count($after) = 0">
    <xsl:call-template name="remove-ws-right">
     <xsl:with-param name="astr" select="$contsl"/>
    </xsl:call-template>
   </xsl:when>
   <xsl:otherwise>
    <xsl:value-of select="$contsl"/>
   </xsl:otherwise>
  </xsl:choose>
 </xsl:variable>

 <xsl:value-of select="$contslr"/>

</xsl:template>

<!-- Remove whitespace from the left of a string -->
<xsl:template name="remove-ws-left">
 <xsl:param name="astr"/>

 <xsl:choose>
  <xsl:when test="starts-with($astr,'&#xA;') or
                  starts-with($astr,'&#xD;') or
                  starts-with($astr,'&#x20;') or
                  starts-with($astr,'&#x9;')">
   <xsl:call-template name="remove-ws-left">
    <xsl:with-param name="astr" select="substring($astr, 2)"/>
   </xsl:call-template>
  </xsl:when>
  <xsl:otherwise>
   <xsl:value-of select="$astr"/>
  </xsl:otherwise>
 </xsl:choose>
</xsl:template>

<!-- Remove whitespace from the right of a string -->
<xsl:template name="remove-ws-right">
 <xsl:param name="astr"/>

 <xsl:variable name="last-char">
  <xsl:value-of select="substring($astr, string-length($astr), 1)"/>
 </xsl:variable>

 <xsl:choose>
  <xsl:when test="($last-char = '&#xA;') or
                  ($last-char = '&#xD;') or
                  ($last-char = '&#x20;') or
                  ($last-char = '&#x9;')">
   <xsl:call-template name="remove-ws-right">
    <xsl:with-param name="astr"
     select="substring($astr, 1, string-length($astr) - 1)"/>
   </xsl:call-template>
  </xsl:when>
  <xsl:otherwise>
   <xsl:value-of select="$astr"/>
  </xsl:otherwise>
 </xsl:choose>
</xsl:template>



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

</xsl:stylesheet>
