<html lang="de">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body,html {
	width:100%;
	margin:0;
	padding:0;
}
th {
	text-align:left;
	vertical-align:top;
}
td {
	text-align:left;
	vertical-align:top;
	margin:0;padding:0;
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

div#right {
	display:inline-block;
	float:right;
	margin-right:10%;
	width:150px;
	text-align: right;
}
div#left {
	display:inline-block;
	float:left;
	margin-left:10%;
	width:150px;
}
div#center {
	display:inline-block;
	float:both;
	width:250px;
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
}
	
</style>
</head>
<body>