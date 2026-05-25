<?php

include('includes/mPdf/mpdf.php');
require_once('../system/config-user.php');

$name 	= $_POST['product-name'];
$email 	= $_POST['product-date'];
$msg 	= $_POST['product-downloaded'];
$price 	= $_POST['product-price'];
$sname = $setting['site_name'];

$html .= "
<html>
<head>
<style>
body {font-family: sans-serif;
    font-size: 10pt;
}
td { vertical-align: top; 
    border-left: 0.6mm solid #000000;
    border-right: 0.6mm solid #000000;
	align: center;
}
table thead td { background-color: #EEEEEE;
    text-align: center;
    border: 0.6mm solid #000000;
}
td.lastrow {
    background-color: #FFFFFF;
    border: 0mm none #000000;
    border-bottom: 0.6mm solid #000000;
    border-left: 0.6mm solid #000000;
	border-right: 0.6mm solid #000000;
}

</style>
</head>
<body>

<!--mpdf
<htmlpagefooter name='myfooter'>
<div style='border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; '>
Page {PAGENO} of {nb}
</div>
</htmlpagefooter>

<sethtmlpageheader name='myheader' value='on' show-this-page='1' />
<sethtmlpagefooter name='myfooter' value='on' />
mpdf-->
<div style='text-align:center;font-size:40px;'>$sname</div>
<div style='text-align:center;font-size:20px;'>Thank you for your purchase!</div><br>
<table class='items' width='100%' style='font-size: 9pt; border-collapse: collapse;' cellpadding='8'>
<thead>
<tr>
<td width='15%'>Product Name</td>
<td width='15%'>Purchase Date</td>
<td width='15%'>Have you Downloaded?</td>
<td width='15%'>Purchase Price</td>
</tr>
</thead>
<tbody>
<tr><td class='lastrow'>$name</td><td class='lastrow'>$email</td><td class='lastrow'>$msg</td><td class='lastrow'>$price.00</td></tr>
</tbody>
</table>
</body>
</html>
";



$mpdf=new mPDF('utf-8', "A4");
$mpdf->WriteHTML($html);
$mpdf->SetDisplayMode('fullpage');

$mpdf->Output();

?>