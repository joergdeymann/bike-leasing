<style>
body,html {
	width:100%;
	margin:0;
	padding:0;
}
table {
	border: black solid 1px;
}	
th {
	text-align:left;
	vertical-align:top;
	border: black solid 1px;
}
td {
	text-align:left;
	vertical-align:top;
	margin:0;padding:0;
	border: black solid 1px;
}	

	
table#liste th {
	background-color: #666666;
}
table#liste tr:nth-of-type(odd) td {
	background-color: #DDDDDD;
}
table#liste tr td {
	height:2em;
}

td#rahmen b,#headline {
	display: block;
	background-color: darkblue;
	color: white;
	font-style:italic;
	font-size: 1em;
	margin: 0px;
	padding: 1px;
}

h2 {
	margin:0;
	padding:1px;
}

h3 {
	font-size: 2em;
	margin-bottom: 5px;
	margin-top: 20px;
}
h3 div#right {
	display:inline-block;
	float:right;
	margin-right:10%;
	width:150px;
	text-align: right;
}
h3 div#left {
	display:inline-block;
	float:left;
	margin-left:10%;
	width:150px;
	border: 0px solid transparent;
	background-color: transparent;
}
h3 div#center {
	display:inline-block;
	float:both;
	width:250px;
}
div#p {
	width:80%;
	/* width:16cm;*/
	font-family: Arial;
	font-size: 8px;
	text-align: justify;
}

@page { 
	margin: 0cm !important;
	/* size: landscape; */
	size: A4 portrait;
}
@media print {
	  
	br#pagebreak {
	   page-break-after: always;
	}
	html, body {
		width: 210mm !important;
		height: 297mm !important;
		color-adjust:exact;
		display: inline-block; 		
	}
	div#menu,h1#menu {
		 visibility: hidden;
		 display:none;
	}
}
	
</style>
