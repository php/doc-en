<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  quickref.xsl: Stylesheet for generating quick-reference

  $Id: quickref.xsl,v 1.4 2004-11-14 20:37:29 techtonik Exp $

-->
<!DOCTYPE xsl:stylesheet [

<!ENTITY lowercase "'abcdefghijklmnopqrstuvwxyz'">
<!ENTITY uppercase "'ABCDEFGHIJKLMNOPQRSTUVWXYZ'">

]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:output method="text"/>

<xsl:param name="sortbycase" select="0"/>

<xsl:template match="*"/>

<xsl:template match="/">
  <xsl:choose>
  <xsl:when test="$sortbycase">
    <xsl:apply-templates select="//refnamediv">
        <xsl:sort select="refname"/>
    </xsl:apply-templates>
  </xsl:when>
  <xsl:otherwise>
    <xsl:apply-templates select="//refnamediv">
        <xsl:sort select="translate(refname,&lowercase;,&uppercase;)"/>
    </xsl:apply-templates>
  </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="refnamediv">
  <!-- function reference names can't contain spaces -->
  <xsl:if test="not (contains(normalize-space(refname),' '))">
    <xsl:value-of select="normalize-space(refname)"/>
    <xsl:text> - </xsl:text>
    <xsl:value-of select="normalize-space(refpurpose)"/>
    <xsl:text>&#10;</xsl:text>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>