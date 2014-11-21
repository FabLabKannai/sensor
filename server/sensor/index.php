<?php
/**
 * Server for Arduino Sensor
 * Temperature Humidity Light Noise 
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */

/**
 * MainPorc
 */
class MainPorc {

	const WIDTH = 640;
	const HEIGHT = 240;

	const REFRESH = 60;
	const DAY = 86400;

	const Y_MIN = 0;
	const PRESS_MIN = 950;

	const SQL_WHERE = "";	
	const SQL_ORDER = "DESC";
	const SQL_OFFSET = 0;
	const SQL_LIMIT = 1000;

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
		$renge = isset( $_GET["r"] ) ? $_GET["r"] : "";
		$start_in = isset( $_GET["s"] ) ? $_GET["s"] : "";
		$end_in = isset( $_GET["e"] ) ? $_GET["e"] : "";
				
		echo $this->get_head( $renge );
		$this->db = new DB();
		$this->db->create_table_item_if_not_exist();
		$arr = $this->get_renge_time( $renge, $start_in, $end_in );
		if ( is_array($arr) ) {
			$start = $arr["start"];
			$end  = $arr["end"];
			$rows = $arr["rows"];			
			echo $this->get_date_time( $rows ); 
			echo $this->get_graph( 1, $start, $end, self::Y_MIN );
			echo $this->get_graph( 2, $start, $end, self::Y_MIN );
			echo $this->get_graph( 5, $start, $end, self::PRESS_MIN );
			echo $this->get_graph( 3, $start, $end, self::Y_MIN );
			echo $this->get_graph( 4, $start, $end, self::Y_MIN );	
		} else {
			echo "No Data <br />\n";
		}
		echo $this->get_tail();
	}

	/**
	 * get_head
	 */		
	private function get_head( $renge ) {
		$title = TITLE;
		$refresh = self::REFRESH;
		$url = "index.php";
		if ( $renge ) {
			$url .= "?r=". $renge;
		}	
		$str = <<<EOT
<html>
<head>
<meta http-equiv="Content-Type" content="UTF-8" />
<meta http-equiv="refresh" content="$refresh; URL=$url" />.
<title>$title</title>
</head>
<body>
<h3>$title</h3>
<a href="index.php">[Home]</a> <a href="manage.php">[Manage]</a> <br /><br />
<a href="index.php?r=day">[Day]</a> <a href="index.php?r=week">[Week]</a> <a href="index.php?r=month">[Month]</a><a href="index.php?r=year">[Year]</a><br /><br />
EOT;
		return $str;
	}

	/**
	 * get_tail
	 */	
	private function get_tail() {
		$str = <<<EOT
<br />		
<a href="http://asial.co.jp/jpgraph/" target="_blank">produced by JpGraph</a><br />
</body>	
</html>
EOT;
		return $str;
	}

	/**
	 * get_renge_time
	 */
	private function get_renge_time( $renge, $start_in, $end_in ) {
		list( $flag1, $start1, $end1 ) = $this->calc_period_time( $renge, $start_in, $end_in );
		$rows1 = $this->db->read_all_table_item_time( $start1, $end1 );
		if ( is_array($rows1) && ( $flag1 || ( count($rows1) > self::SQL_LIMIT ))) {
			$ret = array(
				"start" => $start1,
				"end" => $end1,
				"rows" => $rows1
			);
			return $ret;
		}
		$rows2 = $this->db->read_all_table_item( self::SQL_WHERE, self::SQL_ORDER, self::SQL_LIMIT, self::SQL_OFFSET );
		if ( is_array($rows2) && count($rows2) ) {
			$start2 = $rows2[ count($rows2) -1 ]["time"];
			$end2 = $rows2[ 0 ]["time"];
			$ret = array(
				"start" => $start2,
				"end" => $end2,
				"rows" => $rows2
			);
			return $ret;
		}
		return false;
	}

	/**
	 * calc_period_time
	 */	
	private function calc_period_time( $renge, $start_in, $end_in ) {
		$time = time();
		if ( $renge == "period" ) {
			$start = $this->calc_time( $start_in,  ($time - self::DAY) );
			$end  = $this->calc_time( $end_in, $time );
			return array( true, $start, $end );
		}
		switch ( $renge ) {
			case "week":
				$sec = 7 * self::DAY;
				break;
			case "month":
				$sec = 30 * self::DAY;
				break;				
			case "year":
				$sec = 365 * self::DAY;
				break;		
			case "day":
			default:	
				$sec = self::DAY;
				break;	
		}
		$end  = $time;
		$start = $end - $sec;
		return array( false, $start, $end );
	}

	/**
	 * calc_time
	 */
	private function calc_time( $time_in, $default ) {	
		if ( empty($time_in) ) {
			return $default;
		}
		$time = strtotime( $time_in );
		if ( $time == FALSE ) {
			return $default;
		}
		return $time;
	}
			
	/**
	 * get_date_time
	 */	
	private function get_date_time( $rows1 ) {			
		$count1 = count( $rows1 );
		$rows2 = $this->db->reduce_rows( $rows1 );
		$count2 = count( $rows2 );		
		$first = $rows2[0]["time"];
		$last = $rows2[ $count2 -1 ]["time"];
		$count = $count2;
		if ( $count2 < $count1 ) {
			$count = $count2 ." / ". $count1;
		}
		$str = "DateTime ";
		$str .= date( "Y-m-d H:i", $last );
		$str .= " - ";
		$str .= date( "Y-m-d H:i", $first );	
		$str .= " ( ";
		$str .= $count;
		$str .= " )<br />\n";
		return $str;
	}
		
	/**
	 * get_graph
	 */	
	private function get_graph( $kind, $start, $end, $min ) {
		$width = self::WIDTH;
		$height = self::HEIGHT;
		$src = "graph.php?kind=$kind&amp;start=$start&amp;end=$end&amp;min=$min";
		$str = <<<EOT
<img src="$src" width="$width" height="$height" />
<br />
EOT;
		return $str;
	}
	
}

	// main
	require_once "db_base.class.php";
	require_once "db.class.php";
	include "config.php";	
	
	$main = new MainPorc();
	$main->main();

?>