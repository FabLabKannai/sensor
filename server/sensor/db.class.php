<?php
/**
 * DB
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */ 
class DB extends DB_Base {

	const TIME_ORDER = "DESC";
	const TIME_OFFSET = 0;
	const TIME_LIMIT = 0;
	const TIME_NUM = 1000;

	const TABLE_ITEM = "sansor1_item";

	/**
	 * constructor
	 */	
	public function __construct() {
		$this->clear_error();
		$ret = $this->connect( self::SERVER, DB_USER, DB_PASS, DB_USER );
		if ( !$ret ) {
			die( $this->get_error() );
		}
	}

	/**
	 * create_table_item_if_not_exist
	 */	
	public function create_table_item_if_not_exist() {
		if ( !$this->exists_table( self::TABLE_ITEM ) ) {
			$ret = $this->create_table_item();
			if ( $ret ) {
				die( $this->get_error() );
			}
			echo "Created table ". self::TABLE_ITEM. "<br />\n";
		}
	}

	/**
	 * create_table_item
	 */	
	private function create_table_item() {
		$sql = "CREATE TABLE ". self::TABLE_ITEM;
    	$sql .= " ( ";	
    	$sql .= " id INT NOT NULL AUTO_INCREMENT, ";
    	$sql .= " device INT, ";
    	$sql .= " time INT, ";
    	$sql .= " temperature FLOAT, ";
    	$sql .= " humidity FLOAT, ";
    	$sql .= " light FLOAT, ";
    	$sql .= " noise FLOAT, ";
    	$sql .= " PRIMARY KEY (id) ";
    	$sql .= " ) ";
    	return $this->query( $sql ); 
	}

	/**
	 * read_id_table_item
	 */	
	public function read_id_table_item( $id ) {
		return $this->select_id( self::TABLE_ITEM, $id );
	}

	/**
	 * read_all_table_item
	 */		
	public function read_all_table_item( $where, $order, $limit, $offset ) {
		return $this->select_all( self::TABLE_ITEM, $where, $order, $limit, $offset );
	}

	/**
	 * read_all_table_item_time
	 */	
	public function read_all_table_item_time( $start, $end ) {	
		$where = $this->build_time_where( $start, $end );
		return $this->select_all( self::TABLE_ITEM, $where, self::TIME_ORDER, self::TIME_LIMIT, self::TIME_OFFSET );
	}

	/**
	 * build_time_where
	 */
	private function build_time_where( $start, $end ) {	
		$str  = "WHERE ( time > " . intval( $start );
		$str .= " AND time < " . intval( $end );
		$str .= " ) ";
		return $str;
	}
		
	/**
	 * reduce_rows
	 */	
	public function reduce_rows( $rows ) {		
		if ( !is_array($rows) || !count($rows) ) {
			return false;
		}
		$count = count($rows);
		if ( $count < 2 * self::TIME_NUM ) {
			return $rows;
		}
		$step = intval( $count / self::TIME_NUM );
		$new_rows = array();
		for ( $i=0; $i < $count; $i += $step ) {
			$new_rows[] = $rows[ $i ];
		}
		$new_rows[] = $rows[ $count - 1 ];
		return $new_rows;
	}

	/**
	 * insert_table_item
	 */
	public function insert_table_item( $device, $time, $temp, $humi, $light, $noise, $pressure ) {	
		return $this->insert( 
			self::TABLE_ITEM, 
			$this->make_params_table_item( $device, $time, $temp, $humi, $light, $noise, $pressure ) );
	}
	
	/**
	 * update_table_item
	 */
	public function update_table_item( $id, $device, $time, $temp, $humi, $light, $noise ) {			
		return $this->update( 
			self::TABLE_ITEM, $id, 
			$this->make_params_table_item( $device, $time, $temp, $humi, $light, $noise ) );
	}

	/**
	 * update_table_item_noise
	 */
	public function update_table_item_noise( $id, $noise ) {
		$params = array(
			"noise" => floatval( $noise )				
		);
		return $this->update( self::TABLE_ITEM, $id, $params );
	}

	/**
	 * delete_table_item
	 */
	public function delete_table_item( $id ) {			
		return $this->delete( self::TABLE_ITEM, $id );
	}

	/**
	 * delete_all_table_item
	 */
	public function delete_all_table_item() {			
		return $this->truncate_table( self::TABLE_ITEM );
	}

	/**
	 * make_params_table_item
	 */	
	public function make_params_table_item( $device, $time, $temp, $humi, $light, $noise, $pressure ) {
		$params = array(
			"device" => intval( $device ),
			"time" => intval( $time ),
			"temperature" => floatval( $temp ),
			"humidity" => floatval( $humi ),
			"light" => floatval( $light ),
			"noise" => floatval( $noise ),						
			"pressure" => floatval( $pressure ),		
		);
		return $params;	
	}
}

?>