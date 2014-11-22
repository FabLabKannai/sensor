<?php
/**
 * DB_Base
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */
class DB_Base {

	const DEBUG = false;
	const SERVER = "localhost";
	
	protected $conn = null;
	protected 	$sql = "";
	protected $errors = null;

	/**
	 * constructor
	 */	
	protected function __construct() {
		clear_error();
	}

	/**
	 * exists_table
	 */				
	protected function exists_table( $name ) {
		$tables = $this->show_tables();
		if ( !$tables ) { return false; }	
		foreach ( $tables as $table ) {
			if ( $table == $name ) { return true; }
		}
		return false;
	}

	/**
	 * show_tables
	 */	
	protected function show_tables() {
		$sql = "SHOW TABLES";
		$result = $this->query( $sql );
		if ( !$result ) { return null; }
		$rows = array();
		while ( $row = mysql_fetch_row($result) ) {
    		$rows[] = $row[0];
		}
		return $rows;
	}

	/**
	 * truncate_table
	 */	
	protected function truncate_table( $table ) {
		$sql = "TRUNCATE ". $table;
		return $this->query( $sql );
	}

	/**
	 * select_id
	 */	
	protected function select_id( $table, $id ) {
		$sql = "SELECT * FROM ". $table ." WHERE id=". intval( $id );
		$result = $this->query( $sql );
		if ( !$result ) { return null; }
		$row = mysql_fetch_assoc( $result );
		return $row;
	}

	/**
	 * select_all
	 */	
	protected function select_all( $table, $where, $order, $limit, $offset ) {
		$sql = "SELECT * FROM ". $table ." ". $where ." ORDER BY id ". $order ;
		if ( $limit > 0 ) {
			$sql .= " LIMIT ". $limit ;
			if ( $offset > 0 ) {
				$sql .= " OFFSET ". $offset;
			}	
		}
		$result = $this->query( $sql );
		if ( !$result ) { return null; }
		$rows = array();
		while ( $row = mysql_fetch_assoc($result) ) {
    		$rows[] = $row;
		}
		return $rows;
	}

	/**
	 * insert
	 */	
	protected function insert( $table, $params ) {
		$keys = array();
		$values = array();
		foreach( $params as $k => $v ) {
			$keys[] = $k;
			$values[] = $this->escape( $v );
		}
		$str_key = implode( ", ", $keys );
		$str_value = implode( ", ", $values );
		$sql = "INSERT INTO ". $table ." (". $str_key .") VALUES (". $str_value .")";
		return $this->query( $sql );
	}

	/**
	 * update
	 */	
	protected function update( $table, $id, $params ) {
		$sets = array();
		foreach( $params as $k => $v ) {
			$sets[] = $k."=". $this->escape( $v );
		}
		$str_set = implode( ", ", $sets );
		$sql = "UPDATE ". $table ." SET ". $str_set ." WHERE id=". intval( $id );
		return $this->query( $sql );
	}

	/**
	 * delete
	 */	
	protected function delete( $table, $id ) {
		$sql = "DELETE FROM ". $table ." WHERE id=". intval( $id );
		return $this->query( $sql );
	}

	/**
	 * escape
	 */	
	protected function escape( $v ) {
		$str = "'". mysql_escape_string( $v ). "'";
		return $str;
	}

	/**
	 * query
	 */	
	protected function query( $sql ) {
	    $this->set_sql( $sql );
    	$this->clear_error();
    	$ret = mysql_query( $sql );
    	if ( !$ret ) {
    		$this->add_error( $sql );
    		$this->add_error( mysql_error() );
    		if ( self::DEBUG ) {
    			echo $this->get_error( "<br/>\n" );
    		}	 
    		return false;
    	}
    	return $ret;
    }

	/**
	 * connect
	 */	    	
	protected function connect( $server, $user, $pass, $db ) {
		$this->clear_error();
		$this->conn = mysql_connect( $server, $user, $pass );
		if ( !$this->conn ) { 
			$this->add_error( "mysql connect failed" );
			$this->add_error( mysql_error() );
			return false;
		}
		mysql_set_charset( "utf8", $this->conn );
		$ret = mysql_select_db( $db, $this->conn );
		if ( !$ret ){
			$this->add_error( "mysql db failed ". $db );
			$this->add_error( mysql_error() );
			return false;
		}
		return true;
	}

	/**
	 * close
	 */		
	protected function close() {
		if ( $this->conn ){
			mysql_close( $this->conn );
		}
		$this->conn = null;
	}

	/**
	 * set_sql
	 */	
	public function print_sql() {    
        echo $this->sql ."<br/>\n";
	}

	/**
	 * set_sql
	 */	
	protected function set_sql( $sql ) {    
        $this->sql = $sql;
	}

	/**
	 * get_sql
	 */	
	protected function get_sql() {    
        return $this->sql;
	}
	
	/**
	 * clear_error
	 */	
	protected function clear_error() {
    	$this->errors = array();
    }		

	/**
	 * add_error
	 */	
	protected function add_error( $error ) {    
        $this->errors[] = $error;
	}

	/**
	 * get_error
	 */	
	public function get_error( $glue="\n" ) {
		if ( !is_array($this->errors) || !count($this->errors) ) return false;
		return implode( $glue, $this->errors );
	}    
		        		
}

?>