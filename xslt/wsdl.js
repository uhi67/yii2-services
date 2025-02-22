const WsdlDoc = (function() {
	'use strict';

	const documentLoaded = function (event) {
		const button = document.querySelector('#form-testcall-fields button#submit-fields');
		const responseContainer = document.querySelector('div#response-container');
		const response = responseContainer && responseContainer.querySelector('div#response');

		button && button.addEventListener('click', ()=>{
			response.className = 'waiting';
			response.textContent = '...';
			const form = button.parentElement;
			const xmlhttp = new XMLHttpRequest();
			let xml = document.querySelector('pre#xml-data').textContent;
			// Substitute input values into fields
			const inputs = form.querySelectorAll('input');
			inputs.forEach(inp => xml = xml.replaceAll(new RegExp('\\{'+inp.name+'\\}', 'g'), escapeXml(inp.value)));
			// console.log(xml);
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4) {
					if (xmlhttp.status == 200) {
						//Request was successful
						// console.log('Success');
						// console.log(xmlhttp.responseText);
						response.className = 'success';
						const serializer = new XMLSerializer();
						const xmlStr = serializer.serializeToString(xmlhttp.responseXML);
						response.textContent = prettifyXml(xmlStr);
					} else {
						console.log('Failure', xmlhttp.status);
						console.log(xmlhttp.response);
						response.className = 'error';
						response.textContent = xmlhttp.responseText;
					}
					responseContainer.className='';
					responseContainer.scrollIntoView(false);
					responseContainer.scrollIntoView(true);
				}
			}
			xmlhttp.open("POST", form.action);
			xmlhttp.setRequestHeader('Content-Type', 'text/xml');
			xmlhttp.send(xml);
		});

		const button1 = document.querySelector('#form-testcall-xml button#submit-xml');
		button1 && button1.addEventListener('click', ()=>{
			response.className = 'waiting';
			response.textContent = '';
			const form = button1.parentElement;
			const xmlhttp = new XMLHttpRequest();
			let xml = document.querySelector('textarea#xml').value;
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4) {
					if (xmlhttp.status == 200) {
						//Request was successful
						// console.log('Success');
						// console.log(xmlhttp.responseText);
						response.className = 'success';
						const serializer = new XMLSerializer();
						if(xmlhttp.responseXML) {
							const xmlStr = serializer.serializeToString(xmlhttp.responseXML);
							response.textContent = prettifyXml(xmlStr);
						} else {
							response.textContent = xmlhttp.responseText;
						}
					} else {
						console.log('Failure', xmlhttp.status);
						console.log(xmlhttp.response);
						response.className = 'error';
						response.textContent = xmlhttp.responseText;
					}
					responseContainer.className='';
					responseContainer.scrollIntoView(false);
					responseContainer.scrollIntoView(true);
				}
			}
			xmlhttp.open("POST", form.action);
			xmlhttp.setRequestHeader('Content-Type', 'text/xml');
			// console.log(xml);
			xmlhttp.send(xml);
		});

		showdown.setFlavor('github');
		document.querySelectorAll('pre.markdown').forEach(function(value){
			const converter = new showdown.Converter({
				simpleLineBreaks: false
			});
			value.outerHTML = converter.makeHtml(value.textContent);
		});
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', documentLoaded);
	} else {
		documentLoaded();
	}

	const prettifyXml = function(sourceXml) {
		var xmlDoc = new DOMParser().parseFromString(sourceXml, 'application/xml');
		var xsltDoc = new DOMParser().parseFromString([
			'<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">',
			'  <xsl:output indent="yes"/>',	// Notsupported in Firefox!
			'  <xsl:strip-space elements="*"/>',
			'  <xsl:template match="para[content-style][not(text())]">', // change to just text() to strip space in text nodes
			'    <xsl:value-of select="normalize-space(.)"/>',
			'  </xsl:template>',
			'  <xsl:template match="node()|@*">',
			'    <xsl:copy><xsl:apply-templates select="node()|@*"/></xsl:copy>',
			'  </xsl:template>',
			'</xsl:stylesheet>',
		].join('\n'), 'application/xml');

		var xsltProcessor = new XSLTProcessor();
		xsltProcessor.importStylesheet(xsltDoc);
		var resultDoc = xsltProcessor.transformToDocument(xmlDoc);
		var resultXml = new XMLSerializer().serializeToString(resultDoc);
		return resultXml;
	};

	const escapeXml = function(unsafe) {
		return unsafe.replace(/[<>&'"]/g, function (c) {
			switch (c) {
				case '<': return '&lt;';
				case '>': return '&gt;';
				case '&': return '&amp;';
				case '\'': return '&apos;';
				case '"': return '&quot;';
			}
		});
	};

	return {
		prettifyXml: prettifyXml,
		escapeXml: escapeXml,
	};
})();
