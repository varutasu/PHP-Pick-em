<?php
$SEASON_YEAR = 2023;
error_reporting(E_ALL);
$weeks = 18;
$teamCodes = array(
        'JAC' => 'JAX'
);
$schedule = array();

for ($week = 1; $week <= $weeks; $week++) {
        $url = "https://static.nfl.com/ajax/scorestrip?season=".$SEASON_YEAR."&seasonType=REG&week=".$week;
        if ($xmlData = @file_get_contents($url)) {
                $xml = simplexml_load_string($xmlData);
                $json = json_encode($xml);
                $games = json_decode($json, true);
        } else {
                die('Error getting schedule from nfl.com.');
        }

        //build scores array, to group teams and scores together in games
        foreach ($games['gms']['g'] as $gameArray) {
                $game = $gameArray['@attributes'];

                //get game time (eastern)
                $eid = $game['eid']; //date
                $t = $game['t']; //time
                $date = DateTime::createFromFormat('Ymds g:i a', $eid.' '.$t.' pm');
                $gameTimeEastern = $date->format('Y-m-d H:i:00');

                //get team codes
                $away_team = $game['v'];
                $home_team = $game['h'];
                foreach ($teamCodes as $espnCode => $nflpCode) {
                        if ($away_team == $espnCode) $away_team = $nflpCode;
                        if ($home_team == $espnCode) $home_team = $nflpCode;
                }

                $schedule[] = array(
                        'weekNum' => $week,
                        'gameTimeEastern' => $gameTimeEastern,
                        'homeID' => $home_team,
                        'visitorID' => $away_team
                );
        }
}

$output = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

DROP TABLE IF EXISTS `nflp_schedule`;
CREATE TABLE IF NOT EXISTS `nflp_schedule` (
  `gameID` int(11) NOT NULL AUTO_INCREMENT,
  `weekNum` int(11) NOT NULL,
  `gameTimeEastern` datetime DEFAULT NULL,
  `homeID` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `homeScore` int(11) DEFAULT NULL,
  `visitorID` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `visitorScore` int(11) DEFAULT NULL,
  `overtime` tinyint(1) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`gameID`),
  KEY `GameID` (`gameID`),
  KEY `HomeID` (`homeID`),
  KEY `VisitorID` (`visitorID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=257 ;

INSERT INTO `nflp_schedule` (`gameID`, `weekNum`, `gameTimeEastern`, `homeID`, `homeScore`, `visitorID`, `visitorScore`, `overtime`) VALUES'."\n";

for ($i = 0; $i < sizeof($schedule); $i++) {
        $output .= '('.($j = $i+1).','.$schedule[$i]['weekNum'].',\''.$schedule[$i]['gameTimeEastern'].'\',\''.$schedule[$i]['homeID'].'\',NULL,\''.$schedule[$i]['visitorID'].'\',NULL,0),'."\n";
}


$output .= "\n".'/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
';

// fix for IE catching or PHP bug issue
header("Pragma: public");
header("Expires: 0"); // set expiration time
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
// browser must download file from server instead of cache

$find = ',';
$replace = ';';
$result = preg_replace(strrev("/$find/"),strrev($replace),strrev($output),1);
echo strrev($result);
// Source: https://stackoverflow.com/questions/3835636/php-replace-last-occurrence-of-a-string-in-a-string

file_put_contents( "nfl_schedule_".$SEASON_YEAR.".sql", strrev($result));
system("dos2unix \"nfl_schedule_\"$SEASON_YEAR\".sql\"");

exit;
