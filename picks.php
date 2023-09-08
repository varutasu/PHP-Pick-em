<?php
require_once('includes/application_top.php');
require('includes/classes/team.php');
include('includes/header.php');

// Add page title
echo '<div id="page-title" class="col-xs-8 left">';
echo '<i class="bi bi-calendar-check-fill"></i><h1 class="display-1 title">Picks</h1>';  
echo '</div>';

// Get list of years
$sql = "SELECT yearNum FROM nflp_schedule";
$result = mysqli_query($conn, $sql);
$years = array();
while($row = mysqli_fetch_assoc($result)) {
  $years[] = $row['yearNum'];
}

// Display year dropdown
echo '<select name="year">';
foreach ($years as $year) {
  echo "<option value='$year'>$year</option>"; 
}
echo '</select>';

// Get list of weeks
$sql = "SELECT weekNum FROM nflp_schedule";
$result = mysqli_query($conn, $sql);
$weeks = array();
while($row = mysqli_fetch_assoc($result)) {
  $weeks[] = $row['weekNum']; 
}

// Display week dropdown
echo '<select name="week">';
foreach ($weeks as $week) {
  echo "<option value='$week'>Week $week</option>";
}
echo '</select>';

// Get games for selected week
$week = $_GET['week']; 
$sql = "SELECT * FROM nflp_schedule WHERE weekNum = $week";
$result = mysqli_query($conn, $sql);

// Display games
while ($row = mysqli_fetch_assoc($result)) {

  $homeTeam = new Team($row['team1_id']);
  $awayTeam = new Team($row['team2_id']);
  
  echo '<div class="game">';

  echo '<div class="team">';
  echo '<img src="'.$homeTeam->logo.'">';
  echo '<span>'.$homeTeam->name.'</span>';  
  echo '</div>';

  echo 'vs';

  echo '<div class="team">';
  echo '<img src="'.$awayTeam->logo.'">';
  echo '<span>'.$awayTeam->name.'</span>';
  echo '</div>';

  echo '<input type="radio" name="game'.$row['id'].'" value="'.$homeTeam->id.'"> '.$homeTeam->name;
  echo '<input type="radio" name="game'.$row['id'].'" value="'.$awayTeam->id.'"> '.$awayTeam->name;

  echo '</div>';

}

?>