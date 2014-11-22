<?php
/**
 * Manage
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */
class Manage {

	const SQL_WHERE = "";	
	const SQL_ORDER = "DESC";
	const SQL_OFFSET = 0;
	const SQL_LIMIT = 50;

	private $SELF = "";
		
	private $db = null;

	/**
	 * constructor
	 */		
    public function __construct() {
		$this->SELF = $_SERVER["PHP_SELF"];
	}

	/**
	 * main
	 */
	public function main() {
		echo $this->get_head();

		$this->db = new DB();
			
		$action = isset( $_GET["action"] ) ? $_GET["action"] : "";
		if ( !$action ) {
			$action = isset( $_POST["action"] ) ? $_POST["action"] : "";
		}
	
		$flag_list = false;
		if ( $action == "add_form" ) {
			echo $this->get_add_form();
		} else if ( $action == "add" ) {
			$this->add();
			$flag_list = true;
		} else if ( $action == "edit_form" ) {
			echo $this->get_edit_form();
		} else if ( $action == "edit" ) {
			$this->edit();
			$flag_list = true;
		} else if ( $action == "delete" ) {
			$this->delete();
			$flag_list = true;
		} else if ( $action == "delete_all" ) {
			$this->delete_all();
			$flag_list = true;
		} else {
			$flag_list = true;
		}

		if ( $flag_list ) {
			echo $this->get_add_link();
			echo $this->get_delete_all_form();
			echo $this->get_list();
		}
		
		echo $this->get_tail();
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
<h3>Manage</h3>
<a href="index.php">[Home]</a> <a href="{$this->SELF}">[Manage]</a> <br />
<br />
EOT;
		return $str;
	}

	/**
	 * get_tail
	 */	
	private function get_tail() {
		$str = <<<EOT
</body>	
</html>
EOT;
		return $str;
	}

	/**
	 * get_add_link
	 */	
	private function get_add_link() {
		$str = <<<EOT
<a href="?action=add_form">[Add Record]</a><br />
EOT;
		return $str;
	}

	/**
	 * get_br
	 */	
	private function get_br() {
		return "<br />\n";
	}

	/**
	 * get_add_form
	 */		
	private function get_add_form() {
		$params = $this->db->make_params_table_item( 0, 0, 0, 0, 0, 0, 0 );
		return $this->get_form( "add", "Add", 0, $params );
	}

	/**
	 * get_edit_form
	 */
	private function get_edit_form() {
		$id = isset( $_GET["id"] ) ? intval( $_GET["id"] ) : 0;
		if ( $id <= 0 ) {
			return "invalid id <br/>\n";
		}
		$row = $this->db->read_id_table_item( $id );
		$params = $this->db->make_params_table_item( 
			$row["device"], $row["time"], $row["temperature"], $row["humidity"], $row["light"], $row["noise"], $row["pressure"] );
		$str = $this->get_form( "edit", "Edit", $id, $params );
		$str .= $this->get_delete_form( $id );
		return $str;
	}

	/**
	 * get_form
	 */
	private function get_form( $action, $submit, $id, $params ) {

		$str = <<<EOT1
<form action="{$this->SELF}" method="post">
<input type="hidden" name="action" value="$action" />
<input type="hidden" name="id" value="$id" /> 
<table>
EOT1;

		foreach( $params as $k => $v ) {
			$str .= "<tr><td>";
			$str .= $k;
			$str .= "</td><td>";
			$str .= "<input type=\"text\" name=\"". $k ."\" value=\"". $v ."\" >";
			$str .= "</td><tr>\n"; 
		}

		$str .= <<<EOT2
<tr><td></td><td>
<input type="submit" value="$submit" />
</td><tr>
</table> 
</form>
EOT2;

		return $str;
	}

	/**
	 * get_delete_form
	 */
	private function get_delete_form( $id ) {
		$str = <<<EOT
<form action="{$this->SELF}" method="post">
<input type="hidden" name="action" value="delete" />
<input type="hidden" name="id" value="$id" />   
<input type="submit" value="Delete" /> 
</form>
EOT;
		return $str;
	}

	/**
	 * get_delete_all_form
	 */
	private function get_delete_all_form() {
		$str = <<<EOT
<form action="{$this->SELF}" method="post">
<input type="hidden" name="action" value="delete_all" />  
<input type="submit" value="Delete All" /> 
</form>
EOT;
		return $str;
	}

	/**
	 * add
	 */
	private function add() {
		$device = isset( $_POST["device"] ) ? $_POST["device"] : "";
		$time = isset( $_POST["time"] ) ? $_POST["time"] : "";
		$temp = isset( $_POST["temperature"] ) ? $_POST["temperature"] : "";
		$humi = isset( $_POST["humidity"] ) ? $_POST["humidity"] : "";
		$light = isset( $_POST["light"] ) ? $_POST["light"] : "";
		$noise = isset( $_POST["noise"] ) ? $_POST["noise"] : "";	
		$pressure= isset( $_POST["pressure"] ) ? $_POST["pressure"] : "";		
		$this->db->insert_table_item( $device, $time, $temp, $humi, $light, $noise, $pressure );
	}

	/**
	 * edit
	 */
	private function edit() {
		$id = isset( $_POST["id"] ) ? $_POST["id"] : 0;
		$device = isset( $_POST["device"] ) ? $_POST["device"] : "";
		$time = isset( $_POST["time"] ) ? $_POST["time"] : "";
		$temp = isset( $_POST["temperature"] ) ? $_POST["temperature"] : "";
		$humi = isset( $_POST["humidity"] ) ? $_POST["humidity"] : "";
		$light = isset( $_POST["light"] ) ? $_POST["light"] : "";
		$noise = isset( $_POST["noise"] ) ? $_POST["noise"] : "";	
		$pressure = isset( $_POST["pressure"] ) ? $_POST["pressure"] : "";	
		$this->db->update_table_item( $id, $device, $time, $temp, $humi, $light, $noise, $pressure );
	}

	/**
	 * delete
	 */
	private function delete() {
		$id = isset( $_POST["id"] ) ? $_POST["id"] : 0;
		$this->db->delete_table_item( $id );
	}

	/**
	 * delete_all
	 */
	private function delete_all() {
		$this->db->delete_all_table_item();
	}

	/**
	 * get_list
	 */
	private function get_list() {
		$rows = $this->db->read_all_table_item( self::SQL_WHERE, self::SQL_ORDER, self::SQL_LIMIT, self::SQL_OFFSET );
		if ( !$rows ) {
			echo $this->db->get_error( "<br />\n" );
			return;
		}
		$str = "<table>\n";
		$str .= "<tr>";
		$str .= "<th></th>";
		$str .= "<th>ID</th>";
		$str .= "<th>Device</th>";
		$str .= "<th>Time</th>";
		$str .= "<th>Temperature</th>";
		$str .= "<th>Humidity</th>";
		$str .= "<th>Light</th>";
		$str .= "<th>Noise</th>";				
		$str .= "<th>Pressure</th>";
		$str .= "</tr>\n";
		if ( is_array($rows) && count($rows) ) {		
			foreach( $rows as $row ) {
				$href = $this->SELF ."?action=edit_form&id=". $row["id"];
				$str .= "<tr>";
				$str .= "<td><a href=\"". $href ."\">[Edit]</a></td>";
				$str .= "<td>". $row["id"] ."</td>";
				$str .= "<td>". $row["device"] ."</td>";
				$str .= "<td>". date( "Y-m-d H:i:s", $row["time"]  ) ."</td>";
				$str .= "<td align=\"center\">". $row["temperature"] ."</td>";
				$str .= "<td>". $row["humidity"] ."</td>";
				$str .= "<td>". $row["light"] ."</td>";
				$str .= "<td>". $row["noise"] ."</td>";								
				$str .= "<td>". $row["pressure"] ."</td>";		
				$str .= "</tr>\n";		
			}
		}	
		$str .= "</table>\n";
		return $str;
	}
}

	// main
	require_once "db_base.class.php";
	require_once "db.class.php";
	include "config.php";	

	$manage = new Manage();
	$manage->main();

?>