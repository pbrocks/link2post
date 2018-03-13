function openReference(refSourceName) {
	var i;
	var x = document.getElementsByClassName("refSource");
	for (i = 0; i < x.length; i++) {
		x[i].style.display = "none";  
	}
	document.getElementById(refSourceName).style.display = "block";  
}