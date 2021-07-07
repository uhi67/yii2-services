const WsdlDoc = (function() {
	'use strict';

	const documentLoaded = function (event) {
		const button = document.querySelector('button#submit');
		button.addEventListener('click', ()=>{
			const form = button.parentElement;
			const xmlhttp = new XMLHttpRequest();
			const xml = document.querySelector('pre#xml-data').textContent;
			xmlhttp.onreadystatechange = function() {
				if (xmlhttp.readyState == 4) {
					if (xmlhttp.status == 200) {
						//Request was successful
						console.log('Success');
						console.log(xmlhttp.responseXML);
						document.querySelector('div#response').className = 'success';
					} else {
						console.log('Failure');
						console.log('state', xmlhttp.readyState, 'status', xmlhttp.status);
						console.log(xmlhttp.response);
						document.querySelector('div#response').className = 'error';
					}
					const responseContainer = document.querySelector('div#response-container');
					responseContainer.className='';
					document.querySelector('div#response').textContent = xmlhttp.responseText;
				}
			}
			xmlhttp.open("POST", form.action);
			xmlhttp.setRequestHeader('Content-Type', 'text/xml');
			xmlhttp.send(xml);
		});
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', documentLoaded);
	} else {
		documentLoaded();
	}

	return {
	};
})();
