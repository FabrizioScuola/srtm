<?php

	include __DIR__ . '/srtm.php';
	
	$tmp_srtm = new srtm(); 

	echo $tmp_srtm->get_lat_long_elevation(0, 0);

?>