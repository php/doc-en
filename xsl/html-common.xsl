<!-- 

  Common HTML customizations

  $Id: html-common.xsl,v 1.2 2002-01-20 22:53:09 hholzgra Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:param name="funcsynopsis.style">ansi</xsl:param>
<xsl:param name="funcsynopsis.decoration">1</xsl:param>
<xsl:param name="refentry.generate.name">0</xsl:param>

<!-- Load version information into variable -->
<xsl:param name="version" select="document('version.xml')/versions"/>

<!-- We do not want semicolon at the end of prototype and our own style
     of square brackets for optional parameters -->
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
