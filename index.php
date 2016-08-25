<?php
/**
* @package Watchful.li siteoverview with Excel export
* @author Rene Kreijveld
* @authorUrl https://about.me/renekreijveld
* @copyright (c) 2016, Rene Kreijveld
*/

// Config
define('API_KEY', 'add-your-watchful.li-api-key-here');
define('BASE_URL', 'https://app.watchful.li/api/v1');

// Show only published websites? Then set SHOW_ONLY_PUBLISHED to true.
// Show all sites? Then set SHOW_ONLY_PUBLISHED to false.
define('SHOW_ONLY_PUBLISHED', true);

// Get base URL
function getUrl() {
	$url = strtok($_SERVER["REQUEST_URI"],'?');
	return $url;
}

/** Include PHPExcel */
require_once dirname(__FILE__) . '/PHPExcel.php';

// Setup curl call, request json format
if (SHOW_ONLY_PUBLISHED) {
	$ch = curl_init(BASE_URL . '/sites?published=1&limit=100&order=access_url+');
} else {
	$ch = curl_init(BASE_URL . '/sites?limit=100&order=access_url+');
}
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
	$updates = false;
	if ($task == "showupdates") {
		$updates = true;
		$task = "showlist";
	}
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
			$nrUpdates = 0;
			$tableHtml = '<table id="WFTable" class="table">';
			$tableHtml .= '<thead>';
			$tableHtml .= '<tr>';
			$tableHtml .= '<th>site id</th>';
			$tableHtml .= '<th>url</th>';
			$tableHtml .= '<th>joomla</th>';
			$tableHtml .= '<th>up</th>';
			$tableHtml .= '<th>updates</th>';
			$tableHtml .= '<th>ip</th>';
			$tableHtml .= '<th>php</th>';
			$tableHtml .= '<th>mysql</th>';
			$tableHtml .= '</tr>';
			$tableHtml .= '</thead>';
			$tableHtml .= '<tbody>';
			// process all sites, build HTML table of site data
			foreach ($sitesdata as $site) :
				if (!$updates || ($updates && $site->nbUpdates > 0)) {
					$siteData = "<a target='_blank' href='" . $site->access_url . "'>Frontend</a><br/><a target='_blank' href='" . $site->admin_url . "'>Administrator</a>";
					$siteStatus = ($site->up == 2) ? '<span class="text-success"><i class="fa fa-check"></i></span>' : '<span class="text-danger"><i class="fa times-circle"></i></span>';
					$tableHtml .= '<tr>';
					$tableHtml .= '<td class="siteid">' . $site->siteid . '</td>';
					$tableHtml .= '<td class="url"><a data-toggle="popover" title="' . $site->name . '" data-html="true" data-placement="top" data-content="' . $siteData . '">' . $site->access_url . '</a></td>';
					$tableHtml .= '<td class="jversion">' . $site->j_version . '</td>';
					$tableHtml .= '<td class="sitestatus">' . $siteStatus . '</td>';
					$tableHtml .= '<td class="updates">' . ($site->nbUpdates > 0 ? '<span class="label label-danger">' : '') . $site->nbUpdates . ($site->nbUpdates > 0 ? '</span>' : '') . '</td>';
					$tableHtml .= '<td class="ip">' . $site->ip . '</td>';
					$tableHtml .= '<td class="php">' . $site->php_version . '</td>';
					$tableHtml .= '<td class="mysql">' . $site->mysql_version . '</td>';
					$tableHtml .= '</tr>';
					unset($site->tags);
					if ($site->nbUpdates > 0) $updateSites++;
					$nrUpdates += $site->nbUpdates;
				}
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
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="René Kreijveld">

		<title>Watchful.li sitelist</title>

		<!-- Bootstrap core CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<!-- Datatables  CSS -->
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.6/jq-2.2.3/dt-1.10.12/datatables.min.css"/>
		<!-- Font Awesome -->
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">

		<!-- Datatables -->
		<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.6/jq-2.2.3/dt-1.10.12/datatables.min.js"></script>

		<style type="text/css">
			.jumbotron {padding: 16px 0}
			.jumbotron h1 {margin-top: 10px; margin-bottom: 20px;}
			.jumbotron h3 {margin-top: 0;}
			h3.popover-title {min-width: 200px;}
		</style>
	</head>

	<body>
		<div class="jumbotron">
			<div class="container">
				<h1>Watchful.li sitelist</h1>
				<div class="row">
					<div class="col-md-8">
						<h3>
							Websites: <span class="label label-success"><?php echo $totalSites;?></span>&nbsp;
							Sites with updates: <span class="label label-<?php echo ($updateSites == 0) ? 'success' : 'danger';?>"><?php echo $updateSites;?></span>&nbsp;
							Updates available: <span class="label label-<?php echo ($nrUpdates == 0) ? 'success' : 'danger';?>"><?php echo $nrUpdates;?></span>
						</h3>
					</div>
					<div class="col-md-4">
						<p class="pull-right">
							<a href="<?php echo getUrl().'?task=showupdates';?>" class="btn btn-danger"><i class="fa fa-bolt"></i> Updates</a>&nbsp;
							<a href="<?php echo getUrl();?>" class="btn btn-primary"><i class="fa fa-list"></i> All sites</a>&nbsp;
							<a target="_blank" href="<?php echo getUrl().'?task=doexcel';?>" class="btn btn-success"><i class="fa fa-table"></i> Excel export</a>
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="container">
			<?php echo $tableHtml; ?>
			<hr>
			<footer>
				<p style="font-size: 12px;">Data collected <i class="fa fa-calendar"></i> <?php echo date("Y-m-d"); ?> <i class="fa fa-clock-o"></i> <?php echo date("H:i:s"); ?>, written by <a href="https://about.me/renekreijveld" target="_blank">René Kreijveld</a>. <i class="fa fa-github"></i> <a href="https://github.com/renekreijveld/Watchful.li-sitelist" target="_blank">View on Github</a> All data collected through <a href="https://watchful.li/support-services/kb/article/watchful-rest-api" target="_blank">Watchful REST API</a>. Display with <a href="https://www.datatables.net" target="_blank">jQuery DataTables plugin</a>.</p>
			</footer>
		</div>
		<script>
		$(document).ready(function(){
			var table = $('#WFTable').DataTable( {
				stateSave: true
			} );
			table.on( 'draw', function () {
				$('.url a').popover();
			} );
		});
		$('.url a').popover();
		</script>
	</body>
</html>