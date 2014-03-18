<?php
/*
  +-------------------------------------------------------------------------+
  | Copyright (C) 2004 Juan Luis Frances Jimenez						    |
  | Copyright 2010-2014, Davide Franco			                            |
  |                                                                         |
  | This program is free software; you can redistribute it and/or           |
  | modify it under the terms of the GNU General Public License             |
  | as published by the Free Software Foundation; either version 2          |
  | of the License, or (at your option) any later version.                  |
  |                                                                         |
  | This program is distributed in the hope that it will be useful,         |
  | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
  | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
  | GNU General Public License for more details.                            |
  +-------------------------------------------------------------------------+
*/

session_start();
include_once( 'core/global.inc.php' );

// Instanciate, bootstrap and run new Application instance (Bacula-Web)
$bwapp = new BwApplication('application/config/bwapp.config.php');
$bwapp->bootstrap();
$bwapp->run();
/*
// Initialise view and model
$view 	= new View();
$dbSql 	= null;

try {
 $dbSql = new Bweb($view);

// Custom period for dashboard
$custom_period = array( LAST_DAY, NOW);		// defautl period is last day

if( isset($_POST['period_selector']) and !is_null($_POST['period_selector']) ) {
 $view->assign( 'custom_period_list_selected', $_POST[period_selector]);

 switch($_POST['period_selector']) {
  case 'last_day':
    $custom_period = array( LAST_DAY, NOW);
    break;
  case 'last_week':
    $custom_period = array( LAST_WEEK, NOW);
    break;
  case 'last_month':
    $custom_period = array( LAST_MONTH, NOW);
    break;
  case 'since_bot':
    $custom_period = array( FIRST_DAY, NOW);
    break;
 }
}else {
 $view->assign( 'custom_period_list_selected', 'last_day');
}

 $custom_period_list = array( 	'last_day' => 'Last 24 hours',
				'last_week' => 'Last week',
				'last_month' => 'Last month',
				'since_bot' => 'Since BOT');
 $view->assign('custom_period_list', $custom_period_list);

 // Set period start - end for widget header
 $view->assign('literal_period', date("D j M Y ", $custom_period[0]) . ' to ' . date("D j M Y ", $custom_period[1]) );

 // Running, completed, failed, waiting and canceled jobs status over last 24 hours
 $view->assign( 'running_jobs', Jobs_Model::count_Jobs( $dbSql->db_link, $custom_period, 'running') );
 $view->assign( 'completed_jobs', Jobs_Model::count_Jobs( $dbSql->db_link, $custom_period, 'completed') );
 $view->assign( 'failed_jobs', Jobs_Model::count_Jobs( $dbSql->db_link, $custom_period, 'failed') );
 $view->assign( 'waiting_jobs', Jobs_Model::count_Jobs( $dbSql->db_link, $custom_period, 'waiting') );
 $view->assign( 'canceled_jobs', Jobs_Model::count_Jobs( $dbSql->db_link, $custom_period, 'canceled') );

 // Stored files number 
 $view->assign( 'stored_files', CUtils::format_Number( Jobs_Model::getStoredFiles( $dbSql->db_link, $custom_period ) ) );
 
 // Overall stored bytes
 $view->assign( 'stored_bytes', CUtils::Get_Human_Size( Jobs_Model::getStoredBytes( $dbSql->db_link, $custom_period ) ) );

 // Database size
 $view->assign( 'database_size', Database_Model::get_Size( $dbSql->db_link, $dbSql->catalog_current_id ) );
 
 // Total bytes and files stored over the last 24 hours
 $view->assign( 'bytes_last', CUtils::Get_Human_Size( Jobs_Model::getStoredBytes( $dbSql->db_link, $custom_period ) ) );
 $view->assign( 'files_last', CUtils::format_Number( Jobs_Model::getStoredFiles( $dbSql->db_link, $custom_period ) ) );

 // Number of clients
 $view->assign('clients', Clients_Model::count($dbSql->db_link) );

 // Defined Jobs and Filesets
 $view->assign( 'defined_filesets', FileSets_Model::count( $dbSql->db_link ) );
 $view->assign( 'defined_jobs', Jobs_Model::count_Job_Names( $dbSql->db_link ) );

 // Incremental, Differential and Full jobs over the last 24 hours
 $view->assign('incr_jobs', Jobs_Model::count_Jobs( $dbSql->db_link, $custom_period, null, J_INCR) );
 $view->assign('diff_jobs', Jobs_Model::count_Jobs( $dbSql->db_link, $custom_period, null, J_DIFF) );
 $view->assign('full_jobs', Jobs_Model::count_Jobs( $dbSql->db_link, $custom_period, null, J_FULL) );

 // Volumes disk usage
 $volumes_size = Volumes_Model::getDiskUsage($dbSql->db_link);
 $view->assign('volumes_size', CUtils::Get_Human_Size( $volumes_size ) );

 // Pools count
 $view->assign( 'pools_nb', Pools_Model::count( $dbSql->db_link ) );

 // Backup Job list
 $view->assign('jobs_list', Jobs_Model::get_Jobs_List($dbSql->db_link) );
 
 // Clients list
 $view->assign('clients_list', Clients_Model::getClients($dbSql->db_link) );

 // Count volumes
 $view->assign('volumes_nb', Volumes_Model::count($dbSql->db_link) );

 // ==============================================================
 // Last period <Job status graph>
 // ==============================================================
 $jobs_status = array('Running', 'Completed', 'Waiting', 'Failed', 'Canceled');
 $jobs_status_data = array();

 foreach ($jobs_status as $status) {
	$jobs_count 		= Jobs_Model::count_Jobs( $dbSql->db_link, $custom_period, strtolower($status) );
	$jobs_status_data[] = array($status, $jobs_count );
 }
 
 $graph = new CGraph("graph.png");
 $graph->SetData($jobs_status_data, 'pie');
 $graph->SetGraphSize(350, 220);
 $graph->setPieLegendColors( array( 'gray', 'green','blue', 'red', 'orange') );

 // Graph rendering
 $view->assign( 'graph_jobs', $graph->Render() );
 unset($graph);

 // ==============================================================
 // Volumes per pool widget
 // ==============================================================

 $vols_by_pool = array();
 $graph = new CGraph("graph1.png");
 $max_pools = '9';
 $table_pool = 'Pool';
 $limit = '';
 $sum_vols = '';

 // Count defined pools in catalog
 $pools_count = Pools_Model::count( $dbSql->db_link );

 // Display 9 biggest pools and rest of volumes in 10th one display as Other
 if ($pools_count > $max_pools) {
    if( CDB::getDriverName() == 'pgsql') {
	  $limit = $max_pools . 'OFFSET ' . ($pools_count - $max_pools);
    }else{
	  $limit = $max_pools . ',' . ($pools_count - $max_pools);
    }

    $query = array( 'table' => $table_pool,
		            'fields' => array('SUM(numvols) AS sum_vols'),
                    'limit' => $limit,
                    'groupby' => 'name');
    
	$result 	= CDBUtils::runQuery( CDBQuery::get_Select($query), $dbSql->db_link);
	$sum_vols 	= $result->fetch();
 } else {
    $limit      = $pools_count;
 }

 // Check database driver for pool table name
 if ( CDB::getDriverName() == 'pgsql') {
	$table_pool = strtolower($table_pool);
 }

 $query = array('table' => $table_pool, 'fields' => array('poolid,name,numvols'), 'orderby' => 'numvols DESC', 'limit' => $max_pools);
 $result = CDBUtils::runQuery( CDBQuery::get_Select($query), $dbSql->db_link );

 foreach ($result as $pool) {
    $vols_by_pool[] = array($pool['name'], $pool['numvols']);
 }

 if ($pools_count > $max_pools) 
 	$vols_by_pool[] = array('Others', $sum_vols['sum_vols']);

 $graph->SetData($vols_by_pool, 'pie');
 $graph->SetGraphSize(350, 220);

 // Graph rendering
 $view->assign( 'graph_pools', $graph->Render() );

 // ==============================================================
 // Last 7 days stored Bytes widget
 // ==============================================================
 $days_stored_bytes = array();
 $days = DateTimeUtil::getLastDaysIntervals(7);

 foreach ($days as $day) {
    $stored_bytes 			= CUtils::Get_Human_Size( Jobs_Model::getStoredBytes( $dbSql->db_link, array($day['start'], $day['end']) ), 1, 'GB', false );
    $days_stored_bytes[] 	= array(date("m-d", $day['start']), $stored_bytes);
 } 
 
 $graph = new CGraph("graph2.png");
 $graph->SetData($days_stored_bytes, 'bars');
 $graph->SetGraphSize(350, 220);
 $graph->SetYTitle("GB");

 // Graph rendering
 $view->assign( 'graph_stored_bytes', $graph->Render() );

 // ==============================================================
 // Last used volumes widget
 // ==============================================================

 $last_volumes = array();

 // Building SQL statment
 $where = array();
 $tmp   = "(Media.Volstatus != 'Disabled') ";

 switch ( CDB::getDriverName() ) {
    case 'mysql':
    case 'pgsql':
        $tmp .= "AND (Media.LastWritten IS NOT NULL)";
        break;
    case 'sqlite':
        $tmp .= "AND (Media.Lastwritten != 0)"; 
        break;
 }
 
 $where[] = $tmp;

 $statment = array(	'table' 	=> 'Media',
					'fields' 	=> array('Media.MediaId', 'Media.Volumename', 'Media.Lastwritten', 'Media.VolStatus', 'Media.VolJobs', 'Pool.Name AS poolname'),
					'join' 		=> array('table' => 'Pool', 'condition' => 'Media.PoolId = Pool.poolid'),
					'where' 	=> $where,
					'orderby' 	=> 'Media.Lastwritten DESC',
					'limit' 	=> '10');

 // Run the query
 $result 	= CDBUtils::runQuery( CDBQuery::get_Select($statment), $dbSql->db_link );

 foreach( $result as $volume ) {
    // odd or even row
    if ((count($last_volumes) % 2) > 0)
        $volume['odd_even'] = "even";

    $last_volumes[] = $volume;
 }

 $view->assign('volumes_list', $last_volumes);
 
 }catch (Exception $e) {
    CErrorHandler::displayError($e);
 }
 
 // Set page name
 $view->assign('page_name', 'Dashboard');
 
 // Render template
 $view->render('index.tpl');
*/
?>
