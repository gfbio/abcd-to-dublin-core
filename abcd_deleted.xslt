<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:oai="http://www.openarchives.org/OAI/2.0/" xmlns:so="https://ws.gfbio.org/so/" xmlns:abcd="http://www.tdwg.org/schemas/abcd/2.06" xmlns:efg="http://www.synthesys.info/ABCDEFG/1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:php="http://php.net/xsl" exclude-result-prefixes="php" extension-element-prefixes="php">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<oai:record>
			<oai:header status="deleted">
				<oai:identifier>
					<xsl:text>urn:gfbio.org:abcd:set:</xsl:text>
					<xsl:value-of select="/abcd:DataSets/abcd:DataSet/so:BMS_ArchiveFolder"/>
				</oai:identifier>
				<oai:datestamp>2018-01-01T00:00:00Z</oai:datestamp>
			</oai:header>
		</oai:record>
		<xsl:for-each select="abcd:DataSets/abcd:DataSet/abcd:Units/abcd:Unit">
			<oai:record>
				<oai:header status="deleted">
					<oai:identifier>
            <xsl:text>urn:gfbio.org:abcd:</xsl:text>
            <xsl:value-of select="../../so:BMS_ArchiveFolder"/>
            <xsl:text>:</xsl:text>
            <xsl:value-of select="translate(translate(normalize-space(abcd:UnitID),' ',''),'/','-')"/>					
					</oai:identifier>
					<oai:datestamp>2018-01-01T00:00:00Z</oai:datestamp>
				</oai:header>
			</oai:record>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
