<script type="text/javascript">   
     $(window).load(function() {
      //This execute when entire finished loaded
      window.print();
    });
</script>
<style type="text/css">
body {
		font-family:Calibri,Arial,Tahoma;
		font-size:11pt;
		margin:0;
		padding:0;
	}
	
h1 { 
       background-color: #003366;
       color: white;
	   height:7mm;
	   width:16cm;
	   font-size:6mm;
	   margin:0;
	   padding:1;
	   textalign:left;
    }
	

img#all {
	width:5cm;
}

h2 { 
		height: 13pt;
		background-color: #003366;
		color: white;
		font-size:11pt;
		margin:0;
		padding:1;
		textalign:left;

    }

table {
	border: 1px solid #003366;
	border-right: 0px solid #003366;
	#border-collapse: collapse;
}

td {
	vertical-align: top;
	border-right:1px solid #003366;
	#border-left:1px solid #003366;
}

table#none {
	border-collapse: collapse;
	border: 0px solid #003366;
}

table#none tr, table#none td {
	border: 0px solid #003366;
}

table#gross {
		width:16cm;
}
table#klein {
		width:12cm;
}
 
	
th {
	height: 13pt;
	color: white;
	background-color: #003366;
	margin:0;
	padding:0;
}
td {
	padding-left:2px;
}

div#p {
	width:16cm;
	font-family: Arial;
	font-size: 8px;
	text-align: justify;
}



* {
    -webkit-print-color-adjust: exact !important;   /* Chrome, Safari */
    color-adjust: exact !important;                 /*Firefox*/
}

@media screen {
	div#page {
		width:80%;
		page-break-after:always !important;
	}	
}

@page {
	size:A4;
	margin: 0;
	#margin-top: 0cm;
}

@media print {
	header {
		display:none;
	}
	div#page {
		padding-top:1cm;
		#width:210mm;
		page-break-after:always !important;
		border: 0px red solid;
	}	
	button {
		display: none !important;
	}
	u {
		text-decoration:underline;
	}
	div#menu,h1#menu {
		 visibility: hidden;
		 display:none;
	}

	
}
</style>
