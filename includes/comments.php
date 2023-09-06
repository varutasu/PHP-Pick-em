<?php
if (COMMENTS_SYSTEM == 'basic' && $_POST['action'] == 'Add Comment') {
	//if user has not submitted within the last 15 seconds
	$sql = "select * from " . DB_PREFIX . "comments where userID = " . $user->userID . " and subject = '" . $mysqli->real_escape_string($_POST['subject']) . "' and postDateTime > date_add(now(), INTERVAL -15 SECOND)";
	$query = $mysqli->query($sql) or die($mysqli->error);
	if ($query->num_rows == 0) {
		$sql = "insert into " . DB_PREFIX . "comments (userID, subject, comment, postDateTime) values (" . $user->userID . ", '" . $mysqli->real_escape_string($_POST['subject']) . "', '" . $mysqli->real_escape_string($_POST['comment']) . "', now());";
		$mysqli->query($sql) or die($mysqli->error);
	}
	$query->free;
}
?>
<hr />
<div class="col-md-12">
		<h2>Comments/Taunts</h2>
		<?php
if (COMMENTS_SYSTEM == 'basic') {
?>
	<form id="addcomment" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<textarea class="form-control custom-control" name="comment" style="resize:none" placeholder="Add a comment..."></textarea>
			<input class="input-group-addon btn btn-secondary" type="submit" name="action" value="Add Comment" />
	</form>
</div>
<div class="back2top"><a href="<?php echo $_SERVER['REQUEST_URI']; ?>#">Back to top</a></div>
<div class="col-md-12" id="comments">
<?php
	$sql = "select * from " . DB_PREFIX . "comments order by postDateTime desc limit 12";
	$query = $mysqli->query($sql);
	while ($row = $query->fetch_assoc()) {
		$postUser = $login->get_user_by_id($row['userID']);
		switch (USER_NAMES_DISPLAY) {
			case 1:
				echo '<div><span><span class="comment-name">' . trim($postUser->firstname . ' ' . $postUser->lastname) . '</span> on ' . date('n/j @ g:i a', strtotime($row['postDateTime'])) . '</span></div>';
				break;
			case 2:
				echo '<div><span><span class="comment-name">' . $postUser->firstname . '</span> on ' . date('n/j @ g:i a', strtotime($row['postDateTime'])) . '</span></div>';
				break;
			default: //3
				echo '<div><span><span class="comment-name">' . $postUser->firstname . '</span> on ' . date('n/j @ g:i a', strtotime($row['postDateTime'])) . '</span></div>';
				break;
		}
		echo '<p class="written-comment">' . nl2br(trim($row['comment'])) . '</p>' . "\n";
	}
	$query->free;
} else if (COMMENTS_SYSTEM == 'disqus') {
?>

 <div id="disqus_thread"></div>
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = '<?php echo DISQUS_SHORTNAME; ?>'; // required: replace example with your forum shortname

        /* * * DON'T EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>
    <noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>
    <a href="http://disqus.com" class="dsq-brlink">comments powered by <span class="logo-disqus">Disqus</span></a>
<?php
}
?>
</div>