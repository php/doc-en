<?xml version="1.0" encoding="iso-8859-1"?>
<!-- 

  bightml.xsl: HTML specific stylesheet (not chunked)

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:import href="./docbook/html/docbook.xsl"/>
<xsl:include href="html-common.xsl"/>


<!-- don't let the copright + year-list appear twice -->
<xsl:template match="copyright" mode="titlepage.mode" />

<!-- add missing separators -->
<xsl:template match="preface|part|chapter|reference|appendix|sect1|sect2">
  <hr/>
  <xsl:apply-imports/>
</xsl:template>

<xsl:template match="reference/partintro/section">
  <xsl:apply-imports/>
</xsl:template>

<!-- Just decrease FILESIZE in this huge file -->
<xsl:template match="refentry">
  <hr/>
  <div class="{name(.)}">
    <xsl:call-template name="anchor">
      <xsl:with-param name="conditional" select="0"/>
    </xsl:call-template>
    <xsl:apply-templates/>
    <xsl:call-template name="process.footnotes"/>
  </div>
</xsl:template>


<!-- Add parenthesis FUNCTIONS, and if target exists link
     them to their refentry, otherweise make it bold  -->
<xsl:template match="function">
  <xsl:variable name="content">
    <xsl:apply-templates/><xsl:text>()</xsl:text>
  </xsl:variable>
  <xsl:variable name="targetid">
    <xsl:value-of select="concat('function.', translate(string(current()),'_','-'))"/>
  </xsl:variable>

  <xsl:choose>
      <xsl:when test="ancestor::refentry/refnamediv/refname=translate(current(),
                        'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')
                      or count(/*/part[@id='funcref']/*/refentry[@id=$targetid]) = 0">
      <b><xsl:copy-of select="$content"/></b>
    </xsl:when>
    <xsl:otherwise>
      <a>
        <xsl:attribute name="href">
          <xsl:call-template name="href.target">
            <xsl:with-param name="object" select="id($targetid)"/> 
          </xsl:call-template>
        </xsl:attribute>
        <xsl:copy-of select="$content"/>
      </a>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>



</xsl:stylesheet>
