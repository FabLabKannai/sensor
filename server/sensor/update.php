<?php
/**
 * Update
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */
class Update {

	const SQL_WHERE = "";	
	const SQL_ORDER = "ASC";
	const SQL_OFFSET = 0;
	const SQL_LIMIT = 0;
		
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
		$num = $this->update();
		echo $this->get_tail( $num );
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
<h3>Update</h3>
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

	/**
	 * update
	 */
	private function update() {
		$num = 0;
		$rows = $this->db->read_all_table_item( self::SQL_WHERE, self::SQL_ORDER, self::SQL_LIMIT, self::SQL_OFFSET );
		if ( !is_array($rows) || !count($rows) ) {	
			echo $this->db->get_error( "<br />\n" );
			return 0;
		}
		$num = 0;	
		foreach( $rows as $row ) {
			$id = $row["id"];
			$noise_old = $row["noise"];
			$noise_new = abs( $noise_old - 512 );		
			if ( $noise_old > 150 ) {					
				$this->db->update_table_item_noise( $id, $noise_new );
				$num ++;
			}
		}
		return $num;
	}
}

	// main
	require_once "db_base.class.php";
	require_once "db.class.php";
	include "config.php";	

	$update = new Update();
	$update->main();

?>