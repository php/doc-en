<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  quickref.xsl: Stylesheet for generating quick-reference

  $Id: quickref.xsl,v 1.2 2004-11-14 17:34:27 techtonik Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:output method="text"/>

<xsl:template match="*"/>

<xsl:template match="/">
  <xsl:apply-templates select="//refnamediv">
     <xsl:sort select="refname"/>
  </xsl:apply-templates>
</xsl:template>

<xsl:template match="refnamediv">
  <xsl:value-of select="normalize-space(refname)"/>
  <xsl:text> - </xsl:text>
  <xsl:value-of select="normalize-space(refpurpose)"/>
  <xsl:text>
</xsl:text>
</xsl:template>

</xsl:stylesheet>