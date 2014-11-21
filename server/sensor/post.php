<?php
/**
 * Post
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */
class Post {

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
		$this->db = new DB();

		$method = isset( $_SERVER["REQUEST_METHOD"] ) ? $_SERVER["REQUEST_METHOD"] : "";
		$param = isset( $_POST["param"] ) ? $_POST["param"] : "";

		header( "Content-Type: text/plain" );

		if ( $method != "POST" ) {
			echo $this->get_failure( "method is ".$method );
		} else if ( empty($param) ) {
			echo $this->get_failure( "No Param" );
		} else {
			$device = 1;
			$time = time();
			$p = json_decode( $param, true );
			$temp = isset( $p["temperature"] ) ? $p["temperature"] : 0;
			$humi = isset( $p["humidity"] ) ? $p["humidity"] : 0;
			$light = isset( $p["light"] ) ? $p["light"] : 0;
			$noise = isset( $p["noise"] ) ? $p["noise"] : 0;
			$pressure = isset( $p["pressure"] ) ? $p["pressure"] : 0;
			$ret = $this->db->insert_table_item( $device, $time, $temp, $humi, $light, $noise, $pressure );
			if ( $ret ) {
				echo $this->get_success();
			} else {
				echo $this->get_failure( $this->db->get_error() );
			}
		}
	}

	/**
	 * get_success
	 */
	private function get_success() {
		$str = "{ code: 1 }";
		return $str;
	}

	/**
	 * get_failure
	 */
	private function get_failure( $reason ) {
		$str = "{ code: 0, reason: $reason }";
		return $str;
	}
		
}

	// main
	require_once "db_base.class.php";
	require_once "db.class.php";
	include "config.php";	

	$post = new Post();
	$post->main();

?>