<?php
require_once('includes/application_top.php');
require('includes/classes/team.php');

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'Submit') {
    $cutoffDateTime = getCutoffDateTime($week, $year);

    // Sanitize user input and use prepared statements for database queries
    $stmt = $mysqli->prepare("DELETE FROM " . DB_PREFIX . "picksummary WHERE yearNum = ? AND weekNum = ? AND userID = ?");
    $stmt->bind_param("iii", $year, $week, $user->userID);
    $stmt->execute();
    $stmt->close();

    $showPicks = isset($_POST['showPicks']) ? 1 : 0;
    $stmt = $mysqli->prepare("INSERT INTO " . DB_PREFIX . "picksummary (yearNum, weekNum, userID, showPicks) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $year, $week, $user->userID, $showPicks);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT * FROM " . DB_PREFIX . "schedule WHERE yearNum = ? AND weekNum = ? AND (DATE_ADD(NOW(), INTERVAL ? HOUR) < gameTimeEastern AND DATE_ADD(NOW(), INTERVAL ? HOUR) < ?)");
    $stmt->bind_param("iiiss", $year, $week, SERVER_TIMEZONE_OFFSET, SERVER_TIMEZONE_OFFSET, $cutoffDateTime);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $gameID = (int)$row['gameID'];
            $userPick = isset($_POST['game' . $gameID]) ? (int)sanitizeInput($_POST['game' . $gameID]) : 0;

            // Use prepared statements for insert and delete queries
            $stmt = $mysqli->prepare("DELETE FROM " . DB_PREFIX . "picks WHERE userID = ? AND gameID = ?");
            $stmt->bind_param("ii", $user->userID, $gameID);
            $stmt->execute();

            if ($userPick !== 0) {
                $stmt = $mysqli->prepare("INSERT INTO " . DB_PREFIX . "picks (userID, gameID, pickID) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $user->userID, $gameID, $userPick);
                $stmt->execute();
            }
        }
    }
    $result->free_result();

} else {
    // Redirect to the current page with default year and week if needed
    if (empty($_GET['year']) || empty($_GET['week'])) {
        $currentScript = $_SERVER['PHP_SELF']; // Get the current script name
        header('Location: ' . $currentScript . '?year=' . $year . '&week=' . $week);
        exit;
    }

    $cutoffDateTime = getCutoffDateTime($week);
    $firstGameTime = getFirstGameTime($week);
}

include('includes/header.php');
?>

<script type="text/javascript">
    function checkform() {
        //make sure all picks have a checked value
        var f = document.entryForm;
        var allChecked = true;
        var allR = document.getElementsByTagName('input');
        for (var i=0; i < allR.length; i++) {
            if(allR[i].type == 'radio') {
                if (!radioIsChecked(allR[i].name)) {
                    allChecked = false;
                }
            }
        }
        if (!allChecked) {
            return confirm('One or more picks are missing for the current week.  Do you wish to submit anyway?');
        }
        return true;
    }
    function radioIsChecked(elmName) {
        var elements = document.getElementsByName(elmName);
        for (var i = 0; i < elements.length; i++) {
            if (elements[i].checked) {
                return true;
            }
        }
        return false;
    }
    function checkRadios() {
      $('input[type=radio]').each(function(){
       //alert($(this).attr('checked'));
        var targetLabel = $('label[for="'+$(this).attr('id')+'"]');
        console.log($(this).attr('id')+': '+$(this).is(':checked'));
        if ($(this).is(':checked')) {
          //console.log(targetLabel);
         targetLabel.addClass('highlight');
        } else {
          targetLabel.removeClass('highlight');
        }
      });
    }
    $(function() {
        checkRadios();
        $('input[type=radio]').click(function(){
          checkRadios();
        });
        $('label').click(function(){
          checkRadios();
        });
    });
</script>

<div class="row flex">
    <div id="page-title" class="col-xs-8 left">
        <i class="bi bi-calendar-check-fill"></i><h1 class="display-1 title">Picks</h1>
    </div>
    <div class="col-xs-4 right">
        <?php
        // Display year nav
        $sql = "SELECT DISTINCT yearNum FROM " . DB_PREFIX . "schedule ORDER BY yearNum;";
        if ($query = $mysqli->query($sql)) {
            $currentScript = $_SERVER['PHP_SELF']; // Get the current script name
            $yearNav = '<div class="btn-group"><button type="button" class="btn btn-outline dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $year . ' <span class="caret"></span></button>';
            $yearNav .= '    <ul class="dropdown-menu"> ';
            $i = 0;
            while ($row = $query->fetch_assoc()) {
                $yearNum = (int) $row['yearNum']; // Assuming year is an integer
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

<div class="row">
    <div id="content" class="col-md-12">
        <h2>Week <?php echo $week; ?> - Make Your Picks:</h2>
        <p>Please make your picks below for each game.</p>
        <?php
        // Get existing picks
		$picks = getUserPicks($year, $week, $user->userID);

        // Get show picks status
		$sql = "SELECT * FROM " . DB_PREFIX . "picksummary WHERE yearNum = " . $year . " AND weekNum = " . $week . " AND userID = " . $user->userID . ";";
        $query = $mysqli->query($sql);
        if ($query->num_rows > 0) {
            $row = $query->fetch_assoc();
            $showPicks = (int)$row['showPicks'];
        } else {
            $showPicks = 1;
        }
        $query->free;

        // Display schedule for the week
        $sql = "SELECT s.*, (DATE_ADD(NOW(), INTERVAL " . SERVER_TIMEZONE_OFFSET . " HOUR) > gameTimeEastern OR DATE_ADD(NOW(), INTERVAL " . SERVER_TIMEZONE_OFFSET . " HOUR) > '" . $cutoffDateTime . "')  AS expired ";
        $sql .= "FROM " . DB_PREFIX . "schedule s ";
        $sql .= "INNER JOIN " . DB_PREFIX . "teams ht ON s.homeID = ht.teamID ";
        $sql .= "INNER JOIN " . DB_PREFIX . "teams vt ON s.visitorID = vt.teamID ";
        $sql .= "WHERE s.yearNum = " . $year . " ";
        $sql .= "AND s.weekNum = " . $week . " ";
        $sql .= "ORDER BY s.gameTimeEastern, s.gameID";
            
        $query = $mysqli->query($sql) or die($mysqli->error);

        if ($query->num_rows > 0) {
            echo '<form name="entryForm" action="entry_form.php" method="post" onsubmit="return checkform();">' . "\n";
			echo '<input type="hidden" name="year" value="' . $year . '" />';
            echo '<input type="hidden" name="week" value="' . $week . '" />' . "\n";
            
            echo '    <div class="row">' . "\n";
            echo '        <div class="col-xs-12">' . "\n";
            $i = 0;
            
            while ($row = $query->fetch_assoc()) {
                $scoreEntered = false;
                $homeTeam = new team($row['homeID']);
                $visitorTeam = new team($row['visitorID']);
                $homeScore = (int)$row['homeScore'];
                $visitorScore = (int)$row['visitorScore'];
                $rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
                
                if (!empty($homeScore) || !empty($visitorScore)) {
                    // If the score is entered, show the score
                    $scoreEntered = true;
                    $homeScore = (int)$row['homeScore'];
                    $visitorScore = (int)$row['visitorScore'];
                    
                    if ($homeScore > $visitorScore) {
                        $winnerID = $row['homeID'];
                        $homestatus = true;
                        $homelabel = 'winner';
                        $visitorlabel = 'loser';
                    } else if ($visitorScore > $homeScore) {
                        $winnerID = $row['visitorID'];
                        $visitorstatus = true;
                        $visitorlabel = 'winner';
                        $homelabel = 'loser';
                    }
                } else {
                    $visitorlabel = 'default';
                    $homelabel = 'default';
                }
                
                if ($row['expired']) {
                    // Show locked pick
                    $pickID = getPickID($row['gameID'], $user->userID);
                    if ($scoreEntered) {
                        // Set the status of the pick (correct, incorrect)
                        if ($pickID == $winnerID) {
                            if ($pickID == $row['visitorID']) {
                                $homelabel = 'default';
                                $visitorlabel = 'correct';
                            }
                            if ($pickID == $row['homeID']) {
                                $homelabel = 'correct';
                                $visitorlabel = 'default';
                            }
                        }
                        if ($pickID != $winnerID) {
                            if ($pickID == $row['visitorID']) {
                                $homelabel = 'default';
                                $visitorlabel = 'incorrect';
                            }
                            if ($pickID == $row['homeID']) {
                                $homelabel = 'incorrect';
                                $visitorlabel = 'default';
                            }
                        }
                        if ($visitorScore == $homeScore) {
                            $homelabel = '';
                            $visitorlabel = '';
                        } else {
                            $pickLabel = '';
                        }
                    }
                }
                
                echo '                <div class="matchup">' . "\n";
                echo '                    <div class="row versus">' . "\n";
                echo '                        <div class="col-xs-4 ' . $visitorlabel . $pickLabel . '">' . "\n";
                echo '                            <label for="' . $row['gameID'] . $visitorTeam->teamID . '" class="label-for-check"><div class="team-logo"><img src="images/logos/' . $visitorTeam->teamID . '.svg" onclick="document.entryForm.game' . $row['gameID'] . '[0].checked=true;" /></div>' . "\n";
                echo '                            <div class="team-city">' . $visitorTeam->city . '</div>' . "\n";
                echo '                            <div class="team-name">' . $visitorTeam->team . '</div>' . "\n";
                echo '                            </label>' . "\n";
                echo '                        </div>' . "\n";
                echo '                        <div class="col-xs-1 team-separator">' . "\n";
                
                if (!empty($homeScore) || !empty($visitorScore)) {
                    //$winnerID will be null if it's a tie, which is ok
                    if ($scoreEntered) {
                        if ($row['final'] == 1) {
                            echo '                    <div class="score"><b>' . $row['visitorScore'] . ' - ' . $row['homeScore'] . '</b></div><div class="date">Final</div>' . "\n";
                        } else {
                            echo '                    <div class="score"><b>' . $row['visitorScore'] . ' - ' . $row['homeScore'] . '</b></div>' . "\n";
                        }
                    } else {
                        // Else show time of game
                        echo '                    <div class="at-sign">@</div>' . "\n";
                        echo '                    <div class="date">' . date('D n/j', strtotime($row['gameTimeEastern'])) . '</div>' . "\n";
                        echo '                    <div class="time">' . date('g:i A', strtotime($row['gameTimeEastern'])) . ' ET</div>' . "\n";
                    }
                }
                
                echo '                        </div>' . "\n";
                echo '                        <div class="col-xs-4 ' . $homelabel . $pickLabel . '">' . "\n";
                echo '                            <label for="' . $row['gameID'] . $homeTeam->teamID . '" class="label-for-check"><div class="team-logo"><img src="images/logos/' . $homeTeam->teamID . '.svg" onclick="document.entryForm.game' . $row['gameID'] . '[1].checked=true;" /></div>' . "\n";
                echo '                            <div class="team-city">' . $homeTeam->city . '</div>' . "\n";
                echo '                            <div class="team-name">' . $homeTeam->team . '</div>' . "\n";
                echo '                            </label>' . "\n";
                echo '                        </div>' . "\n";
                echo '                    </div>' . "\n";
                
                if (!$row['expired']) {
                    echo '                    <div class="row bg-row2">' . "\n";
                    echo '                        <div class="col-xs-4 center">' . "\n";
                    echo '                            <input type="radio" class="check-with-label" name="game' . $row['gameID'] . '" value="' . $visitorTeam->teamID . '" id="' . $row['gameID'] . $visitorTeam->teamID . '"' . (($picks[$row['gameID']]['pickID'] == $visitorTeam->teamID) ? ' checked' : '') . ' />' . "\n";
                    echo '                        </div>' . "\n";
                    echo '                        <div class="col-xs-2 time"></div>' . "\n";
                    echo '                        <div class="col-xs-4 center">' . "\n";
                    echo '                            <input type="radio" class="check-with-label" name="game' . $row['gameID'] . '" value="' . $homeTeam->teamID . '" id="' . $row['gameID'] . $homeTeam->teamID . '"' . (($picks[$row['gameID']]['pickID'] == $homeTeam->teamID) ? ' checked' : '') . ' />' . "\n";
                    echo '                        </div>' . "\n";
                    echo '                    </div>' . "\n";
                }
                
                echo '                </div>' . "\n";
                $i++;
            }
            echo '        </div>' . "\n";
            echo '    </div>' . "\n";
            
            if (ALWAYS_HIDE_PICKS) {
                echo '<p class="noprint"><input type="hidden" name="showPicks" id="showPicks" value="0"' . (($showPicks) ? ' checked="checked"' : '') . ' /> <label for="showPicks">' . "\n";
            } else {
                echo '<p class="noprint"><input type="checkbox" name="showPicks" id="showPicks" value="1"' . (($showPicks) ? ' checked="checked"' : '') . ' /> <label for="showPicks">Allow others to see my picks</label></p>' . "\n";
            }
            
            echo '<p class="noprint"><input class="fab" type="submit" name="action" value="Submit" /></p>' . "\n";
            echo '</form>' . "\n";
        }

        echo '    </div>' . "\n"; // end col
        echo '    </div>' . "\n"; // end entry-form row

        //echo '<div id="comments" class="row">';
        include('includes/comments.php');
        //echo '</div>';
        ?>