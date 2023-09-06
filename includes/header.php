<?php
header('Content-Type:text/html; charset=utf-8');
header('X-UA-Compatible:IE=Edge,chrome=1'); //IE8 respects this but not the meta tag when under Local Intranet
?>
<!DOCTYPE html>
<html xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo SITE_NAME . ' ' . SEASON_YEAR; ?></title>
	<base href="<?php echo SITE_URL; ?>" />
	<link rel="stylesheet" type="text/css" media="all" href="css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="css/all.css" />
	<link rel="stylesheet" type="text/css" media="all" href="css/custom.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/jquery.countdown.css" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
	<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/modernizr-2.7.0.min.js"></script>
	<script type="text/javascript" src="js/svgeezy.min.js"></script>
	<script type="text/javascript" src="js/jquery.main.js"></script>

	<script type="text/javascript" src="js/jquery.jclock.js"></script>
	<script type="text/javascript" src="js/jquery.plugin.min.js"></script>
	<script type="text/javascript" src="js/jquery.countdown.min.js"></script>
   	<script>
		$(document).ready(function() {

					var CurrentUrl= document.URL;
					var CurrentUrlEnd = CurrentUrl.split('/').filter(Boolean).pop();
					console.log(CurrentUrlEnd);
					$( "#nav li a" ).each(function() {
						var ThisUrl = $(this).attr('href');
						var ThisUrlEnd = ThisUrl.split('/').filter(Boolean).pop();

						if(ThisUrlEnd == CurrentUrlEnd){
						$(this).closest('li').addClass('active')
						}
					});

		});
	</script>
</head>

<body>
	<div class="container">
		<header id="header" class="row">
				<nav>
					<input type="checkbox" id="nav" class="hidden"/>
					<label for="nav" class="nav-open"><i></i><i></i><i></i></label>
					<div class="nav-container">
						<ul id="nav">
							<li class="btn"><a href="./"><span class="material-symbols-outlined">home</span> Home</a></li>
							<?php if ($user->userName !== 'admin') { ?>
							<li class="btn"><a href="entry_form.php<?php echo ((!empty($_GET['week'])) ? '?week=' . (int)$_GET['week'] : ''); ?>"><span class="material-symbols-outlined">event_available</span> Your Picks</a></li>
							<?php } ?>
							<li class="btn"><a href="results.php<?php echo ((!empty($_GET['week'])) ? '?week=' . (int)$_GET['week'] : ''); ?>"><span class="material-symbols-outlined">format_list_numbered</span> Results</a></li>
							<li class="btn"><a href="standings.php"><i class="bi bi-trophy-fill"></i> Leaderboard</a></li>
						</ul>
					</div>
				</nav>
						<div class="profile">
							<div class="btn btn-icon"><a href="rules.php" title="Rules/Help"><span class="material-symbols-outlined">help</span></a>
							</div>
							<div class="dropdown">
								<button class="btn dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								<span class="material-symbols-outlined">account_circle</span>
								<span class="text"><?php echo $_SESSION['loggedInUser']; ?></span><b class="caret"></b>
								</button>
								<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
								<?php if ($_SESSION['logged'] === 'yes' && $user->is_admin) { ?>
									<li><a href="scores.php">Enter Scores</a></li>
									<li><a href="getHtmlScores.php?BATCH_SCORE_UPDATE_KEY=<?php echo BATCH_SCORE_UPDATE_KEY; ?>">Auto-Update Scores</a></li>
									<li><a href="send_email.php">Send Email</a></li>
									<li><a href="users.php">Update Users</a></li>
									<li><a href="schedule_edit.php">Edit Schedule</a></li>
									<li><a href="edit_picks.php">Edit Picks</a></li>
									<li><a href="email_templates.php">Email Templates</a></li>
									<?php } ?>	
									<li><a href="user_edit.php">My Account</a></li>
									<li><a href="logout.php">Logout <?php echo $user->userName; ?></a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
		</header>
		<div id="pageContent">
		<?php
		if ($user->is_admin && is_array($warnings) && sizeof($warnings) > 0) {
			echo '<div id="warnings">';
			foreach ($warnings as $warning) {
				echo $warning;
			}
			echo '</div>';
		}
		?>

