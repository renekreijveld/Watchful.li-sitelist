<?php
/**
 * @package       Watchful.li siteoverview with Excel export
 * @author        Rene Kreijveld
 * @authorUrl https://about.me/renekreijveld
 * @copyright (c) 2016, Rene Kreijveld
 */

// Get base URL
function getUrl()
{
	$url = strtok($_SERVER["REQUEST_URI"], '?');

	return $url;
}

/** Include Config */
require_once dirname(__FILE__) . '/config.php';

/** Include PHPExcel */
require_once dirname(__FILE__) . '/PHPExcel.php';

// Defauts
$sitesdata     = null;
$watchfulerror = null;

// Demo data as sitedata
if (SHOW_DEMO_DATA)
{
	$sitesdata = $demoData;
}

// Real data as sitedata
if (!SHOW_DEMO_DATA)
{
	// Setup curl call, request json format
	if (SHOW_ONLY_PUBLISHED)
	{
		$ch = curl_init(BASE_URL . '/sites?published=1&limit=100&order=access_url+');
	}
	else
	{
		$ch = curl_init(BASE_URL . '/sites?limit=100&order=access_url+');
	}

	$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_CUSTOMREQUEST  => 'GET',
		CURLOPT_HTTPHEADER     => array(
			'api_key: ' . API_KEY,
			'Content-type: application/json',
			'Accept: application/json'
		),
	);

	curl_setopt_array($ch, ($options));

	// Get Watchful response
	$watchful = json_decode(curl_exec($ch));

	// Website data and errors
	if (isset($watchful->msg->data))
	{
		$sitesdata     = $watchful->msg->data;
		$watchfulerror = null;
	}
	else
	{
		$sitesdata     = null;
		$watchfulerror = $watchful->msg;
	}
}

// Process website data
if ($sitesdata)
{
	$task    = isset($_GET["task"]) ? $_GET["task"] : "showlist";
	$updates = false;

	if ($task == "showupdates")
	{
		$updates = true;
		$task    = "showlist";
	}

	switch ($task)
	{
		case "doexcel":
			$start       = $sitesdata[0];
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Watchful.li")
				->setLastModifiedBy("Watchful.li")
				->setTitle("Watchful.li sitelist")
				->setSubject("Watchful.li sitelist");
			$objPHPExcel->setActiveSheetIndex(0);

			$rowArray = [];
			foreach ((array) $start as $var => $value)
			{
				$rowArray[] = $var;
			}

			$objPHPExcel->getActiveSheet()->fromArray($rowArray, null, 'A1');
			$objPHPExcel->getActiveSheet()->getStyle('A1:DD1')->getFont()->setBold(true);

			$start = 2;
			foreach ($sitesdata as $i)
			{
				$rowArray = [];
				foreach ((array) $i as $var => $value)
				{
					$rowArray[] = $value;
				}

				$objPHPExcel->getActiveSheet()->fromArray($rowArray, null, 'A' . $start);
				$start++;
			}

			$objPHPExcel->getActiveSheet()->setTitle('Watchful.li sitelist');
			$objPHPExcel->setActiveSheetIndex(0);

			// Set Excel headers
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="watchfulli_sitelist.' . date('Ymd') . '.' . date('His') . '.xlsx"');
			header('Cache-Control: max-age=1');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
			header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header('Pragma: public'); // HTTP/1.0

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save('php://output');
			exit;
			break;

		case "showlist":
			$totalSites  = count($sitesdata);
			$updateSites = 0;
			$nrUpdates   = 0;

			// Process all sites, build HTML table of site data
			foreach ($sitesdata as $site)
			{
				if (!$updates || ($updates && $site->nbUpdates > 0))
				{
					if ($site->nbUpdates > 0) $updateSites++;
					$nrUpdates += $site->nbUpdates;
				}
			}

			break;
	}
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

	<!-- Bootstrap, jQuery and Datatables CSS -->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.6/jq-2.2.3/dt-1.10.12/datatables.min.css"/>
	<!-- Font Awesome CSS -->
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
	<!-- Bootstrap, jQuery and Datatables Javascript -->
	<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.6/jq-2.2.3/dt-1.10.12/datatables.min.js"></script>

	<style type="text/css">
		.jumbotron {
			padding: 16px 0
		}

		.jumbotron h1 {
			margin-top: 10px;
			margin-bottom: 20px;
		}

		.jumbotron h3 {
			margin-top: 0;
		}

		h3.popover-title {
			min-width: 200px;
		}

		.toggle-vis, .show-all-columns, .url a {
			cursor: pointer;
		}
	</style>
</head>

<body>
<div class="jumbotron">
	<div class="container">
		<h1>Watchful.li sitelist</h1>

		<?php if ($sitesdata): ?>
			<div class="row">
				<div class="col-md-7">
					<h3>
						Websites: <span class="label label-success"><?php echo $totalSites; ?></span>&nbsp;
						Sites with updates:
						<span class="label label-<?php echo ($updateSites == 0) ? 'success' : 'danger'; ?>"><?php echo $updateSites; ?></span>&nbsp;
						Updates:
						<span class="label label-<?php echo ($nrUpdates == 0) ? 'success' : 'danger'; ?>"><?php echo $nrUpdates; ?></span>
					</h3>
				</div>
				<div class="col-md-5">
					<p class="pull-right">
						<a href="#" class="btn btn-danger showupdates"><i class="fa fa-bolt"></i> Updates</a>&nbsp;
						<a href="#" class="btn btn-primary showallsites"><i class="fa fa-list"></i> All sites</a>&nbsp;
						<a href="<?php echo getUrl(); ?>" class="btn btn-primary"><i class="fa fa-refresh"></i> Refresh</a>&nbsp;
						<a target="_blank" href="<?php echo getUrl() . '?task=doexcel'; ?>" class="btn btn-success"><i class="fa fa-table"></i> Excel export</a>
					</p>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="container">
	<?php if ($sitesdata): ?>
		<div class="row">
			<div class="col-md-8">
				<div class="btn-toolbar">
					<div class="btn-group">Show/hide columns:</div>
					<div class="btn-group" role="group" aria-label="Toggle Columns">
						<button type="button" class="btn btn-primary btn-sm show-all-columns">show all</button>
						<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="0">site id</button>
						<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="2">joomla</button>
						<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="3">up</button>
						<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="4">updates</button>
						<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="5">ip</button>
						<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="6">webserver</button>
						<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="7">php</button>
						<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="8">mysql</button>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<p class="pull-right">Showing
					<strong><?php echo(SHOW_DEMO_DATA ? 'demo data' : 'live data from Watchful.li'); ?></strong>
				</p>
			</div>
		</div>

		<hr>

		<table id="WFTable" class="table">
			<thead>
			<tr>
				<th>site id</th>
				<th>url</th>
				<th>joomla</th>
				<th>up</th>
				<th>updates</th>
				<th>ip</th>
				<th>webserver</th>
				<th>php</th>
				<th>mysql</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($sitesdata as $site): ?>
				<?php $siteData = "<a target='_blank' href='" . $site->access_url . "'>Frontend</a><br/><a target='_blank' href='" . $site->admin_url . "'>Administrator</a>"; ?>
				<tr>
					<td class="siteid"><?php echo $site->siteid; ?></td>
					<td class="url">
						<a data-toggle="popover" title="<?php echo $site->name; ?>" data-html="true" data-placement="top" data-content="<?php echo $siteData; ?>"><?php echo $site->access_url; ?></a>
					</td>
					<td class="jversion"><?php echo $site->j_version; ?></td>
					<td class="sitestatus">
						<?php if ($site->up == 2): ?>
							<span class="text-success"><i class="fa fa-check"></i></span>
						<?php else: ?>
							<span class="text-danger"><i class="fa times-circle"></i></span>
						<?php endif; ?>
					</td>
					<td class="updates">
						<?php if ($site->nbUpdates > 0): ?>
							<span class="text-danger">yes (<?php echo $site->nbUpdates; ?>)</span>
						<?php else: ?>
							no
						<?php endif; ?>
					</td>
					<td class="ip"><?php echo $site->ip; ?></td>
					<td class="apache"><?php echo $site->server_version; ?></td>
					<td class="php"><?php echo $site->php_version; ?></td>
					<td class="mysql"><?php echo $site->mysql_version; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php else: ?>
		<div class="alert alert-danger" role="alert"><?php echo $watchfulerror; ?></div>
	<?php endif; ?>

	<hr>

	<footer>
		<p style="font-size: 12px;">Data collected <i class="fa fa-calendar"></i> <?php echo date("Y-m-d"); ?>
			<i class="fa fa-clock-o"></i> <?php echo date("H:i:s"); ?>, written by
			<a href="https://about.me/renekreijveld" target="_blank">René Kreijveld</a>.
			<i class="fa fa-github"></i>
			<a href="https://github.com/renekreijveld/Watchful.li-sitelist" target="_blank">View on Github</a>.
			All data collected through
			<a href="https://watchful.li/support-services/kb/article/watchful-rest-api" target="_blank">Watchful REST API</a>.
			Display with <a href="https://www.datatables.net" target="_blank">jQuery DataTables plugin</a>.
		</p>
	</footer>
</div>

<script>
	function showUpdates() {
		$('#WFTable').DataTable().column(4).search('yes').draw();
	}

	function showAll() {
		$('#WFTable').DataTable().column(4).search('').draw();
	}

	$(document).ready(function () {
		var table = $('#WFTable').DataTable({
			stateSave: true,
			autoWidth: false
		});

		$(".showupdates").click(function () {
			showUpdates();
		});

		$(".showallsites").click(function () {
			showAll();
		});

		$(".show-all-columns").click(function () {
			table.columns().visible(true);
			$('.toggle-vis').addClass('active');
		});

		$('.toggle-vis').on('click', function (e) {
			e.preventDefault();
			// Get the column API object
			var column = table.column($(this).attr('data-column'));
			// Toggle the visibility
			column.visible(!column.visible());
			$(this).toggleClass('active');
		});

		$('.url a').popover();
	});
</script>

</body>
</html>