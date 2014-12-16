<?php
/**
 * MainGraph
 * require JpGraph http://asial.co.jp/jpgraph/
 *
 * FabLab Kannai Sensor Project
 * 2014-08-20
 */
class MainGraph {

	const WIDTH = 640;
	const HEIGHT = 240;
		
	private $NAMES = array( "", "temperature", "humidity", "light", "noise", "pressure"  );

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

		$kind = isset( $_GET["kind"] ) ? intval( $_GET["kind"] ) : 0;
		$start = isset( $_GET["start"] ) ? intval( $_GET["start"] ) : 0;
		$end  = isset( $_GET["end"] ) ? intval( $_GET["end"] ) : 0;
		$min = isset( $_GET["min"] ) ? intval( $_GET["min"] ) : 0;
				
		if ( $kind < 1 ||  $kind > count( $this->NAMES ) ) {
			return;
		}

		$rows1 =$this->db->read_all_table_item_time( $start, $end );
		if ( !is_array($rows1) || !count($rows1) ) {
			echo "No Rows $start - $end <br/>\n";
			return;
		}

		$rows2 = $this->db->reduce_rows( $rows1 );			
		$name = $this->NAMES[ $kind ];

		// remove all zero data
		$n = 0;
		$xd = array();					
		$yd = array();
		foreach( $rows2 as $row ) {
			$temp = $row["temperature"];	
			$humi = $row["humidity"];
			$light = $row["light"];
			$noise = $row["noise"];				
			if (( $temp != 0 )&&( $humi > 0 )&&( $light > 0 )&&( $noise > 0 )) {		
				$xd[ $n ] = $row["time"];	
				$y = $row[ $name ];
				if ( $y < $min ) {
					$y = $min;
				}
				$yd[ $n ] = $y;						
				$n++;
			}		
		}

		// set x y data		
		$xdata = array();					
		$ydata = array();
		for( $i = 0; $i < $n; $i++ ) {
			$j = $n - $i - 1;
			$xdata[ $j ] = $xd[ $i ];	
			$ydata[ $j ] = $yd[ $i ];							
		}

		// The code to setup a very basic graph
		$graph = new Graph( self::WIDTH, self::HEIGHT );
		$graph->SetScale( 'datlin', $min,  max( $ydata ) );
		$graph->SetMarginColor( 'white' );
		$graph->SetFrame( true, 'white', 1 );

		// tile
		$graph->title->Set( $name );
		$graph->title->SetFont( FF_ARIAL,FS_BOLD, 12 );

		// Use Ariel font
		$graph->xaxis->SetFont( FF_ARIAL, FS_NORMAL, 9 );
		$graph->yaxis->SetFont( FF_ARIAL, FS_NORMAL, 9 );
		$graph->xgrid->Show();

		// x axis
		$graph->xaxis->SetLabelAngle( 90 );
		$graph->xaxis->scale->SetDateFormat( 'H:i' );

		// y axis
		$graph->yscale->SetAutoTicks();

		// Create the plot line
		$p1 = new LinePlot( $ydata, $xdata );
		$p1->SetColor( "orange" ); 
		$graph->Add( $p1 );
						
		// Output graph
		$graph->Stroke();
	}
		
}

	// main
	$PATH = "/usr/share/php/jpgraph-3.0.8/src/";
	require_once $PATH. "jpgraph.php";
	require_once $PATH. "jpgraph_line.php";
	require_once $PATH. "jpgraph_date.php";
	require_once "db_base.class.php";
	require_once "db.class.php";
	include "config.php";	
	
	$graph = new MainGraph();
	$graph->main();

?>