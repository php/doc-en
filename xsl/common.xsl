<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  Common customizations for all formats

  $Id: common.xsl,v 1.5 2002-02-10 12:13:23 goba Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<!-- Configuration parameters -->
<xsl:param name="funcsynopsis.style" select="'ansi'"/>
<xsl:param name="funcsynopsis.decoration" select="'1'"/>
<xsl:param name="refentry.generate.name" select="'0'"/>

<!-- Turn off separators on reference and refentry pages -->
<xsl:param name="refentry.separator" select="'0'"/>
<xsl:template name="reference.titlepage.separator"/>

<!-- Load version information into variable -->
<xsl:param name="version" select="document('version.xml')/versions"/>

<!-- We do not want semicolon at the end of prototype and our own style
     of square brackets for optional parameters -->
<xsl:template match="paramdef/parameter">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="paramdef/parameter/optional">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="paramdef">
  <xsl:if test="preceding-sibling::paramdef=false()">(</xsl:if>
  <xsl:apply-templates />
  <xsl:choose>
    <xsl:when test="following-sibling::paramdef">
      <xsl:choose>
        <xsl:when test="following-sibling::paramdef[position()=1]/child::parameter/child::optional">
          <xsl:text> [, </xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>, </xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
      <xsl:for-each select="preceding-sibling::paramdef/child::parameter/child::optional">
        <xsl:text>]</xsl:text>
      </xsl:for-each>
      <xsl:if test="child::parameter/child::optional">
        <xsl:text>]</xsl:text>
      </xsl:if>
      <xsl:text>)</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- same for docbook 4 style protos -->
<xsl:template match="methodsynopsis">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="methodsynopsis/void">
  <xsl:text>(void)</xsl:text>
</xsl:template>

<xsl:template match="methodsynopsis/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodparam/type">
  <xsl:apply-templates />
  <xsl:text> </xsl:text>
</xsl:template>

<xsl:template match="methodparam/parameter">
  <xsl:apply-templates />
</xsl:template>

<xsl:template match="methodparam">
  <xsl:if test="preceding-sibling::methodparam=false()">(</xsl:if>
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
      <xsl:for-each select="preceding-sibling::methodparam/attribute::choice[.='opt']">
        <xsl:text>]</xsl:text>
      </xsl:for-each>
      <xsl:if test="child::methodparam/attribute::choice[.='opt']">
        <xsl:text>]</xsl:text>
      </xsl:if>
      <xsl:text>)</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>
