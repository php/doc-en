<?xml vedsion="1.0" encoding="iso-8859-1"?>
<!-- 

  Common HTML customizations

  $Id: html-common.xsl,v 1.5 2002-02-09 12:19:54 goba Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:include href="common.xsl"/>

<!-- Enclose functions in links, add parenthesis -->
<xsl:template match="function">
  <xsl:choose>
    <xsl:when test="name(parent::*)!='funcdef'">
      <xsl:choose>
        <xsl:when test="ancestor::refentry/refnamediv/refname!=translate(current(),'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')">
          <a>
	    <xsl:attribute name="href">
	      <xsl:call-template name="href.target">
		<xsl:with-param name="object" select="id(concat('function.', translate(string(current()),'_','-')))"/> 
	      </xsl:call-template>
	    </xsl:attribute>
  	    <xsl:call-template name="inline.boldseq">
	      <xsl:with-param name="content">
		<xsl:apply-templates/>
		<xsl:text>()</xsl:text>
	      </xsl:with-param>
	    </xsl:call-template>
          </a>
        </xsl:when>
        <xsl:otherwise>
	  <xsl:call-template name="inline.boldseq">
	    <xsl:with-param name="content">
	      <xsl:apply-templates/>
	      <xsl:text>()</xsl:text>
	    </xsl:with-param>
	  </xsl:call-template>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:when>
    <xsl:otherwise>
     <xsl:call-template name="inline.monoseq"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- To overcome precedence issues -->
<xsl:template match="funcdef/function">
  <xsl:choose>
    <xsl:when test="$funcsynopsis.decoration != 0">
      <b class="fsfunc"><xsl:apply-templates/></b>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- Title abbreviations are not used in HTML output AFAIK -->
<xsl:template match="titleabbrev"/>

<!-- Add version information bellow function name -->
<xsl:template match="refnamediv">
  <p>(<xsl:value-of select="$version/function[@name=string(current()/refname)]/@from"/>)</p>
  <xsl:apply-imports/>
</xsl:template>

</xsl:stylesheet>
