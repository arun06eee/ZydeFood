<?php

include_once('config.php');

if($con) {

	$sql = "SELECT * FROM zyde_locations";
	$result = mysqli_query($con, $sql);

	if(!$result) {
		echo "<br/>couldn't get data";
	}
	while ($row = mysqli_fetch_assoc($result)) {
		$arr[] = $row;
	}
	$data = json_encode($arr);
	print_r($data);
}else{
		echo "could not connect";
	}
?>