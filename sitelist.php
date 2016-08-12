<?php
/**
* @package Watchful.li siteoverview with Excel export
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
if (!$watchful->error) $sitesdata = $watchful->msg->data;

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
		// retrieve data
		$watchful = json_decode(curl_exec($ch));

		// if no error proceed
		if (!$watchful->error) :
			$sitesdata = $watchful->msg->data;
			$SitesData = '<table class="table">';
			$SitesData .= '<tr>';
			$SitesData .= '<th>site id</th>';
			$SitesData .= '<th>url</th>';
			$SitesData .= '<th>joomla</th>';
			$SitesData .= '<th>ip</th>';
			$SitesData .= '<th>php</th>';
			$SitesData .= '<th>mysql</th>';
			$SitesData .= '</tr>';
			// process all sites, build HTML table of site data
			foreach ($sitesdata as $site) :
				$SitesData .= '<tr>';
				$SitesData .= '<td>' . $site->siteid . '</td>';
				$SitesData .= '<td>' . $site->access_url . '</td>';
				$SitesData .= '<td>' . $site->j_version . '</td>';
				$SitesData .= '<td>' . $site->ip . '</td>';
				$SitesData .= '<td>' . $site->php_version . '</td>';
				$SitesData .= '<td>' . $site->mysql_version . '</td>';
				$SitesData .= '</tr>';
				unset($site->tags);
			endforeach;
			$SitesData .= '</table>';
		endif;
}

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

		<!-- Font Awesome -->
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">

		<style type="text/css">
			.jumbotron {padding: 16px 0}
			.jumbotron h1 {margin-top: 10px}
		</style>
	</head>

	<body>
		<div class="jumbotron">
			<div class="container">
				<h1>Watchful.li sitelist</h1>
				<a target="_blank" href="<?php echo getUrl().'?task=doexcel';?>" class="btn btn-primary"><i class="fa fa-table"></i> Excel export</a>
			</div>
		</div>

		<div class="container">
			<?php echo $SitesData; ?>
			<hr>
			<footer>
				<p style="font-size: 12px;">Data collected <i class="fa fa-calendar"></i> <?php echo date("Y-m-d"); ?> <i class="fa fa-clock-o"></i> <?php echo date("H:i:s"); ?>, written by <a href="https://about.me/renekreijveld" target="_blank">René Kreijveld</a>. <i class="fa fa-github"></i> <a href="https://github.com/renekreijveld/Watchful.li-sitelist" target="_blank">View on Github</a> All data collected through <a href="https://watchful.li/support-services/kb/article/watchful-rest-api" target="_blank">Watchful REST API</a>.</p>
			</footer>
		</div>
	</body>
</html>