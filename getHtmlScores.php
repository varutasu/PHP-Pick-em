<?php
require('includes/application_top.php');

if ($_GET['execute'] != 1) {
	print "<a href=\"getHtmlScores.php?BATCH_SCORE_UPDATE_KEY=" . BATCH_SCORE_UPDATE_KEY . "&execute=1\">Execute?</a></br></br>";
} else {
	print "<a href=\"results.php\">Results</a></br></br>";
}

$week = (int)$_GET['week'];
if (empty($week)) {
	$week = (int)getCurrentWeek(); //get current week
}

//get schedules from file
$schedules_file = 'schedules.json';
$schedules = json_decode(file_get_contents($schedules_file), true);

//update database
foreach ($schedules as $schedule) {
	$homeScore = $schedule['homeScore'];
	$visitorScore = $schedule['visitorScore'];
	$overtime = $schedule['overtime'];
	$final = $schedule['final'];
	$gameID = $schedule['gameID'];

	$sql = "update " . DB_PREFIX . "schedule ";
	$sql .= "set homeScore = " . $homeScore . ", visitorScore = " . $visitorScore . ", overtime = " . $overtime . ", final = " . $final . " ";
	$sql .= "where gameID = " . $gameID;

	$mysqli->query($sql) or die('Error updating score: ' . $mysqli->error);
}

//see how the scores array looks for debugging
echo '<hr /><strong>Scores:</strong><br/><pre>' . print_r($schedules, true) . '</pre>';
echo '<hr /><strong>Raw Data:</strong><br/><pre>' . print_r($games, true) . '</pre>';
//echo json_encode($scores);
