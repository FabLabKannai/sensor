<?php
/**
 * Data
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */
class Data {

	private $db = null;

	/**
	 * constructor
	 */		
    public function __construct() {
    	// dummy
    }

	/**
	 * main
	 */
	public function main() {	
		echo $this->get_head();
		$this->db = new DB();
		$lines = file( "data.txt" );
		foreach( $lines as $line ) {
			$this->insert( trim($line) );
		}
		
		echo $this->get_tail( count($lines) );
	}

	/**
	 * insert
	 */
	private function insert( $param_in ) {
 
		$param = str_replace( "param=", "", $param_in  );
 
		$START = 1413280440;
	
		$DEVICE = 1;
		$p = json_decode( $param, true );
		$p_time = isset( $p["time"] ) ? $p["time"] : 0;
		$time = $START + intval( $p_time / 1000 );
		$temp = isset( $p["temperature"] ) ? $p["temperature"] : 0;
		$humi = isset( $p["humidity"] ) ? $p["humidity"] : 0;
		$light = isset( $p["light"] ) ? $p["light"] : 0;
		$noise = isset( $p["noise"] ) ? $p["noise"] : 0;
		$pressure = isset( $p["pressure"] ) ? $p["pressure"] : 0;

		echo date( "Y-m-d H:i:s", $time  ) ." ";
		echo $time ." ". $temp ." ". $humi ." ". $light ." ". $noise ." ". $pressure  ."<br/>\n "; 

		$ret = $this->db->insert_table_item( $DEVICE , $time, $temp, $humi, $light, $noise, $pressure );
		if ( !$ret ) {
			echo $this->db->get_error( "<br/>\n" ) ;
		}
	}

	/**
	 * get_head
	 */	
	private function get_head() {
		$title = TITLE;
		$str = <<<EOT
<html>
<head>
<meta http-equiv="Content-Type" content="UTF-8" />
<title>$title</title>
</head>
<body>
<h3>Data</h3>
<a href="index.php">[Home]</a><br />
<br />
EOT;
		return $str;
	}

	/**
	 * get_tail
	 */	
	private function get_tail( $num ) {
		$str = <<<EOT
Updated $num <br/>		
</body>	
</html>
EOT;
		return $str;
	}
		
}

	// main
	require_once "db_base.class.php";
	require_once "db.class.php";
	include "config.php";	

	$data = new Data();
	$data->main();

?>