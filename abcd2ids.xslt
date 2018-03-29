<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:oai="http://www.openarchives.org/OAI/2.0/" xmlns:abcd="http://www.tdwg.org/schemas/abcd/2.06" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:for-each select="abcd:DataSets/abcd:DataSet/abcd:Units/abcd:Unit">
		<xsl:if test="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString">
				<oai:header>
					<oai:identifier>urn:gfbio.org:abcd:<xsl:value-of select="translate(translate(normalize-space(abcd:UnitID),' ',''),'/','-')"/>
					</oai:identifier>
					<xsl:if test="../../abcd:Metadata/abcd:RevisionData/abcd:DateModified">
						<oai:datestamp>							
						<xsl:value-of select="substring(../../abcd:Metadata/abcd:RevisionData/abcd:DateModified,0,11)"/>
						</oai:datestamp>
					</xsl:if>
				</oai:header>
				</xsl:if>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
