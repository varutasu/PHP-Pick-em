<?php
require('includes/application_top.php');

$weekStats = array();
$playerTotals = array();
$possibleScoreTotal = 0;
calculateStats();

include('includes/header.php');
?>
<div id="page-title" class="col-xs-12">
	<i class="bi bi-trophy-fill"></i>
	<h1 class="display-1 title">Leaderboard</h1>
</div>
<div class="row">
	<div class="col-md-8 col-xs-12">
		<div class="row">
			<div class="col-md-6 col-xs-12">
				<h5>Top Picker</h5>
				<div class="table-responsive">
					<table class="table leader">
						<?php							
						if (is_array($playerTotals) && sizeof($playerTotals) > 0) {
							$playerTotals = sort2d($playerTotals, 'score', 'desc');
							$i = 0;
							foreach($playerTotals as $playerID => $stats) {
								if ($tmpScore < $stats[score]) $tmpScore = $stats[score]; //set initial top score
								//if next lowest score is reached, increase counter
								if ($stats[score] < $tmpScore ) $i++;
								//if score is zero or counter is 1 or higher, break
								if ($stats[score] == 0 || $i > 1) break;
								$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
								$pickRatio = $stats[score] . '/' . $possibleScoreTotal;
								$pickPercentage = number_format((($stats[score] / $possibleScoreTotal) * 100), 2) . '%';
								switch (USER_NAMES_DISPLAY) {
									case 1:
										echo '	<tr' . $rowclass . '><td class="default">' . $stats[name] . '</td><td class="default" align="center">' . $stats[wins] . '</td><td class="default" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
										break;
									case 2:
										echo '	<tr' . $rowclass . '><td class="default">' . $stats[userName] . '</td><td class="default" align="center">' . $stats[wins] . '</td><td class="default" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
										break;
									default: //3
										echo '	<tr' . $rowclass . '><td class="default">' . $stats[name] . '</td><td class="default" align="center">' . $stats[wins] . '</td><td class="default" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
										break;
								}
								$i++;
							}
						} else {
							echo '	<tr><td colspan="3">No weeks have been completed yet.</td></tr>' . "\n";
						}
						?>
					</table>
				</div>
			</div>	
			<div class="col-md-6 col-xs-12">
				<h5>Current Leader</h5>
				<div class="table-responsive">
					<table class="table leader">
						<?php
						if (is_array($playerTotals) && sizeof($playerTotals) > 0) {
							arsort($playerTotals);
							$i = 0;
							foreach($playerTotals as $playerID => $stats) {
								if ($tmpScore < $stats[score]) $tmpScore = $stats[score]; //set initial top score
								//if next lowest score is reached, increase counter
								if ($stats[score] < $tmpScore ) $i++;
								//if score is zero or counter is 1 or higher, break
								if ($stats[score] == 0 || $i > 1) break;
								$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
								$pickRatio = $stats[score] . '/' . $possibleScoreTotal;
								$pickPercentage = number_format((($stats[score] / $possibleScoreTotal) * 100), 2) . '%';
								switch (USER_NAMES_DISPLAY) {
									case 1:
										echo '	<tr' . $rowclass . '><td class="default">' . $stats[name] . '</td><td class="default" align="center">' . $stats[wins] . '</td><td class="default" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
										break;
									case 2:
										echo '	<tr' . $rowclass . '><td class="default">' . $stats[userName] . '</td><td class="default" align="center">' . $stats[wins] . '</td><td class="default" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
										break;
									default: //3
										echo '	<tr' . $rowclass . '><td class="default">' . $stats[name] . '</td><td class="default" align="center">' . $stats[wins] . '</td><td class="default" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
										break;
								}
								$i++;
							}
						} else {
							echo '	<tr><td colspan="3">No weeks have been completed yet.</td></tr>' . "\n";
						}
						?>
					</table>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h5>Weekly Stats</h5>
				<div class="table-responsive">
					<table class="table table-striped">
						<tr><th align="left">Week</th><th align="left">Winner(s)</th><th>Score</th></tr>
						<?php
						if (isset($weekStats)) {
							$i = 0;
							foreach($weekStats as $week => $stats) {
								$winners = '';
								if (is_array($stats[winners])) {
									foreach($stats[winners] as $winner => $winnerID) {
										$tmpUser = $login->get_user_by_id($winnerID);
										switch (USER_NAMES_DISPLAY) {
											case 1:
												$winners .= ((strlen($winners) > 0) ? ', ' : '') . trim($tmpUser->firstname . ' ' . $tmpUser->lastname);
												break;
											case 2:
												$winners .= ((strlen($winners) > 0) ? ', ' : '') . $tmpUser->userName;
												break;
											default: //3
												$winners .= ((strlen($winners) > 0) ? ', ' : '') . $tmpUser->firstname;
												break;
										}
									}
								}
								$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
								echo '	<tr' . $rowclass . '><td>' . $week . '</td><td>' . $winners . '</td><td align="center">' . $stats[highestScore] . '/' . $stats[possibleScore] . '</td></tr>';
								$i++;
							}
						} else {
							echo '	<tr><td colspan="3">No weeks have been completed yet.</td></tr>' . "\n";
						}
						?>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
	
	<div class="col-md-4 col-xs-12">
		<h5>All Players</h5>
		<div class="table-responsive">
			<table class="table table-striped">
				<tr><th align="left">Player</th><th align="left">Wins</th><th>Pick Ratio</th></tr>
			<?php
			if (isset($playerTotals)) {
				$playerTotals = sort2d($playerTotals, 'score', 'desc');
				$i = 0;
				foreach($playerTotals as $playerID => $stats) {
					$rowclass = (($i % 2 == 0) ? ' class="altrow"' : '');
					$pickRatio = $stats[score] . '/' . $possibleScoreTotal;
					$pickPercentage = number_format((($stats[score] / $possibleScoreTotal) * 100), 2) . '%';
					switch (USER_NAMES_DISPLAY) {
						case 1:
							echo '	<tr' . $rowclass . '><td class="default">' . $stats[name] . '</td><td class="default" align="center">' . $stats[wins] . '</td><td class="default" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
						case 2:
							echo '	<tr' . $rowclass . '><td class="default">' . $stats[userName] . '</td><td class="default" align="center">' . $stats[wins] . '</td><td class="default" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
						default: //3
							echo '	<tr' . $rowclass . '><td class="default">' . $stats[name] . '</td><td class="default" align="center">' . $stats[wins] . '</td><td class="default" align="center">' . $pickRatio . ' (' . $pickPercentage . ')</td></tr>';
							break;
					}
					$i++;
				}
			} else {
				echo '	<tr><td colspan="3">No weeks have been completed yet.</td></tr>' . "\n";
			}
			?>
			</table>
		</div>
	</div>
</div>
</div>

<?php
include('includes/comments.php');

include('includes/footer.php');
?>
