/*
	
	----------------------------------
	----- HOW TO USE THIS SCRIPT -----
	----------------------------------
	
	To properly call AJAX in our system, use the following rules:
	
	1. Load AJAX with: loadAjax("scriptName", "ajaxDivID", "var1=a", "var2=b", ...)
	
		- "scriptName" is the page that you'll be loading, e.g. /directory/to/ajax/scripts/{scriptName}.js
		
		- "ajaxDivID" is the div ID on the page that will be updated.
		
		- A common example of this script might be done when clicking on an icon, such as:
			<a href="javscript:void(0)" onclick="loadAjax('checkData', 'ajaxDivID', 'menu=1')">
		
		- Each argument passed to loadAjax() after ajaxDivID will provide additional parameters that
		  get sent as $_POST values to that page.
		  
			^ For example, the first script shown would pass $_POST['var1'] = "a" and $_POST['var2'] = "b",
			which can be used to generate data on that page.
	
*/

// This function will load an AJAX view (into the ajax bubble)
function loadAjax(scriptName, ajaxDivID)
{
	// Prepare a query string
	var queryString = "";
	
	// Each additional argument sent to this function is set up like: value=something
	// So a full function call would look like this:
	// loadAjax('functionName', 'divToChange', 'username=Joe', 'guess=100', 'showEmail=true');
	for (var i = 2; i < arguments.length; i++)
	{
		queryString = queryString + "&" + arguments[i];
	}
	
	processAjax(scriptName, ajaxDivID, queryString);
}

// This is the true processor for AJAX - don't call this directly. Use loadAjax() or processForm().
function processAjax(scriptName, ajaxDivID, queryString)
{
	// Run AJAX
	if(window.XMLHttpRequest)
	{
		xmlhttp = new XMLHttpRequest();
	}
	else
	{
		// This a fix for Broken IE (5 & 6)
		// As of January 2013, only 0.3% of users use IE 6
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	xmlhttp.onreadystatechange = function()
	{
		if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
		{
			// Load the AJAX Response into the intended DIV
			document.getElementById(ajaxDivID).innerHTML = xmlhttp.responseText;
		}
	}
	
	// Run the Processor
	xmlhttp.open("POST", "../../../ajaxProcess.php", true);
	
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	
	xmlhttp.send("load=" + scriptName + queryString);
}

/*
	----------------------------------
	----- HOW TO USE THIS SCRIPT -----
	----------------------------------
	The script below is used to work with forms using AJAX, rather than refreshing the page.
	
	In order for this script to be called properly, the form must be set up like this:
	<form id="formID" name="someName" action="/admin/fileToLoad' onsubmit="return processForm('formID', 'fileToLoad', 'divToAlter')">
		<!-- Form Content Goes Here -->
	</form>
	
	What this script does is takes the information from the form and prepares it to be sent
	through AJAX. It will repress the standard form behavior (submitting to a new page and
	redirecting to it). It does this because we don't want to reload the entire page - we
	only want to reload the AJAX div on THIS page, but we still want the data to process.
	
	-----------------------
	----- LINE BREAKS -----
	-----------------------
	One of the quirks about this script is that line breaks are not sent, so we've created
	our own special character for it: %BRK
	
	When the AJAX is loaded on the receiving page, the constructor should recognize the %BRK
	character and replace it with an actual line break for the respective purpose (i.e. HTML
	would use <br / >, but URLs or the Database would be different).
	
*/

// Process Form
function processForm(formID, scriptName, ajaxDivID)
{
	/* Do what you want with the form */
	var form = document.getElementById(formID);
	var elements = form.elements;
	var queryString = "";
	
	for(var i = 0; i < elements.length; i++)
	{
		if(typeof elements[i] != 'undefined' && typeof elements[i].name != 'undefined' && typeof elements[i].value != 'undefined')
		{
			// Prepare Element Name
			var elemName = elements[i].name;
			
			// Special Checks for Checkboxes
			if(elements[i].type == "checkbox")
			{
				if(elements[i].checked == true)
				{
					queryString = queryString + "&" + elemName + "=on";
				}
			}
			else
			{
				var elemValue = elements[i].value; // works
			
				// Replace newlines
				elemValue = elemValue.replace(/\n\r?/g, '%BRK');

				queryString = queryString + "&" + elemName + "=" + elemValue;
			}
		}
	}
	
	// Send the Form Data through the ajax processing script:
	processAjax(scriptName, ajaxDivID, queryString);
	
	// You must return false to prevent the default form behavior
	return false;
}
