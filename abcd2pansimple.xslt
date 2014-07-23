<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions" xmlns:oai="http://www.openarchives.org/OAI/2.0/" xmlns:abcd="http://www.tdwg.org/schemas/abcd/2.06" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:for-each select="abcd:DataSets/abcd:DataSet/abcd:Units/abcd:Unit">
			<oai:record>
				<oai:header>
					<oai:identifier>urn:gfbio.org:abcd:<xsl:value-of select="translate(normalize-space(abcd:UnitID),' ','')"/>
					</oai:identifier>
					<!---<oai:datestamp>
						<xsl:value-of select="../../abcd:Metadata/abcd:RevisionData/abcd:DateModified"/>
					</oai:datestamp>-->
				</oai:header>
				<oai:metadata>
					<dataset xmlns="urn:pangaea.de:dataportals" xmlns:dc="http://purl.org/dc/elements/1.1/">
						<dc:title>
							<xsl:choose>
								<xsl:when test="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString">
									<xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="abcd:RecordBasis"/>
								</xsl:otherwise>
							</xsl:choose> [<xsl:value-of select="abcd:UnitID"/>]</dc:title>
						<xsl:for-each select="../../abcd:ContentContacts">
							<dc:contributor>
								<xsl:value-of select="abcd:ContentContact/abcd:Name"/>
							</dc:contributor>
						</xsl:for-each>
						<xsl:for-each select="abcd:Gathering/abcd:Agents">
							<dc:contributor>
								<xsl:choose>
									<xsl:when test="abcd:GatheringAgent/abcd:Person/abcd:FullName!=''">
										<xsl:value-of select="abcd:GatheringAgent/abcd:Person/abcd:FullName"/>
									</xsl:when>
									<xsl:when test="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:InheritedName!=''">
										<xsl:value-of select="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:InheritedName"/>
										<xsl:if test="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:GivenNames!=''">, <xsl:value-of select="abcd:GatheringAgent/abcd:Person/abcd:AtomisedName/abcd:GivenNames"/>
										</xsl:if>
									</xsl:when>
									<xsl:when test="abcd:GatheringAgent/abcd:AgentText!=''">
										<xsl:value-of select="abcd:GatheringAgent/abcd:AgentText"/>
									</xsl:when>
									<xsl:when test="abcd:GatheringAgentsText!=''">
										<xsl:value-of select="abcd:GatheringAgentsText"/>
									</xsl:when>
								</xsl:choose>
							</dc:contributor>
						</xsl:for-each>
						<!---
						<dc:date>
							<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin"/>
						</dc:date>-->
						<dc:publisher>
							<xsl:choose>
								<xsl:when test="../../abcd:Metadata/abcd:Owners/abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text">
									<xsl:value-of select="../../abcd:Metadata/abcd:Owners/abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../../abcd:Metadata/abcd:Description/abcd:Representation/abcd:Title"/>
								</xsl:otherwise>
							</xsl:choose>
						</dc:publisher>
						<dataCenter>
							<xsl:choose>
								<xsl:when test="../../abcd:Metadata/abcd:Owners/abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text">
									<xsl:value-of select="../../abcd:Metadata/abcd:Owners/abcd:Owner/abcd:Organisation/abcd:Name/abcd:Representation/abcd:Text"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="../../abcd:Metadata/abcd:Description/abcd:Representation/abcd:Title"/>
								</xsl:otherwise>
							</xsl:choose>
						</dataCenter>
						<dc:type>
							<xsl:choose>
								<xsl:when test="contains(abcd:RecordBasis,'Specimen') or contains(abcd:KindOfUnit,'Specimen')">PhysicalObject</xsl:when>
								<xsl:when test="contains(abcd:RecordBasis,'Observation') or contains(abcd:KindOfUnit,'Observation')">Dataset</xsl:when>
								<xsl:when test="contains(abcd:RecordBasis,'Photograph') or contains(abcd:KindOfUnit,'Photograph') or abcd:RecordBasis='MultimediaObject' or abcd:KindOfUnit=MultimediaObject">Image</xsl:when>
							</xsl:choose>
						</dc:type>
						<dc:format>text/html</dc:format>
						<linkage type="metadata">
							<xsl:value-of select="abcd:RecordURI"/>
						</linkage>
						<dc:identifier>
							<xsl:value-of select="abcd:UnitID"/>
						</dc:identifier>
						<dc:coverage xsi:type="CoverageType">
							<xsl:if test="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal!=''">
								<northBoundLatitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal"/>
								</northBoundLatitude>
								<westBoundLongitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LongitudeDecimal"/>
								</westBoundLongitude>
								<southBoundLatitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LatitudeDecimal"/>
								</southBoundLatitude>
								<eastBoundLongitude>
									<xsl:value-of select="abcd:Gathering/abcd:SiteCoordinateSets/abcd:SiteCoordinates/abcd:CoordinatesLatLong/abcd:LongitudeDecimal"/>
								</eastBoundLongitude>
							</xsl:if>
							<xsl:if test="abcd:Gathering/abcd:Country/abcd:Name!=''">
								<location>
									<xsl:value-of select="abcd:Gathering/abcd:Country/abcd:Name"/>
								</location>
							</xsl:if>
							<xsl:if test="abcd:Gathering/abcd:LocalityText!=''">
								<location>
									<xsl:value-of select="abcd:Gathering/abcd:LocalityText"/>
								</location>
							</xsl:if>
							<xsl:for-each select="abcd:Gathering/abcd:NamedAreas">
								<location>
									<xsl:value-of select="abcd:NamedArea/abcd:AreaName"/>
								</location>
							</xsl:for-each>
							<xsl:choose>
								<xsl:when test="abcd:Gathering/abcd:DateTime/abcd:DateText!=''">
									<startDate>
										<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:DateText[1]"/>
									</startDate>
									<endDate>
										<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:DateText"/>
									</endDate>
								</xsl:when>
								<xsl:when test="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin!=''">
									<startDate>
										<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeBegin"/>
									</startDate>
								</xsl:when>
								<xsl:when test="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeEnd!=''">
									<endDate>
										<xsl:value-of select="abcd:Gathering/abcd:DateTime/abcd:ISODateTimeEnd"/>
									</endDate>
								</xsl:when>
							</xsl:choose>
						</dc:coverage>
						<dc:subject xsi:type="SubjectType" type="taxonomy">
							<xsl:value-of select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:ScientificName/abcd:FullScientificNameString"/>
						</dc:subject>
						<xsl:for-each select="abcd:Identifications/abcd:Identification/abcd:Result/abcd:TaxonIdentified/abcd:HigherTaxa">
							<dc:subject xsi:type="SubjectType" type="taxonomy">
								<xsl:value-of select="abcd:HigherTaxon/abcd:HigherTaxonName"/>
							</dc:subject>
						</xsl:for-each>
						<dc:rights>
							<xsl:value-of select="../../abcd:Metadata/abcd:IPRStatements/abcd:TermsOfUseStatements/abcd:TermsOfUse/abcd:Text"/>
						</dc:rights>
					</dataset>
				</oai:metadata>
			</oai:record>
		</xsl:for-each>
	</xsl:template>
</xsl:stylesheet>
