<?php
header("Refresh: 300;url='results.php'");

require('includes/application_top.php');
require_once('includes/functions.php');

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Set default values for year and week if not provided in the URL
if (empty($_GET['year'])) {
    $year = date('Y'); // Set to the current year
} else {
    $year = (int)$_GET['year'];
}

if (empty($_GET['week'])) {
    $week = getCurrentWeek(); // Set to the current week using your function or method
} else {
    $week = (int)$_GET['week'];
}
    // Redirect to the current page with default year and week if needed
    if (empty($_GET['year']) || empty($_GET['week'])) {
        $currentScript = $_SERVER['PHP_SELF']; // Get the current script name
        header('Location: ' . $currentScript . '?year=' . $year . '&week=' . $week);
        exit;
    }

    $cutoffDateTime = getCutoffDateTime($week);
    $firstGameTime = getFirstGameTime($week);

include('includes/header.php');
?>

<div class="row flex">
    <div id="page-title" class="col-xs-8 left">
        <i class="bi bi-calendar-check-fill"></i><h1 class="display-1 title">Results</h1>
    </div>
    <div class="col-xs-4 right">
        <?php
        // Display year nav
        $sql = "SELECT DISTINCT year FROM " . DB_PREFIX . "schedule ORDER BY year;";
        if ($query = $mysqli->query($sql)) {
            $currentScript = $_SERVER['PHP_SELF']; // Get the current script name
            $yearNav = '<div class="btn-group"><button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $year . ' <span class="caret"></span></button>';
            $yearNav .= '    <ul class="dropdown-menu"> ';
            $i = 0;
            while ($row = $query->fetch_assoc()) {
                $yearNum = (int) $row['year']; // Assuming year is an integer
                $yearNav .= '<li><a href="' . $currentScript . '?year=' . $yearNum . '&week=' . $week . '">' . $yearNum . '</a></li>';
                $i++;
            }
            $query->free_result();
            $yearNav .= '    </ul>' . "\n";
            $yearNav .= '</div>' . "\n";
            echo $yearNav;
        } else {
            echo "Error in year query: " . $mysqli->error;
        }
        ?>

        <?php
        // Display week nav
        $sql = "SELECT DISTINCT weekNum FROM " . DB_PREFIX . "schedule ORDER BY weekNum;";
        if ($query = $mysqli->query($sql)) {
            $currentScript = $_SERVER['PHP_SELF']; // Get the current script name
            $weekNav = '<div class="btn-group"><button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Week ' . $week . ' <span class="caret"></span></button>';
            $weekNav .= '    <ul class="dropdown-menu"> ';
            $i = 0;
            while ($row = $query->fetch_assoc()) {
                $weekNum = (int) $row['weekNum']; // Assuming weekNum is an integer
                $weekNav .= '<li><a href="' . $currentScript . '?year=' . $year . '&week=' . $weekNum . '">Week ' . $weekNum . '</a></li>';
                $i++;
            }
            $query->free_result();
            $weekNav .= '    </ul>' . "\n";
            $weekNav .= '</div>' . "\n";
            echo $weekNav;
        } else {
            echo "Error in week query: " . $mysqli->error;
        }
        ?>
    </div>
</div>


<?php
//get array of games
$allScoresIn = true;
$games = array();
$sql = "select * from " . DB_PREFIX . "schedule where year = '" . $year . "' and weekNum = " . $week . " order by gameTimeEastern, gameID";
$query = $mysqli->query($sql);
while ($row = $query->fetch_assoc()) {
	$games[$row['gameID']]['gameID'] = $row['gameID'];
	$games[$row['gameID']]['homeID'] = $row['homeID'];
	$games[$row['gameID']]['visitorID'] = $row['visitorID'];
	// dtjr
	$games[$row['gameID']][$row['homeID']]['score'] = $row['homeScore'];
	$games[$row['gameID']][$row['visitorID']]['score'] = $row['visitorScore'];
	$games[$row['gameID']]['overtime'] = $row['overtime'];
	$games[$row['gameID']]['final'] = $row['final'];
	// dtjr
	$homeScore = (int)$row['homeScore'];
	$visitorScore = (int)$row['visitorScore'];
	if ( ($homeScore + $visitorScore > 0) && ($row['final'] == 1) ) {
		if ($homeScore > $visitorScore) {
			$games[$row['gameID']]['winnerID'] = $row['homeID'];
		}
		if ($visitorScore > $homeScore) {
			$games[$row['gameID']]['winnerID'] = $row['visitorID'];
		}
	} else {
		$games[$row['gameID']]['winnerID'] = '';
		$allScoresIn = false;
	}
}
$query->close();

// dtjr
# include('includes/column_right.php');
if (!$allScoresIn) {
	include('includes/column_countdown.php');
}
// dtjr

//get array of player picks
$playerPicks = array();
$playerTotals = array();
$sql = "select p.userID, p.gameID, p.pickID, p.points ";
$sql .= "from " . DB_PREFIX . "picks p ";
$sql .= "inner join " . DB_PREFIX . "users u on p.userID = u.userID ";
$sql .= "inner join " . DB_PREFIX . "schedule s on p.gameID = s.gameID ";
$sql .= "where s.weekNum = " . $week . " and u.userName <> 'admin' ";
$sql .= "order by p.userID, s.gameTimeEastern, s.gameID";
$query = $mysqli->query($sql);
$i = 0;
while ($row = $query->fetch_assoc()) {
	$playerPicks[$row['userID']][$row['gameID']] = $row['pickID'];
	if (!empty($games[$row['gameID']]['winnerID']) && $row['pickID'] == $games[$row['gameID']]['winnerID']) {
		//player has picked the winning team
		$playerTotals[$row['userID']] += 1;
	} else {
		$playerTotals[$row['userID']] += 0;
	}
	$i++;
}
$query->free;
?>
<script type="text/javascript">
$(document).ready(function(){
	$(".table tr").mouseover(function() {
		$(this).addClass("over");}).mouseout(function() {$(this).removeClass("over");
	});
	$(".table tr").click(function() {
		if ($(this).attr('class').indexOf('overPerm') > -1) {
			$(this).removeClass("overPerm");
		} else {
			$(this).addClass("overPerm");
		}
	});
	//$( "#table1" ).draggable({ containment: "#table1-container", scroll: false })
});
</script>
<style type="text/css">
.pickTD { width: 24px; font-size: 9px; text-align: center; }
</style>

<?php
if (!$allScoresIn) {
	echo '<div class="message warning"><span class="material-symbols-outlined">warning</span>Not all scores have been updated for week ' . $week . ' yet.</div>' . "\n";
}

$hideMyPicks = hidePicks($user->userID, $week);
if ($hideMyPicks && !$weekExpired) {
	echo '<p style="font-weight: bold; color: green;">* Your picks are currently hidden to other users.</p>' . "\n";
}

if (sizeof($playerTotals) > 0) {
?>




<!-- dtjr - beg scores -->
<div class="table-responsive">
<table class="table table-striped">
	<thead>
		<tr><th align="left">Scores</th></tr>
	</thead>
	<tbody>
<?php
	//loop through all games
	echo '<tr>' . "\n";
	echo '<td>Away</td>' . "\n";
	foreach($games as $game) {
		if ($game['visitorID'] == $game['winnerID'] && (int)$game['final'] == 1) {
			echo '<td><span class="winner">' . $game['visitorID'] . "&nbsp;-&nbsp;" . $game[$game['visitorID']]['score'] . '</span></td>' . "\n";
			// echo '<td><span class="winner">' . $game[$game['visitorID']]['score'] . '</span></td>' . "\n";
		} else {
			echo '<td>' . $game['visitorID'] . "&nbsp;-&nbsp;" . $game[$game['visitorID']]['score'] . '</td>' . "\n";
			// echo '<td> ' . $game[$game['visitorID']]['score'] . ' </td>' . "\n";
		}
	}
	echo '</tr>' . "\n";
	echo '<tr>' . "\n";
	echo '<td>Home</td>' . "\n";
	foreach($games as $game) {
		if ($game['homeID'] == $game['winnerID'] && (int)$game['final'] == 1) {
			echo '<td><span class="winner">' . $game['homeID'] . "&nbsp;-&nbsp;" . $game[$game['homeID']]['score'] . '</span></td>' . "\n";
			// echo '<td><span class="winner">' . $game[$game['homeID']]['score'] . '</span></td>' . "\n";
		} else {
			echo '<td>' . $game['homeID'] . "&nbsp;-&nbsp;" . $game[$game['homeID']]['score'] . '</td>' . "\n";
			// echo '<td> ' . $game[$game['homeID']]['score'] . ' </td>' . "\n";
		}
	}
	echo '</tr>' . "\n";
?>
	</tbody>
</table>
</div>
<!-- dtjr - end scores -->
<div class="table-responsive">
<table class="table table-striped">
	<thead>
		<tr><th align="left">Player</th><th colspan="<?php echo sizeof($games); ?>">Week <?php echo $week; ?></th><th align="left">Score</th></tr>
	</thead>
	<tbody>
<?php
	$i = 0;
	arsort($playerTotals);
	foreach($playerTotals as $userID => $totalCorrect) {
		$hidePicks = hidePicks($userID, $week);
		if ($i == 0) {
			$topScore = $totalCorrect;
			$winners[] = $userID;
		} else if ($totalCorrect == $topScore) {
			$winners[] = $userID;
		}
		$tmpUser = $login->get_user_by_id($userID);
		$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
		//echo '	<tr' . $rowclass . '>' . "\n";
		echo '	<tr>' . "\n";
		switch (USER_NAMES_DISPLAY) {
			case 1:
				echo '		<td>' . trim($tmpUser->firstname . ' ' . $tmpUser->lastname) . '</td>' . "\n";
				break;
			case 2:
				echo '		<td>' . trim($tmpUser->userName) . '</td>' . "\n";
				break;
			default: //3
				echo '		<td>' . $tmpUser->firstname . '</td>' . "\n";
				break;
		}
		//loop through all games
		foreach($games as $game) {
			$pick = '';
			$pick = $playerPicks[$userID][$game['gameID']];
			// dtjr
			$score = $game[$pick]['score'] ;
			// dtjr
			if (!empty($game['winnerID'])) {
				//score has been entered
				if ($playerPicks[$userID][$game['gameID']] == $game['winnerID']) {
					$pick = '<span class="winner">' . $pick . '</span>';
				}
			} else {
				//mask pick if pick and week is not locked and user has opted to hide their picks
				$gameIsLocked = gameIsLocked($game['gameID']);
				if (!$gameIsLocked && !$weekExpired && $hidePicks && (int)$userID !== (int)$user->userID && (int)$user->userID <> 1) {
					$pick = '***';
				}
			}
			echo '		<td class="pickTD">' . $pick . '</td>' . "\n";
		}
		echo '		<td nowrap><b>' . $totalCorrect . '/' . sizeof($games) . ' (' . number_format(($totalCorrect / sizeof($games)) * 100, 2) . '%)</b></td>' . "\n";
		echo '	</tr>' . "\n";
		$i++;
	}

	
?>
	</tbody>
</table>
</div>
<?php
	//display list of absent players
	$sql = "select * from " . DB_PREFIX . "users where `status` = 1 and userID not in(" . implode(',', array_keys($playerTotals)) . ") and userName <> 'admin'";
	$query = $mysqli->query($sql);
	if ($query->num_rows > 0) {
		$absentHtml = '<p><b>Absent Players:</b> ';
		$i = 0;
		while ($row = $query->fetch_assoc()) {
			if ($i > 0) $absentHtml .= ', ';
			switch (USER_NAMES_DISPLAY) {
				case 1:
					$absentHtml .= trim($row['firstname'] . ' ' . $row['lastname']);
					break;
				case 2:
					$absentHtml .= $row['userName'];
					break;
				default: //3
					$absentHtml .= $row['firstname'];
					break;
			}
			$i++;
		}
		echo $absentHtml;
	}
	$query->free;
}

// dtjr
include('includes/column_stats.php');
// dtjr

include('includes/comments.php');

include('includes/footer.php');
