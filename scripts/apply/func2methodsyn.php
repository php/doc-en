<?php

$conversion_xsl='
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                version="1.0">

<xsl:output method="xml" />

<!-- default rule -> copy element, attributes and recursive content -->
<xsl:template match="*">
  <xsl:copy>
    <xsl:copy-of select="@*"/>
    <xsl:apply-templates/>
  </xsl:copy>
</xsl:template>

<!-- ignore funcsynopsis tags -->
<xsl:template match="/funcsynopsis">
     <xsl:apply-templates/>   
</xsl:template>

<!-- convert foncprototype to methodsynopsis -->
<xsl:template match="/funcsynopsis/funcprototype">
   <methodsynopsis>
    <xsl:apply-templates/>
   </methodsynopsis>
</xsl:template>

<!-- ignore funcdef tag -->
<xsl:template match="/funcsynopsis/funcprototype/funcdef">
    <xsl:apply-templates/>
</xsl:template>

<!-- function is now methodname in this context -->
<xsl:template match="/funcsynopsis/funcprototype/funcdef/function">
<methodname><xsl:apply-templates/></methodname>
</xsl:template>

<!-- replaceable is now methodname.replaceable in this context -->
<xsl:template match="/funcsynopsis/funcprototype/funcdef/replaceable">
<methodname><replaceable><xsl:apply-templates/></replaceable></methodname>
</xsl:template>

<!-- first text element is the return type
     needs to be enclosed in type tags now
-->
<xsl:template match="/funcsynopsis/funcprototype/funcdef/text()[1]">
  <xsl:if test="position() = 1"> <!-- first only -->
    <type>
     <xsl:value-of select="normalize-space(.)"/>
    </type>
  </xsl:if>
</xsl:template>

<!-- paramdef is now methodparam, empty paramdef should be void/ -->
<xsl:template match="/funcsynopsis/funcprototype/paramdef">
  <xsl:choose>
    <xsl:when test="count(parameter)>0">
    <methodparam>
     <xsl:if test="*/optional"> <!-- optional is now attribute and not special tag -->
      <xsl:attribute name="choice">opt</xsl:attribute>
     </xsl:if>
     <xsl:apply-templates/>
    </methodparam>
  </xsl:when>
  <xsl:otherwise>
    <void/>
  </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<!-- first text in paramdef is paramter type and needs type tags -->
<xsl:template match="/funcsynopsis/funcprototype/paramdef/text()[1]">
  <xsl:if test="position() = 1">
     <type>
      <xsl:value-of select="normalize-space(.)"/>
     </type>
  </xsl:if>
</xsl:template>

<!-- ignore optional tag here, already processed above -->
<xsl:template match="/funcsynopsis/funcprototype/paramdef/parameter/optional">
      <xsl:apply-templates/>
</xsl:template>

<!-- there is no varargs in methodsynopsis, but a rep attribute for methodparam -->
<xsl:template match="/funcsynopsis/funcprototype/varargs">
     <methodparam rep="repeat"><type>mixed</type><parameter>...</parameter></methodparam>
</xsl:template>

</xsl:stylesheet>
';

$xslt_processor = false;

function apply($input) {
	global $conversion_xsl, $xslt_processor;
	$output="";
	$flag=false;

	if(!function_exists("xslt_create")) {
		die("this conversion requires a PHP executable with XSLT extension");
	}

	$xmlhead="<?xml version='1.0' encoding='iso-8859-1' ?>\n";

	$lines = explode("\n",$input);
	for($nr=0;$nr<sizeof($lines);$nr++) {
		$line = $lines[$nr]."\n";

		if(strstr($line,("<funcsynopsis>"))) {
			$flag=true;
			$funcsyn = $xmlhead."\n".str_replace("&","&amp;",$line);
			do {
				$line=$lines[++$nr]."\n";;
				$funcsyn .= str_replace("&","&amp;",$line);
			} while(!strstr($line,("</funcsynopsis>")));
			$arguments = array('/_xml' => $funcsyn,
												 '/_xsl' => $conversion_xsl
												 );
			if(!is_resource($xslt_processor)) {
				$xslt_processor = xslt_create();
			}
			$result = xslt_process($xslt_processor, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
			
			if(is_string($result)) {
				$result = str_replace("&amp;","&",$result);
				$result = explode("\n",$result);
				unset($result[0]);
				$output .= rtrim(utf8_decode(join("\n",$result)))."\n";
			} else {
				echo "line $nr\n";
				echo $funcsyn;
				return false;
			}
		} else if (strstr($line,("<?xml"))&&($nr==1)) {
			$xmlhead=$line;
			$output .= $line;
		} else {
			$output .= $line;
		}
	}

	return $flag ? $output : false;
}

?>
