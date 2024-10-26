<?php
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_rad.php";
include "class/class_abholung.php";

$rad=new Rad($db);
$abh=new Abholung($db);

$request="select rebikeid, recnum from rad_rad order by rebikeid,recnum";
$rad->query($request);

$bike=array();
$count=0;
$rebikeid="XX";

while($row=$rad->next()) {
	if ($row['rebikeid'] != $rebikeid) {
		$count=0;
		$rebikeid=$row['rebikeid'];
	} else {
		$count++;
	}
	// if ($row[
	echo "count=$count, Bikeid=".$row['rebikeid'].",".$row['recnum']."<br>";
	$bike[$row['rebikeid']][$count]=$row['recnum'];
}

foreach($bike as $bikeid => $a) {
	foreach($a as $k => $v) {
		if ($k == 0) {
			$new_recnum=$v;
		} else {
			$request="update rad_abholung set radnr='".$new_recnum."' where radnr='".$v."'";
			$request2 = "delete from rad_rad where recnum=$v";
			echo $request."<br>".$request2."<br><br>";
			
			$abh->query($request);
			$rad->query($request2);

		}			
	}
}

echo "<pre>";
var_dump($bike);
echo "</pre>";
?>
