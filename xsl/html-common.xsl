<!-- 

  Common HTML customizations

  $Id: html-common.xsl,v 1.1 2002-01-06 15:42:31 hholzgra Exp $

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:param name="funcsynopsis.style">ansi</xsl:param>
<xsl:param name="funcsynopsis.decoration">1</xsl:param>
<xsl:param name="refentry.generate.name">0</xsl:param>

<!-- Load version information into variable -->
<xsl:param name="version" select="document('version.xml')/versions"/>

<!-- We do not want semicolon at the end of prototype -->
<xsl:template match="paramdef">
  <xsl:variable name="paramnum">
    <xsl:number count="paramdef" format="1"/>
  </xsl:variable>
  <xsl:if test="$paramnum=1">(</xsl:if>
  <xsl:choose>
    <xsl:when test="$funcsynopsis.style='ansi'">
      <xsl:apply-templates/>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates select="./parameter"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:choose>
    <xsl:when test="following-sibling::paramdef">
      <xsl:text>, </xsl:text>
    </xsl:when>
    <xsl:otherwise>
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
