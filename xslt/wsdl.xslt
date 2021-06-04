<?xml version="1.0" encoding="UTF-8"?>
<!--suppress XmlDefaultAttributeValue, JSUnresolvedLibraryURL -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/">
    <xsl:output method="xml" version="1.0" indent="no"
                doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
                doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
                omit-xml-declaration="yes"
    />

    <xsl:template match="/">
        <html lang="hu">
            <head>
                <title>
                    <xsl:value-of select="definitions/@name"/> Web Service
                </title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css"
                      integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x"
                      crossorigin="anonymous"/>
                <link rel="preconnect" href="https://fonts.gstatic.com"/>
                <link rel="stylesheet"
                      href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,400;0,700;1,400;1,700&amp;family=Roboto:ital,wght@0,400;0,700;1,400;1,700&amp;display=swap"/>
                <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css"
                      integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay"
                      crossorigin="anonymous"/>
            </head>
            <body>
                <h1>
                    <xsl:value-of select="wsdl:definitions/@name"/>
                </h1>

                <xsl:apply-templates select="wsdl:definitions"/>

                <!--suppress CheckTagEmptyBody -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
                        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
                        crossorigin="anonymous"></script>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="wsdl:definitions">
        <h3>Service documentation</h3>
        <dl>
            <dt>Target namespace</dt>
            <dd>
                <xsl:value-of select="@targetNamespace"/>
            </dd>
        </dl>

        <xsl:apply-templates select="wsdl:portType"/>
        <xsl:apply-templates select="wsdl:types"/>

    </xsl:template>

    <xsl:template match="wsdl:portType">
        <h2>Operations</h2>
        <ul>
            <xsl:apply-templates select="wsdl:operation"/>
        </ul>
    </xsl:template>

    <xsl:template match="wsdl:operation">
        <li>
            <xsl:value-of select="@name"/>
            <xsl:if test="wsdl:documentation!=''">
                <p><i><xsl:value-of select="wsdl:documentation"/></i></p>
            </xsl:if>
        </li>
    </xsl:template>

    <xsl:template match="wsdl:types">
        <h2>Data types</h2>
        <xsl:apply-templates />
    </xsl:template>

    <xsl:template match="xsd:element">
        <tr>
            <td><xsl:value-of select="@name" /></td>
            <td><xsl:value-of select="@type" /></td>
            <td>
                <xsl:choose>
                    <xsl:when test="@minOccurs">
                        <xsl:value-of select="@minOccurs" />
                    </xsl:when>
                    <xsl:otherwise>
                        1
                    </xsl:otherwise>
                </xsl:choose>
                ..
                <xsl:choose>
                    <xsl:when test="@maxOccurs">
                        <xsl:value-of select="@maxOccurs" />
                    </xsl:when>
                    <xsl:otherwise>
                        1
                    </xsl:otherwise>
                </xsl:choose>
            </td>
            <td>
                <xsl:value-of select="xsd:annotation/xsd:documentation" />
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="xsd:complexType">
        <h3><xsl:value-of select="@name"/></h3>
        <p><xsl:value-of select="xsd:annotation/xsd:documentation" /></p>
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Cardinality</th>
                <th>Description</th>
            </tr>

            <xsl:apply-templates select="xsd:sequence|xsd:complexContent|xsd:choice|xsd:all" />
        </table>
    </xsl:template>

</xsl:stylesheet>
