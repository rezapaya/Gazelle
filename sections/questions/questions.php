<?
if (!check_perms("users_mod")) {
	error(404);
}
include(SERVER_ROOT.'/classes/text.class.php');
$Text = new TEXT(true);

$DB->query("
		SELECT
			uq.ID,
			uq.Question,
			uq.UserID,
			uq.Date,
			(
				SELECT COUNT(1)
				FROM staff_answers
				WHERE QuestionID = uq.ID
			) AS Responses
		FROM user_questions AS uq
		WHERE uq.ID NOT IN
				(
					SELECT siq.QuestionID
					FROM staff_ignored_questions AS siq
					WHERE siq.UserID = '$LoggedUser[ID]'
				)
			AND uq.ID NOT IN
				(
					SELECT sq.QuestionID
					FROM staff_answers AS sq
					WHERE sq.UserID = '$LoggedUser[ID]'
				)
		ORDER BY uq.Date DESC");
$Questions = $DB->to_array();

$DB->query("
	SELECT COUNT(1)
	FROM user_questions");
list($TotalQuestions) = $DB->next_record();

View::show_header("Ask the Staff", "questions");

?>
<div class="thin">
	<div class="header">
		<h2>
			User Questions
			<span style="float: right;">
				<?=$TotalQuestions?> questions asked, <?=count($Questions)?> left to answer
			</span>
		</h2>
	</div>
	<div class="linkbox">
		<a class="brackets" href="questions.php?action=answers">View staff answers</a>
		<a class="brackets" href="questions.php?action=popular_questions">Popular questions</a>
	</div>
<?	foreach($Questions as $Question) { ?>
	<div id="question<?=$Question['ID']?>" class="box box2">
		<div class="head">
			<span>
				<a class="post_id" href="questions.php#question<?=$Question['ID']?>">#<?=$Question['ID']?></a>
				<?=Users::format_username($Question['UserID'])?> - <?=time_diff($Question['Date'])?>
			</span>
			<span style="float: right;">
<?				if ($Question['Responses'] > 0) { ?>
					<a href="#" id="<?=$Question['ID']?>" class="view_responses brackets"><?=$Question['Responses'] == 1 ? ("View " . $Question['Responses'] . " response") : ("View " . $Question['Responses'] . " responses")?></a>
					-
<?				} ?>
				<form class="hidden" id="delete_<?=$Question['ID']?>" method="post" action="">
					<input type="hidden" name="action" value="take_remove_question" />
					<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
					<input type="hidden" name="question_id" value="<?=$Question['ID']?>" />
				</form>
				<a href="#" onclick="if (confirm('Are you sure?') == true) { $('#delete_<?=$Question['ID']?>').raw().submit(); } return false;" class="brackets">Delete</a>
				-
				<form class="hidden" id="ignore_<?=$Question['ID']?>" method="post" action="">
					<input type="hidden" name="action" value="take_ignore_question" />
					<input type="hidden" name="auth" value="<?=$LoggedUser['AuthKey']?>" />
					<input type="hidden" name="question_id" value="<?=$Question['ID']?>" />
				</form>
				<a href="#" onclick="if (confirm('Are you sure?') == true) { $('#ignore_<?=$Question['ID']?>').raw().submit(); } return false;" class="brackets">Ignore</a>
				-
				<a href="#" id="<?=$Question['ID']?>" class="answer_link brackets">Answer</a>
			</span>
		</div>
		<div class="pad">
			<?=$Text->full_format($Question['Question'])?>
		</div>
	</div>
	<div id="answer<?=$Question['ID']?>" class="hidden center box pad">
		<? new TEXTAREA_PREVIEW("replybox_" . $Question['ID'], "replybox_" . $Question['ID'], '', 40, 8); ?>
		<input type="submit" class="submit submit_button" id="<?=$Question['ID']?>" value="Answer" />
	</div>
<?	} ?>
</div>
<?
View::show_footer();
