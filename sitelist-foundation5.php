<?php
/**
* @package Watchful.li siteoverview with Excel export, Foundation 5 version
* @author Rene Kreijveld
* @authorUrl https://about.me/renekreijveld
* @copyright (c) 2016, Rene Kreijveld
*/

//Config
define('API_KEY', 'add-your-watchful.li-api-key-here');
define('BASE_URL', 'https://watchful.li/api/v1');

// get base URL for refresh button
function getUrl() {
	$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
	$url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
	$url .= $_SERVER["REQUEST_URI"];
	return $url;
}

/** Include PHPExcel */
require_once dirname(__FILE__) . '/PHPExcel.php';

// setup curl call, request json format
$ch = curl_init(BASE_URL . '/sites?limit=100&order=access_url+');
$options = array(
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_CUSTOMREQUEST => 'GET',
	CURLOPT_HTTPHEADER => array(
		'api_key: ' . API_KEY,
		'Content-type: application/json',
		'Accept: application/json'
	),
);
curl_setopt_array($ch, ($options));
$watchful = json_decode(curl_exec($ch));

if (!$watchful->error) :
	$sitesdata = $watchful->msg->data;

	$task = $_GET["task"];
	if (is_null($task)) $task = "showlist";
	switch ($task) {
		case "doexcel":
			$start = $sitesdata[0];
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Watchful.li")
			 ->setLastModifiedBy("Watchful.li")
			 ->setTitle("Watchful.li sitelist")
			 ->setSubject("Watchful.li sitelist");
			$objPHPExcel->setActiveSheetIndex(0);
			$rowArray = [];
			foreach ((array)$start as $var => $value)
			{
				$rowArray[] = $var;
			}
			$objPHPExcel->getActiveSheet()->fromArray($rowArray, NULL, 'A1');
			$objPHPExcel->getActiveSheet()->getStyle('A1:DD1')->getFont()->setBold(true);
			$start = 2;
			foreach ($sitesdata as $i) :
				$rowArray = [];
				foreach ((array)$i as $var => $value)
				{
					$rowArray[] = $value;
				}
				$objPHPExcel->getActiveSheet()->fromArray($rowArray, NULL, 'A'.$start);
				$start++;
			endforeach;
			$objPHPExcel->getActiveSheet()->setTitle('Watchful.li sitelist');
			$objPHPExcel->setActiveSheetIndex(0);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="watchfulli_sitelist.' . date('Ymd') . '.' . date('His') . '.xlsx"');
			header('Cache-Control: max-age=1');
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			break;
		case "showlist":
			$totalSites = count($sitesdata);
			$updateSites = 0;
			$updates = 0;
			$tableHtml = '<table id="WFTable">';
			$tableHtml .= '<thead>';
			$tableHtml .= '<tr>';
			$tableHtml .= '<th>site id</th>';
			$tableHtml .= '<th>url</th>';
			$tableHtml .= '<th>joomla</th>';
			$tableHtml .= '<th>updates</th>';
			$tableHtml .= '<th>ip</th>';
			$tableHtml .= '<th>php</th>';
			$tableHtml .= '<th>mysql</th>';
			$tableHtml .= '</tr>';
			$tableHtml .= '</thead>';
			$tableHtml .= '<tbody>';
			// process all sites, build HTML table of site data
			foreach ($sitesdata as $site) :
				$tableHtml .= '<tr>';
				$tableHtml .= '<td>' . $site->siteid . '</td>';
				$tableHtml .= '<td>' . $site->access_url . '</td>';
				$tableHtml .= '<td>' . $site->j_version . '</td>';
				$tableHtml .= '<td>' . $site->nbUpdates . '</td>';
				$tableHtml .= '<td>' . $site->ip . '</td>';
				$tableHtml .= '<td>' . $site->php_version . '</td>';
				$tableHtml .= '<td>' . $site->mysql_version . '</td>';
				$tableHtml .= '</tr>';
				unset($site->tags);
				if ($site->nbUpdates > 0) $updateSites++;
				$updates += $site->nbUpdates;
			endforeach;
			$tableHtml .= '</tbody>';
			$tableHtml .= '</table>';
	}
endif;

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="description" content="Watchful.li sitelist local tool">
		<meta name="author" content="René Kreijveld">

		<title>Watchful.li sitelist</title>

		<!-- Foundation ZURB & DataTables -->
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/zf-5.5.2/jq-2.2.3/dt-1.10.12/datatables.min.css"/>
		<!-- Font Awesome -->
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">

		<!-- Foundation Zurb, jQuery and DataTables Javascript-->
		<script type="text/javascript" src="https://cdn.datatables.net/v/zf-5.5.2/jq-2.2.3/dt-1.10.12/datatables.min.js"></script>
		<style>
			.row {max-width: 86rem;}
		</style>

	</head>

	<body>
		<div class="panel">
			<div class="row">
				<div class="small-12 columns">
					<h1>Watchful.li sitelist</h1>
				</div>
			</div>
			<div class="row">
				<div class="small-8 columns">
					<p>
						Websites: <span class="success round label"><?php echo $totalSites;?></span>&nbsp;
						Sites with updates: <span class="<?php echo ($updateSites == 0) ? 'success' : 'alert';?> round label"><?php echo $updateSites;?></span>&nbsp;
						Updates available: <span class="<?php echo ($updates == 0) ? 'success' : 'alert';?> round label"><?php echo $updates;?></span>
					</p>
				</div>
				<div class="small-4 columns">
					<p class="text-right">
						<a target="_blank" href="<?php echo getUrl().'?task=doexcel';?>" class="success button small"><i class="fa fa-table"></i> Excel export</a>&nbsp;
						<a href="<?php echo getUrl();?>" class="button small"><i class="fa fa-refresh"></i> Refresh</a>
					</p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="small-12 columns">
				<?php echo $tableHtml; ?>
				<hr>
				<footer>
					<p style="font-size: 12px;">Data collected <i class="fa fa-calendar"></i> <?php echo date("Y-m-d"); ?> <i class="fa fa-clock-o"></i> <?php echo date("H:i:s"); ?>, written by <a href="https://about.me/renekreijveld" target="_blank">René Kreijveld</a>. <i class="fa fa-github"></i> <a href="https://github.com/renekreijveld/Watchful.li-sitelist" target="_blank">View on Github</a> All data collected through <a href="https://watchful.li/support-services/kb/article/watchful-rest-api" target="_blank">Watchful REST API</a>. Display with <a href="https://www.datatables.net" target="_blank">jQuery DataTables plugin</a>.</p>
				</footer>
			</div>
		</div>

		<script>
		$(document).foundation();
		$(document).ready(function(){
			$('#WFTable').DataTable();
		});
		</script>
	</body>
</html>