<?php
class srtm {
	
	public function __construct($data_path='') {
		$this->data_path = $data_path;
	}

	public function get_lat_long_elevation($lat, $lon, $round=TRUE) {
		$ll = $this->get_lat_long_tab($lat,$lon);
		$lat_dir = $ll['lat_dir'];
		$lat_adj = $ll['lat_adj'];
		$lon_dir = $ll['lon_dir'];
		$lon_adj = $ll['lon_adj'];
		
		$filetmp =  $ll['lat_dir'] . sprintf("%02.0f", abs((integer)($lat+$ll['lat_adj']))) . $ll['lon_dir'].sprintf("%03.0f", abs((integer)($lon+$ll['lon_adj'])));

		$file = fopen($this->data_path . $filetmp . '.hgt', 'r');

		$y = $lat;
		$x = $lon;
		
		$offset = ( (integer)(($x - (integer)$x + $lon_adj) * 1200) * 2 + (1200 - (integer)(($y - (integer)$y + $lat_adj) * 1200)) * 2402 );		

		fseek($file, $offset);
		$h1 = $this->bytes2int(strrev(fread($file, 2)));
		$h2 = $this->bytes2int(strrev(fread($file, 2)));
		fseek($file, $offset - 2402);
		$h3 = $this->bytes2int(strrev(fread($file, 2)));
		$h4 = $this->bytes2int(strrev(fread($file, 2)));
		fclose($file);		
		
		$m = max($h1, $h2, $h3, $h4);
		for($i=1;$i<=4;$i++) {
			$c = 'h'.$i;
			if ($$c == -32768) $$c = $m;
		}
	
		$fx = ($lon - ((integer)($lon * 1200) / 1200)) * 1200;
		$fy = ($lat - ((integer)($lat * 1200) / 1200)) * 1200;
	
		$elevation = ($h1 * (1 - $fx) + $h2 * $fx) * (1 - $fy) + ($h3 * (1 - $fx) + $h4 * $fx) * $fy;
		if($round) $elevation = round($elevation);
		return $elevation;

	}

	
	private function bytes2int($val) {
		$t = unpack("s", $val);
		$ret = $t[1];
		return $ret;
	}		

	private function get_lat_long_tab($lat,$lon) {
		if($lat < 0){
			$r['lat_dir'] = 'S';
			$r['lat_adj'] = 1;
		}else{
			$r['lat_dir'] = 'N';
			$r['lat_adj'] = 0;
		}
		if ($lon < 0) {
			$r['lon_dir'] = 'W';
			$r['lon_adj'] = 1;
		}else{
			$r['lon_dir'] = 'E';
			if ($r['lat_dir'] == 'S') {
				$r['lon_adj'] = -0.75;;
			}else $r['lon_adj'] = 0;
		};
		return $r;
	}
}
?>
