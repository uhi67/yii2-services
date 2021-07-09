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
                <link rel="stylesheet" href="?xslt&amp;f=wsdl.css"/>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"/>
                <link rel="preconnect" href="https://fonts.gstatic.com"/>
                <link rel="stylesheet"
                      href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,400;0,700;1,400;1,700&amp;family=Roboto:ital,wght@0,400;0,700;1,400;1,700&amp;display=swap"/>
                <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css"
                      integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay"
                      crossorigin="anonymous"/>
            </head>
            <body>
                <header class="navbar navbar-dark">
                    <div class="container">
                        <h1><xsl:value-of select="wsdl:definitions/@name"/></h1>
                    </div>
                </header>
                <div class="container">
                    <xsl:apply-templates select="wsdl:definitions"/>

                    <!--suppress CheckTagEmptyBody -->
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
                            integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
                            crossorigin="anonymous"></script>
                </div>
                <script type="text/javascript" src="?xslt&amp;f=showdown.js"/>
                <script type="text/javascript" src="?xslt&amp;f=wsdl.js"/>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="wsdl:definitions[not(@opName)]">
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
            <b><a href="?doc&amp;o={@name}"><xsl:value-of select="@name"/></a></b>
            <xsl:if test="wsdl:documentation!=''">
                <p class="summary"><xsl:value-of select="substring-before(wsdl:documentation, '&#10;')"/></p>
            </xsl:if>
        </li>
    </xsl:template>

    <xsl:template match="wsdl:types">
        <h2>Data types</h2>
        <xsl:apply-templates />
    </xsl:template>

    <xsl:template match="wsdl:definitions[@opName]">
        <p>Back to the complete <a href="?doc">service documentation</a></p>
        <xsl:apply-templates select="wsdl:portType/wsdl:operation[@name=current()/@opName]" mode="details"/>
    </xsl:template>

    <xsl:template match="wsdl:operation" mode="details">
        <h2><xsl:value-of select="@name"/></h2>
        <xsl:if test="wsdl:documentation!=''">
            <div class="showdown"><pre class="markdown"><xsl:value-of select="wsdl:documentation"/></pre></div>
        </xsl:if>
        <xsl:apply-templates select="wsdl:input|wsdl:output"/>
        <h3>Sample request</h3>
        <pre>
POST <xsl:value-of select="/wsdl:definitions/@uri"/> HTTP/1.1
Host: <xsl:value-of select="/wsdl:definitions/@host"/>
Content-Type: text/xml; charset=utf-8
Content-Length: length

&lt;soapenv:Envelope xmlns:ns="<xsl:value-of select="/wsdl:definitions/@targetNamespace"/>" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
    &lt;soapenv:Header xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"/>
    &lt;soapenv:Body xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
        &lt;ns:<xsl:value-of select="@name"/><xsl:text>></xsl:text>
            <xsl:apply-templates select="wsdl:input" mode="sample"/>
        &lt;/ns:<xsl:value-of select="@name"/>>
    &lt;/soapenv:Body>
&lt;/soapenv:Envelope>
        </pre>

        <h3>Test call</h3>
        <nav>
        <div id="tab-testcall" class="nav nav-tabs" role="tablist">
            <button id="xml-tab" class="nav-link active" data-bs-toggle="tab" data-bs-target="#testcall-xml" aria-controls="XML" aria-selected="true" role="tab">XML</button>
            <button id="fields-tab" class="nav-link" data-bs-toggle="tab" data-bs-target="#testcall-fields" aria-controls="fields" aria-selected="false" role="tab">Field values</button>
        </div>
        </nav>
        <div class="tab-content" id="tab-testcallContent">
            <div class="tab-pane fade show active" id="testcall-xml" role="tabpanel" aria-labelledby="xml-tab">
                <form id="form-testcall-xml" action="{/wsdl:definitions/@uri}" method="post" enctype="text/xml">
                    <textarea id="xml">&lt;soapenv:Envelope xmlns:ns="<xsl:value-of select="/wsdl:definitions/@targetNamespace"/>" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/">
    &lt;soapenv:Header xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"/>
    &lt;soapenv:Body xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
        &lt;ns:<xsl:value-of select="@name"/><xsl:text>></xsl:text>
            <xsl:apply-templates select="wsdl:input" mode="pattern"/>
        &lt;/ns:<xsl:value-of select="@name"/>>
    &lt;/soapenv:Body>
&lt;/soapenv:Envelope>
</textarea>
                    <button type="button" id="submit-xml">Submit</button>
                </form>
            </div>
            <div class="tab-pane fade" id="testcall-fields" role="tabpanel" aria-labelledby="fields-tab">
                <form id="form-testcall-fields" action="{/wsdl:definitions/@uri}" method="post" enctype="text/xml">
                    <xsl:apply-templates select="wsdl:input" mode="form"/>
                    <pre id="xml-data" style="display:none">
        &lt;soapenv:Envelope xmlns:ns="<xsl:value-of select="/wsdl:definitions/@targetNamespace"/>" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            &lt;soapenv:Header xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"/>
            &lt;soapenv:Body xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                &lt;ns:<xsl:value-of select="@name"/><xsl:text>></xsl:text>
                    <xsl:apply-templates select="wsdl:input" mode="pattern"/>
                &lt;/ns:<xsl:value-of select="@name"/>>
            &lt;/soapenv:Body>
        &lt;/soapenv:Envelope>
                    </pre>
                    <button type="button" id="submit-fields">Submit</button>
                </form>
            </div>
        </div>

        <div id="response-container" class="hidden">
            <h3>Response</h3>
            <div id="response"></div>
        </div>
    </xsl:template>

    <xsl:template match="wsdl:input">
        <xsl:variable name="name" select="../@name" />
        <h3>Input</h3>
        <xsl:apply-templates select="/wsdl:definitions/wsdl:message[@name=concat($name, 'In')]"/>
    </xsl:template>

    <xsl:template match="wsdl:input" mode="form">
        <xsl:variable name="name" select="../@name" />
        <xsl:apply-templates select="/wsdl:definitions/wsdl:message[@name=concat($name, 'In')]" mode="input"/>
    </xsl:template>

    <xsl:template match="wsdl:output">
        <xsl:variable name="name" select="../@name" />
        <h3>Output</h3>
        <xsl:apply-templates select="/wsdl:definitions/wsdl:message[@name=concat($name, 'Out')]"/>
    </xsl:template>

    <xsl:template match="wsdl:message" mode="input">
        <xsl:apply-templates select="wsdl:part" mode="input" />
    </xsl:template>

    <xsl:template match="wsdl:message">
        <table class="table table-striped table-bordered table-hover">
            <tr>
                <th>Name</th>
                <th>Type</th>
            </tr>
            <xsl:apply-templates select="wsdl:part" />
        </table>
    </xsl:template>

    <xsl:template match="wsdl:part" mode="input">
        <div class="form-group row">
            <label for="input_{@name}" class="col-sm-2"><xsl:value-of select="@name"/></label>
            <div class="col-sm-10">
                <input name="{@name}" id="input_{@name}" class="form-control"/>
                <div class="help-block"><xsl:value-of select="@type" /></div>
            </div>
        </div>
    </xsl:template>

    <xsl:template match="wsdl:part">
        <tr>
            <td><xsl:value-of select="@name" /></td>
            <td><xsl:value-of select="@type" /></td>
        </tr>
    </xsl:template>

    <xsl:template match="wsdl:input" mode="sample">
        <xsl:variable name="name" select="../@name" />
        <xsl:apply-templates select="/wsdl:definitions/wsdl:message[@name=concat($name, 'In')]" mode="sample"/>
    </xsl:template>

    <xsl:template match="wsdl:input" mode="pattern">
        <xsl:variable name="name" select="../@name" />
        <xsl:apply-templates select="/wsdl:definitions/wsdl:message[@name=concat($name, 'In')]" mode="pattern"/>
    </xsl:template>

    <xsl:template match="wsdl:message" mode="sample">
        <xsl:apply-templates select="wsdl:part" mode="sample"/>
    </xsl:template>

    <xsl:template match="wsdl:message" mode="pattern">
        <xsl:apply-templates select="wsdl:part" mode="pattern"/>
    </xsl:template>

    <xsl:template match="wsdl:part" mode="sample">
        <xsl:text>
            &lt;</xsl:text><xsl:value-of select="@name" />><xsl:call-template name="sample"><xsl:with-param name="type" select="@type"/></xsl:call-template>&lt;/<xsl:value-of select="@name" /><xsl:text>></xsl:text>
    </xsl:template>

    <xsl:template match="wsdl:part" mode="pattern">
        <xsl:text>
            &lt;</xsl:text><xsl:value-of select="@name" /><xsl:apply-templates select="@type" mode="xtype"/>>{<xsl:value-of select="@name" />}&lt;/<xsl:value-of select="@name" /><xsl:text>></xsl:text>
    </xsl:template>

    <xsl:template match="@*" mode="xtype">
        <xsl:text> xsi:type="</xsl:text>
        <xsl:choose>
            <xsl:when test=".='soap-enc:Array'">ns2:Map</xsl:when>
            <xsl:otherwise><xsl:value-of select="."/></xsl:otherwise>
        </xsl:choose>
        <xsl:text>"</xsl:text>
    </xsl:template>

    <xsl:template name="sample">
        <xsl:param name="type"/>
        <xsl:choose>
            <xsl:when test="substring-before($type, ':')='xsd'"><xsl:value-of select="substring-after($type, ':')"/></xsl:when>
            <xsl:when test="substring-before($type, ':')='tns'">
                <!-- recurse custom type-->
                <xsl:value-of select="$type"/>
            </xsl:when>
            <xsl:otherwise><xsl:value-of select="$type"/></xsl:otherwise>
        </xsl:choose>
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
            <td>
                <xsl:value-of select="xsd:annotation/xsd:appinfo" />
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
                <th>Example</th>
            </tr>

            <xsl:apply-templates select="xsd:sequence|xsd:complexContent|xsd:choice|xsd:all" />
        </table>
    </xsl:template>

</xsl:stylesheet>
