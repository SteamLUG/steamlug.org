<?php
include_once('functions_db.php');

if ( !isset( $database ) )
	$database = connectDB( );

	function showPollSelector($elementID = 'pollSelect', $default = -1, $new = False, $limit = 20)
	{
		global $database;

		$query = "select date_format(expireDate, '%Y-%m-%d') as expireDate, title, id from poll order by title ";
		if (is_numeric($limit) && $limit > 0 )
		{
			$query = $query . "limit " . $limit;
		}

		$stmt = $database->prepare($query);
		$stmt->execute();
		$poll = array();
		if ($stmt)
		{
			echo "<div class=\"form-group\">\n
					<label for=\"" . $elementID . "\" class=\"col-lg-2 control-label\">Select Poll</label>\n
					<div class=\"col-lg-10\">
					\t<select class=\"form-control\" name=\"" . $elementID . "\" id=\"" . $elementID . "\">\n";
			if ($new)
			{
				echo "\t\t<option value=\"-1\">-- New --</option>\n";
			}
			$poll = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			foreach ($poll as $p)
			{
				echo "\t\t<option value=\"" . $p['id'] . "\" " . ($p['id'] == $default ? "selected = 'selected'" : "") .">" . $p['title'] . " (exp: " . $p['expireDate']. ")</option>\n";
			}
			echo "\t</select></div></div>\n";
		}
	}

	function showPastPolls($limit = -1)
	{
		global $database;
		$uid = ( array_key_exists( 'u', $_SESSION ) ? $_SESSION[ 'u' ] : False );

		$stmt = $database->prepare("select date_format(expireDate, '%Y-%m-%d') as expireDate, date_format(publishDate, '%Y-%m-%d') as publishDate, title, description, url, multipleChoice, poll.id as id, now() >= expireDate as expired, responseCount, hasVoted from poll left join (select count(uid) as responseCount, count(case when uid = :uid then 1 else NULL end) as hasVoted, pollID from poll_respondent group by pollID) as temp on pollID = poll.id where now() >= publishDate and now() >= expireDate");
		$stmt->execute(array('uid' => $uid));
		$poll = array();
		if ($stmt)
		{
			$poll = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();

			foreach ($poll as $p)
			{
				$p['uid'] = $uid;
				if (!is_numeric($p['responseCount']))
				{
					$p['responseCount'] = 0;
				}
				showPoll($p);
			}
		}
		else
		{
			echo "Oh noes ;_;";
			print_r($database->errorInfo());
		}
	}

	function showCurrentPolls( $limit = -1)
	{
		global $database;
		$uid = ( array_key_exists( 'u', $_SESSION ) ? $_SESSION[ 'u' ] : False );

		$stmt = $database->prepare("select date_format(expireDate, '%Y-%m-%d') as expireDate, date_format(publishDate, '%Y-%m-%d') as publishDate, title, description, url, multipleChoice, poll.id as id, now() >= expireDate as expired, responseCount, hasVoted from poll left join (select count(uid) as responseCount, count(case when uid = :uid then 1 else NULL end) as hasVoted, pollID from poll_respondent group by pollID) as temp on pollID = poll.id where now() >= publishDate and now() < expireDate");
		$stmt->execute(array('uid' => $uid));
		$poll = array();
		if ($stmt)
		{
			$poll = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();

			foreach ($poll as $p)
			{
				$p['uid'] = $uid;
				if (!is_numeric($p['responseCount']))
				{
					$p['responseCount'] = 0;
				}
				showPoll($p);
			}
		}
		else
		{
			echo "Oh noes ;_;";
			print_r($database->errorInfo());
		}
	}

	function showNextExpiringPoll()
	{
		global $database;
		$uid = ( array_key_exists( 'u', $_SESSION ) ? $_SESSION[ 'u' ] : False );

		$stmt = $database->prepare("select date_format(expireDate, '%Y-%m-%d') as expireDate, date_format(publishDate, '%Y-%m-%d') as publishDate, title, description, url, multipleChoice, poll.id as id, now() >= expireDate as expired, responseCount, hasVoted from poll left join (select count(uid) as responseCount, count(case when uid = :uid then 1 else NULL end) as hasVoted, pollID from poll_respondent group by pollID) as temp on pollID = poll.id where now() >= publishDate and now() >= expireDate order by expireDate asc limit 1");
		$stmt->execute(array('uid' => $uid));
		$poll = array();
		if ($stmt)
		{
			$poll = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();

			foreach ($poll as $p)
			{
				$p['uid'] = $uid;
				if (!is_numeric($p['responseCount']))
				{
					$p['responseCount'] = 0;
				}
				showPoll($p);
			}
		}
	}

	function showPoll($poll)
	{
		global $database;

		$canVote = False;

		if ($poll['expired'] == "0")
		{
			if (($poll['hasVoted'] == 0 || $poll['hasVoted'] == "0") && $poll['uid'] != "" && $_SESSION['g'])
			{
				$canVote = True;
			}
		}


		$stmt = $database->prepare("select id, name, description, url, responseCount, responseCount / :count * 100 as percentage from poll_option where pollID = :pollid");
		$stmt->execute(array( 'count' => $poll['responseCount'], 'pollid' => $poll['id']));

		$options = array();
		if ($stmt)
		{
			$options = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
		}
		else
		{
			echo "Oh noes ;_;";
			print_r($database->errorInfo());
		}

		//print_r($poll);
		//print_r($options);
		echo "<article class=\"panel panel-primary\">
				<div class=\"panel-heading\">";
		echo "\t<h3 class=\"panel-title\" data-poll-id=\"" . $poll['id'] . "\">" . htmlspecialchars($poll['title'], ENT_QUOTES) . "</h3>";
		echo "</div>
		<div class=\"panel-body\">";
		if ($canVote)
		{
			echo "<form method=\"post\" action=\"poll_vote.php\">\n";
		}
		echo "<p class=\"text-right\">" . $poll['publishDate'] . " to " . $poll['expireDate'] . "</p>\n";
		echo "\t<p>" . htmlspecialchars($poll['description'], ENT_QUOTES) . "</p>\n";
		if ($poll['url'] != "")
		{
			echo "\t<p><a href = '" . $poll['url'] . "'>Read more</a></p>\n";
		}

		$inputType = 'radio';
		if ($poll['multipleChoice'] != 0)
		{
			$inputType = 'checkbox';
		}
		foreach ($options as $o)
		{
			if ($canVote)
			{
				echo "\t\t\t<input type=\"" . $inputType . "\" name=\"poll_selection[]\" value=\"" . $o['id'] . "\" id=\"option_" . $o['id'] . "\" />\n";
				echo "\t\t\t<label class = 'pollOptionLabel' title = '" . ($o['description'] != "" ? htmlspecialchars($o['description'], ENT_QUOTES) : htmlspecialchars($o['name'], ENT_QUOTES)) . "' for = 'option_" . $o['id'] . "'><span></span>" . htmlspecialchars($o['name'], ENT_QUOTES) . ($o['url'] != "" ? " <a href = '" . $o['url'] . "'>[link]</a>" : "" ) .  " (" . round($o['percentage'], 2) . "%)</label>\n";
			}
			else
			{
				echo "\t\t\t<p title = '" . ($o['description'] != "" ? htmlspecialchars($o['description'], ENT_QUOTES) : htmlspecialchars($o['name'], ENT_QUOTES)) . "'>" . htmlspecialchars($o['name'], ENT_QUOTES) . ($o['url'] != "" ? " <a href = '" . $o['url'] . "'>[link]</a>" : "" ) . " (" . round($o['percentage'], 2) . "%)</p>\n";
			}
			echo "\t\t\t<div class=\"progress\">
						<div class=\"progress-bar progress-bar-info\" title = '" . ($o['description'] != "" ? htmlspecialchars($o['description'], ENT_QUOTES) : htmlspecialchars($o['name'], ENT_QUOTES)) . "' style = 'width: " . (is_numeric($o['percentage']) ? $o['percentage'] : 0 ) . "%;'></div>\n";
			echo "\t\t\t</div>\n";
		}

		if ($poll['uid'])
		{
			echo "\t<p class = 'pollVoteCount'>" . $poll['responseCount'] . " vote" . ($poll['responseCount'] != 1 ? "s" : "" );
			if  ($poll['hasVoted'] > 0)
			{
				echo " (including yours!)";
			}
			echo "</p>\n";
		}
		else
		{
			echo "\t<p class = 'pollVoteCount'>" . $poll['responseCount'] . " vote" . ($poll['responseCount'] != 1 ? "s" : "" ) . "</p>\n";
			echo "\t<p>You must be logged in to vote.</p>\n";
		}

		if ($canVote)
		{
			echo "\t<input type=\"hidden\" name=\"poll\" value=\"" . $poll['id'] . "\" />\n";
			echo "\t<input type=\"hidden\" name=\"page\" value=\"" . $_SERVER['SCRIPT_NAME'] . "\" />\n";
			echo "\t<input class=\"btn btn-info\" type=\"submit\" value=\"Vote\" />\n";
			echo "</form>\n";
		}
		echo "\t</div></article>\n";
	}


	function deletePoll($id)
	{
		if (in_array($_SESSION['u'], getAdmins()))
		{
			global $database;
			$uid = ( array_key_exists( 'u', $_SESSION ) ? $_SESSION[ 'u' ] : False );
			$error = "";

			if (is_numeric($id))
			{
				echo "<p>";
				$stmt = $database->prepare("delete from poll_option where pollID = :pollID");
				$stmt->execute(array('pollID' => $id));
				echo "Deleted " . $stmt->rowCount() . " poll options with pollid " . $id . "<br />\n";
				$stmt = $database->prepare("delete from poll_respondent where pollID = :pollID");
				$stmt->execute(array('pollID' => $id));
				echo "Deleted " . $stmt->rowCount() . " poll responses with pollid " . $id . "<br />\n";
				$stmt = $database->prepare("delete from poll where id = :pollID");
				$stmt->execute(array('pollID' => $id));
				echo "Deleted " . $stmt->rowCount() . " polls with id " . $id . ".\n";
				echo "</p>";
			}
			else
			{
				$error = "bad_poll";
			}

			if ($error != "")
			{
				echo "ERRORS " . $error;
			}
		}
	}


	function savePoll()
	{

		if (in_array($_SESSION['u'], getAdmins()))
		{
			global $database;
			$uid = ( array_key_exists( 'u', $_SESSION ) ? $_SESSION[ 'u' ] : False );
			$error = "";
			echo "<!--";
			if (isset($_POST['poll_title']) && isset($_POST['poll_description']) && isset($_POST['poll_type']) && isset($_POST['poll_publishDate']) && isset($_POST['poll_expireDate']) && isset($_POST['poll_url']))
			{
				if (isset($_POST['poll_id']))
				{
					if (is_numeric($_POST['poll_id']))
					{
						echo "UPDATING!!!";
						$pollID = $_POST['poll_id'];
						$stmt = $database->prepare("update poll set title = :title, description = :description, url = :url, type = :type, multipleChoice = :multipleChoice, publishDate = :publishDate, expireDate = :expireDate where id = :pollID");
						$stmt->execute(array(
							'title' => $_POST['poll_title'],
							'description' => $_POST['poll_description'],
							'url' => $_POST['poll_url'],
							'type' => $_POST['poll_type'],
							'multipleChoice' => ( isset($_POST['poll_multipleChoice']) ? True : False ),
							'publishDate' => $_POST['poll_publishDate'],
							'expireDate' => $_POST['poll_expireDate'],
							'pollID' => $pollID
							));
					}
					else
					{

						$error = 'bad_poll';
					}
				}
				else
				{
					echo "INSERTING!!!";
					$stmt = $database->prepare("insert into poll (title, description, url, type, multipleChoice, publishDate, expireDate) values (:title, :description, :url, :type, :multipleChoice, :publishDate, :expireDate)");
					$stmt->execute(array(
							'title' => $_POST['poll_title'],
							'description' => $_POST['poll_description'],
							'url' => $_POST['poll_url'],
							'type' => $_POST['poll_type'],
							'multipleChoice' => ( isset($_POST['poll_multipleChoice']) ? True : False ),
							'publishDate' => $_POST['poll_publishDate'],
							'expireDate' => $_POST['poll_expireDate']
							));
					$pollID = $database->lastInsertId();
					if (!isset($_GET['poll']))
					{
						$_GET['poll'] = $pollID;
					}
				}


				if ($error != "")
				{
					echo "ERRORS " . $error;
					$error = "";
				}

				//so
				if (isset($_POST['option_name']) && isset($_POST['option_description']) && isset($_POST['option_pollID']) && isset($_POST['option_id']) && isset($_POST['option_delete']) && isset($_POST['option_url']))
				{
					//much
					if (is_array($_POST['option_name']) && is_array($_POST['option_description']) && is_array($_POST['option_pollID']) && is_array($_POST['option_id']) && is_array($_POST['option_delete']) && is_array($_POST['option_url']))
					{
						//sanity
						foreach ($_POST['option_id'] as $i => $id)
						{
							//checking
							if (is_numeric($id) && ($id >= 0))
							{
								if (isset($_POST['option_delete'][$i]))
								{
									//delete
									$stmt = $database->prepare("delete from poll_option where id = :optionID");
									$stmt->execute(array(
											'optionID' => $id
											));
									$stmt->closeCursor();
								}
								else
								{
									//update
									$stmt = $database->prepare("update poll_option set name = :name, description = :description, url = :url, pollID = :pollID where id = :optionID");
									$stmt->execute(array(
											'name' => $_POST['option_name'][$i],
											'description' => $_POST['option_description'][$i],
											'url' => $_POST['option_url'][$i],
											'pollID' => (is_numeric($_POST['option_pollID'][$i]) ? $_POST['option_pollID'][$i] : $pollID),
											'optionID' => $id
											));
									$stmt->closeCursor();
								}
							}
							else if ($_POST['option_name'][$i] != "")
							{
								//insert
								$stmt = $database->prepare("insert into poll_option (name, description, url, pollID, responseCount) values (:name, :description, :url, :pollID, 0)");
								$stmt->execute(array(
										'name' => $_POST['option_name'][$i],
										'description' => $_POST['option_description'][$i],
										'url' => $_POST['option_url'][$i],
										'pollID' => (is_numeric($_POST['option_pollID'][$i]) ? $_POST['option_pollID'][$i] : $pollID)
										));
								$stmt->closeCursor();
							}
						}
					}
					else
					{
						$error = 'bad_options';
					}
				}
				else
				{
					$error = 'bad_options';
				}

			}
			else
			{
				//$error = 'bad_post';
			}

			if ($error != "")
			{
				echo "ERRORS " . $error;
			}

			echo "-->\n";
		}
	}

	function showPollAdmin()
	{
		if (in_array($_SESSION['u'], getAdmins()))
		{
			global $database;
			$uid = ( array_key_exists( 'u', $_SESSION ) ? $_SESSION[ 'u' ] : False );

			$poll = array();
			if (isset($_GET['poll']))
			{
				if (is_numeric($_GET['poll']))
				{
					$stmt = $database->prepare("select date_format(expireDate, '%Y-%m-%d') as expireDate, date_format(publishDate, '%Y-%m-%d') as publishDate, title, description, url, type, multipleChoice, id from poll where id = :pollid");
					$stmt->execute(array( 'pollid' => $_GET['poll'] ));
					$poll = $stmt->fetchAll(PDO::FETCH_ASSOC);
					//echo $_GET['poll'] . ": ".  print_r($poll);
					if (count($poll) > 0)
					{
						$poll = $poll[0];
					}
					else
					{
						//We're trying to edit a post that doesn't exist, but lets just let the player make a new one.
						unset($_GET['poll']);
						echo "<p class=\"text-warning\">Warning: The specified poll does not exist.</p>";
					}

					$stmt->closeCursor();
				}
				else
				{
					//We're trying to edit a post that doesn't exist, but lets just let the player make a new one.
					unset($_GET['poll']);
					echo "<p class=\"text-warning\">Warning: Invalid poll id.</p>";
				}
			}
		?>
			</div>
		</article>
		<article class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo (count($poll) > 0 ? "Poll Details" : "New Poll")?></h3>
			</div>
			<div class="panel-body">
			<form class="form-horizontal" method = 'post' action = 'poll-admin.php<?php echo (isset($_GET['poll']) ? "?poll=" . $_GET['poll'] : "")?>'>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="poll_title">Title</label>
				<div class="col-lg-10">
					<input class="form-control" id="poll_title" name="poll_title" type="text" placeholder="The title of the poll" value='<?php echo (isset($poll['title']) ? $poll['title'] : "") ?>'/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="poll_description">Description</label>
				<div class="col-lg-10">
					<textarea class="form-control" rows="3" id="poll_description" name="poll_description" placeholder="Poll description"><?php echo (isset($poll['description']) ? $poll['description'] : "") ?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="poll_url">URL</label>
				<div class="col-lg-10">
					<input class="form-control" type="text" id="poll_url" name="poll_url" placeholder="Optional supporting URL for the poll." value = "<?php echo (isset($poll['url']) ? $poll['url'] : "") ?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="poll_type">Type</label>
				<div class="col-lg-10">
					<select class="form-control" id="poll_type" name="poll_type">
						<option value="decision" <?php echo (isset($poll['type']) and $poll['type'] == 'decision' ? "selected = 'selected'" : "") ?>>Decision</option>
						<option value="event" <?php echo (isset($poll['type']) and $poll['type'] == 'event' ? "selected = 'selected'" : "") ?>>Event</option>
						<option value="research" <?php echo (isset($poll['type']) and $poll['type'] == 'research' ? "selected = 'selected'" : "") ?>>Research</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="poll_multipleChoice">Multiple Choice</label>
				<div class="col-lg-10">
					<input id="poll_multipleChoice" name="poll_multipleChoice" type="checkbox" <?php if (isset($poll['multipleChoice'])) { echo ($poll['multipleChoice'] != 0 ? "checked = 'checked'" : ""); } ?>/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="poll_publishDate">Publish Date</label>
				<div class="col-lg-10">
					<input class="form-control" id="poll_publishDate" name="poll_publishDate" type="text" placeholder="2013-11-03" value = '<?php echo (isset($poll['publishDate']) ? $poll['publishDate']: "") ?>'/>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="poll_expireDate">Expire Date</label>
				<div class="col-lg-10">
					<input class="form-control" id="poll_expireDate" name="poll_expireDate" type="text" placeholder="2013-11-29" value = '<?php echo (isset($poll['expireDate']) ? $poll['expireDate'] : "") ?>'/>
				</div>
			</div>
				<?php echo (isset($poll['id']) ? "<input type=\"hidden\" name=\"poll_id\" id=\"poll_id\" value=\"" . $poll['id'] . "\" />": "") ?>

			<h3>Poll Options</h3>
			<?php
			$stmt = $database->prepare("select id, name, description, url from poll_option where pollID = :pollid");
			$stmt->execute(array( 'pollid' => (isset($poll['id']) ? $poll['id'] : "") ));
			$options = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt->closeCursor();

			for ($i = 0; $i <= 8; $i++)
			{
				$o = array();
				if ($i < count($options))
				{
					$o = $options[$i];
				}
			?>
			<hr />
			<div class="form-group">
				<label class="col-lg-2 control-label" for="option_name_<?php echo $i; ?>">Option Name</label>
				<div class="col-lg-10">
					<input class="form-control" id="option_name_<?php echo $i; ?>" name="option_name[<?php echo $i; ?>]" type="text" placeholder="A name for this option" value="<?php echo (isset($o['name']) ? $o['name'] : "") ?>" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="option_description_<?php echo $i; ?>">Option Description</label>
				<div class="col-lg-10">
					<textarea class="form-control" rows="3" id="option_description_<?php echo $i; ?>" name="option_description[<?php echo $i; ?>]" placeholder="A brief description for this option."><?php echo (isset($o['description']) ? $o['description'] : "") ?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="option_url_<?php echo $i; ?>">Option URL</label>
				<div class="col-lg-10">
					<input class="form-control" type="text" id="option_url_<?php echo $i; ?>" name="option_url[<?php echo $i; ?>]" placeholder="An optional supporting URL." value="<?php echo (isset($o['url']) ? $o['url'] : "") ?>" />
				</div>
			</div>
			<input type="hidden" id="option_pollID_<?php echo $i; ?>" name="option_pollID[<?php echo $i; ?>]" value="<?php echo (isset($poll['id']) ? $poll['id']: "") ?>"/>
			<div class="form-group">
				<label class="col-lg-2 control-label" for="option_delete_<?php echo $i; ?>">Delete Option</label>
				<div class="col-lg-10">
					<input type="checkbox" id="option_delete<?php echo $i; ?>" name="option_delete[<?php echo $i; ?>]" />
				</div>
			</div>
			<?php
				echo "<input type=\"hidden\" name=\"option_id[" . $i . "]\" id=\"option_id[" . $i . "]\" value=\"" . (isset($o['id']) ? $o['id'] : -1) . "\" />";
			}
			?>
			<!-- this is needed so that we can be confident that the array exists even if nothing is being deleted -->
			<input type="hidden" name="option_delete[-1]" id="delete_option_placeholder" />
			<input class="btn btn-success" type="submit" value="Save" />
			</form>
		<?php
		}
		else
		{
			echo "<p class=\"text-error\">You don't belong here.</p>";
		}
	}
