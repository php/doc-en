<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  quickref.xsl: Stylesheet for generating quick-reference

  $Id: quickref.xsl,v 1.2 2007-04-22 09:37:31 hholzgra Exp $

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
    <xsl:apply-templates select="//part[@id != 'internals']/descendant::refnamediv">
        <xsl:sort select="refname"/>
    </xsl:apply-templates>
  </xsl:when>
  <xsl:otherwise>
    <xsl:apply-templates select="//part[@id != 'internals']/descendant::refnamediv">
      <xsl:sort select="translate(refname,&lowercase;,&uppercase;)"/>
    </xsl:apply-templates>
  </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template match="refnamediv">
  <!-- function reference names can't contain spaces -->
  <xsl:for-each select="refname[not(contains(normalize-space(),' '))]">
    <xsl:value-of select="normalize-space()"/>
    <xsl:text> - </xsl:text>
    <xsl:value-of select="normalize-space(../refpurpose)"/>
    <xsl:text>&#10;</xsl:text>
  </xsl:for-each>
</xsl:template>

</xsl:stylesheet>
