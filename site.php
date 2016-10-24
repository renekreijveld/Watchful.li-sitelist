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

// Defauts
$site          = null;
$watchfulerror = null;

// Get Site ID
$siteID = isset($_GET['id']) ? $_GET['id'] : 0;

// Demo data as sitedata
if (SHOW_DEMO_DATA)
{
	$site = $demoSiteData;
}

if (!$siteID)
{
	$watchfulerror = 'No Site ID set';
}

// Real data as sitedata
if ($siteID && !SHOW_DEMO_DATA)
{
	// Get Site Details
	$ch = curl_init(BASE_URL . '/sites/' . $siteID);

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
	if (isset($watchful->error) && $watchful->error)
	{
		$watchfulerror = $watchful->msg;
	}
	else
	{
		$site = $watchful->msg;
	}

	// Get Site Log
	$ch = curl_init(BASE_URL . '/logs?idx_site=' . $siteID . '&order=id_log-&limit=100');

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
	if (isset($watchful->error) && $watchful->error)
	{
		$watchfulerror = $watchful->msg;
	}
	else
	{
		$site->logs = $watchful->msg->data;
	}

	// Get Site Extensions
	$ch = curl_init(BASE_URL . '/sites/' . $siteID . '/extensions?limit=100');

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
	if (isset($watchful->error) && $watchful->error)
	{
		$watchfulerror = $watchful->msg;
	}
	else
	{
		$site->extensions = $watchful->msg->data;
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

	<title>Watchful.li sitelist<?php if ($site): ?> | <?php echo $site->name; ?><?php endif; ?></title>

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
		<h1 class="pull-left">Watchful.li sitelist</h1>
		<p class="pull-right">Showing
			<strong><?php echo(SHOW_DEMO_DATA ? 'demo data' : 'live data from Watchful.li'); ?></strong>
		</p>

		<?php if ($site): ?>
			<div class="row">
				<div class="col-md-7">
					<h3>
						Site:
						<a href="<?php echo $site->access_url; ?>"><?php echo $site->name; ?></a>
						<span class="label label-<?php echo ($site->new_j_version) ? 'danger' : 'success'; ?>"><i class="fa fa-joomla"></i> <?php echo $site->j_version; ?></span>
						Updates:
						<span class="label label-<?php echo ($site->nbUpdates == 0) ? 'success' : 'danger'; ?>"><?php echo $site->nbUpdates; ?></span>
					</h3>
				</div>
				<div class="col-md-5">
					<p class="pull-right">
						<a href="#logs" role="tab" data-toggle="tab" class="btn btn-primary active js-showtab"><i class="fa fa-list"></i> Logs</a>
						<a href="#extensions" role="tab" data-toggle="tab" class="btn btn-primary js-showtab"><i class="fa fa-plug"></i> Extensions</a>
						<a href="<?php echo getUrl(); ?>?id=<?php echo $siteID; ?>" class="btn btn-primary"><i class="fa fa-refresh"></i> Refresh</a>
						<!--
						@TODO: add email report functionality
						<a href="#" class="btn btn-success"><i class="fa fa-envelope"></i> E-mail report</a>
						-->
					</p>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="container">
	<?php if ($site): ?>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="logs">
				<?php if ($site->logs): ?>
					<div class="row">
						<div class="col-md-8">
							<div class="btn-toolbar">
								<div class="btn-group">Show/hide columns:</div>
								<div class="btn-group" role="group" aria-label="Toggle Columns">
									<button type="button" class="btn btn-primary btn-sm show-all-columns">show all</button>
									<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="0">entry</button>
									<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="1">type</button>
									<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="2">date</button>
								</div>
							</div>
						</div>
					</div>

					<hr>

					<table id="LogsTable" class="table">
						<thead>
						<tr>
							<th width="50%">entry</th>
							<th>type</th>
							<th>date</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($site->logs as $log): ?>
							<tr>
								<td class="log_entry">
									<?php echo $log->log_entry; ?>
								</td>
								<td class="logtype">
									<?php echo $log->log_type; ?>
								</td>
								<td class="log_date">
									<?php echo $log->log_date; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>

			<div role="tabpanel" class="tab-pane" id="extensions" data-page-length="50">
				<?php if ($site->extensions): ?>
					<div class="row">
						<div class="col-md-8">
							<div class="btn-toolbar">
								<div class="btn-group">Show/hide columns:</div>
								<div class="btn-group" role="group" aria-label="Toggle Columns">
									<button type="button" class="btn btn-primary btn-sm show-all-columns">show all</button>
									<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="0">name</button>
									<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="1">prefix</button>
									<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="2">date</button>
									<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="3">version</button>
									<button type="button" class="btn btn-default btn-sm toggle-vis active" data-column="4">update</button>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="btn-group pull-right" role="group" aria-label="Toggle Columns">
								<button type="button" class="btn btn-danger btn-sm showupdates">
									<i class="fa fa-bolt"></i> Updates
								</button>
								<button type="button" class="btn btn-primary btn-sm showallsites">
									<i class="fa fa-list"></i> All extensions
								</button>
							</div>
						</div>
					</div>

					<hr>

					<table id="ExtensionsTable" class="table">
						<thead>
						<tr>
							<th width="25%">name</th>
							<th width="25%">prefix</th>
							<th>date</th>
							<th>version</th>
							<th>new version</th>
							<th>update</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($site->extensions as $extension): ?>
							<tr class="<?php echo ($extension->vUpdate) ? 'danger' : ''; ?>">
								<td class="extname">
									<?php echo $extension->ext_name; ?><?php echo $extension->variant; ?>
								</td>
								<td class="extprefix">
									<?php echo $extension->ext_prefix; ?>
								</td>
								<td class="extdate">
									<?php echo $extension->date; ?>
								</td>
								<td class="extversion">
									<?php echo $extension->version; ?>
								</td>
								<td class="extnewversion">
									<?php echo ($extension->vUpdate) ? $extension->newVersion : ''; ?>
								</td>
								<td class="extupdate">
									<?php echo($extension->vUpdate); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
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
		$('#ExtensionsTable').DataTable().column(5).search('1').draw();
	}

	function showAll() {
		$('#ExtensionsTable').DataTable().column(5).search('').draw();
	}

	$(document).ready(function () {
		$(".showupdates").click(function () {
			showUpdates();
		});

		$(".showallsites").click(function () {
			showAll();
		});

		var tableLogs = $('#LogsTable').DataTable({
			stateSave: true,
			autoWidth: false,
			pageLength: 100
		});

		$(".show-all-columns").click(function () {
			tableLogs.columns().visible(true);
			$('.toggle-vis').addClass('active');
		});

		$('.toggle-vis').on('click', function (e) {
			e.preventDefault();
			// Get the column API object
			var column = tableLogs.column($(this).attr('data-column'));
			// Toggle the visibility
			column.visible(!column.visible());
			$(this).toggleClass('active');
		});

		var tableExtensions = $('#ExtensionsTable').DataTable({
			stateSave: true,
			autoWidth: false,
			pageLength: 100
		});

		$(".show-all-columns").click(function () {
			tableExtensions.columns().visible(true);
			$('.toggle-vis').addClass('active');
		});

		$('.toggle-vis').on('click', function (e) {
			e.preventDefault();
			// Get the column API object
			var column = tableExtensions.column($(this).attr('data-column'));
			// Toggle the visibility
			column.visible(!column.visible());
			$(this).toggleClass('active');
		});

		$('.js-showtab').on('click', function (e) {
			$('.js-showtab').toggleClass('active');
		});
	});
</script>

</body>
</html>