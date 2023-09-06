<?php

// Get the current year
$currentYear = date('Y');

// Connect to Yahoo's API
$url = 'https://fantasysports.yahooapis.com/fantasy/v2/games;game_codes=nfl;seasons='.$currentYear;

// Set the Authorization header
$headers = array('Authorization: Bearer YOUR_OAUTH_TOKEN');

$jsonData = file_get_contents($url, false, stream_context_create(array('http' => array('header' => $headers))));

// Parse the JSON feed
$json = json_decode($jsonData, true);

// Get the games
$games = $json['fantasy_content']['games'];

// Insert the games into the database
foreach ($games as $game) {

        //get game time (eastern)
        $gameTimeEastern = $game['game_time']['start_time_et'];

        //get team codes
        $away_team = $game['away_team_code'];
        $home_team = $game['home_team_code'];

        // Insert the game into the database
        $sql = "INSERT INTO nflp_schedule (year, weekNum, gameTimeEastern, homeID, visitorID)
                VALUES ($currentYear, 1, '$gameTimeEastern', '$home_team', '$away_team');";
        mysqli_query($conn, $sql);

        echo "Added game: $home_team vs $away_team";
}

