<?php
	/**
	* @author Ryan Rule-Hoffman
	**/
	$release = 5; // Build number
	
	// Start out session.
	session_start();
	
	require_once('recaptchalib.php');
	$recatpchaPublicKey = "6LewcucSAAAAAJOxCV4h_KDIqc019Zb77C2BEVPp";
	$recatpchaPrivateKey = "6LewcucSAAAAAJ64W2-qJbBuFeN3bK_j3HL0BE3Y";
	
	// Do any pre-header stuff up here.
	$action = "create";
	if (isset($_REQUEST['action']))
	{
		$action = $_REQUEST['action'];
	}
	
	// Set the title of the page.
	$title = "RIT Honors Pizza Poems";
	
	/**
	* Get a connection to the database.
	* @return The connection to the database.
	**/
	require_once('SiteConfigVars.php');
	function getDatabase()
	{
		$pdo = new PDO('mysql:host='.getConfigValue('dbHost_w_hon').';dbname=w_hon;charset=utf8', 'w-hon', getConfigValue('dbPass_w_hon'));
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
		return $pdo;
	}
	
	/**
	* Print a database error message.
	* @param e The exception.
	**/
	function printDatabaseError($e)
	{
		echo "Sorry, there was an error connecting to the database.<br /> Please try again later!<br />";
	}
	
	/**
	* Get a poem Id from the request. (Usually a GET variable named id)
	* @return The ID, or -1 if no valid ID was found.
	*/
	function getIdFromRequest()
	{
		// Make sure a poem ID is set
		if (isset($_REQUEST['id']))
		{
			$id = $_REQUEST['id'];
			// And valid... This is RIT, don't want people screwing with anything
			if (filter_var($id, FILTER_VALIDATE_INT))
			{
				return $id;
			}
		}
		return -1;
	}
	
	/**
	* Check if the current user has the specified permission.
	* @param permission The permission name.
	* @return If the user has the permission.
	*/
	function hasPermission($permission)
	{
		// TODO: Some kind of authentication.
		return $_SESSION['loggedIn'];
	}
	
	// Parse any actions that need to be handled before page output.
	if ($action == "login")
	{
		$_SESSION['loggedIn'] = 1;
	}
	else if ($action == "logout")
	{
		$_SESSION['loggedIn'] = 0;
	}
	
?>
<!DOCTYPE HTML>

<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title><?php echo $title; ?></title>
		<link rel="stylesheet" href="css/poems.css" />
	</head>
	
	<body>
		<div class="main">
			<h1>Honors Pizza Poem Submission</h1>
			<div id="navbar">
				<ul>
			
					<li><a href="?action=create">Submit Poems</a></li>
				
					<li><a href="?action=view">View Poems</a></li>
				
				</ul>
			</div>
			<br class="clear" />
			<div class="auth">
				<?php
					if ($_SESSION['loggedIn'])
					{
						echo '<a href="?action=logout">Logout</a>';
					}
					else
					{
						echo '<a href="?action=login">Login</a>';
					}
				?>
			</div>
			<br class="clear" />
			<div class="editor">
			<?php
				// Check what our current action is a display the relevent page.
				if ($action == "submit")
				{
					// Grab all of the submitted information.
					$title = $_REQUEST['title'];
					$author = $_REQUEST['author'];
					$content = $_REQUEST['content'];
					
					// Never trust the user.
					$error = 0;
					function displayError($message)
					{
						global $error;
						$error = 1; // Flag that we have displayed an error.
						echo "$message<br />";
					}
					
					// Only check the reCatpcha if the user doesn't have fast submit permissions
					if (hasPermission("poem.fastSubmit") != 1)
					{
						// Check that reCaptcha!
				  		$resp = recaptcha_check_answer ($recatpchaPrivateKey,
				                                $_SERVER["REMOTE_ADDR"],
				                                $_POST["recaptcha_challenge_field"],
				                                $_POST["recaptcha_response_field"]);
				                                
		                if (!$resp->is_valid)
				  		{
				  			displayError("Sorry, the reCaptcha was not solved correctly.");
				  		}
					}
			  		
			  		// Check the data!
					if ($title == "")
					{
						displayError("Please fill out the 'title' field.");
					}
					if ($author == "")
					{
						displayError("Please fill out the 'author' field.");
					}
					if ($content == "")
					{
						displayError("Please fill out the 'content' field.");
					}
					
					// If we encountered any errors, stop.
					if ($error == 1)
					{
						
					}
					else
					{
						try {
							// Submit a poem into the database.
							$db = getDatabase();
							$stmt = $db->prepare("INSERT INTO rit_poems(title,author,text) VALUES(:title,:author,:text)");
							$stmt->execute(array(':title' => $title, ':author' => $author, ':text' => $content));
							
							echo "Thank you; your poem has been submitted.<br />";
						} catch (PDOException $e) {
							printDatabaseError($e);
						}
					}
					echo '
						<a href="?">Back to form</a>
					';
				}
				else if ($action == "vote")
				{
					// Make sure a poem ID is set
					$id = getIdFromRequest();
					if ($id != -1)
					{
						?>
						Please fill out the reCaptcha before voting:<br />
						<?php
							echo '<form method="post" action="?action=confirmvote&id='.$id.'">';
							echo recaptcha_get_html($recatpchaPublicKey);
						?>
						
						<input type="submit" />
						</form>
						<a href="?action=view">Cancel</a>
						<?php
					}
					
				}
				else if ($action == "confirmvote")
				{
					// Make sure a poem ID is set
					$id = getIdFromRequest();
					if ($id != -1)
					{
						$vote = 1;
						// Only check the reCaptcha if the user doesn't have fast voting permissions.
						if (hasPermission("poem.fastVote") != 1)
						{
							// Check that reCaptcha!
					  		$resp = recaptcha_check_answer ($recatpchaPrivateKey,
					                                $_SERVER["REMOTE_ADDR"],
					                                $_POST["recaptcha_challenge_field"],
					                                $_POST["recaptcha_response_field"]);
					                                
			                if (!$resp->is_valid)
					  		{
					  			echo 'Sorry, the reCaptcha was not solved correctly.<br />';
					  			echo '<a href="?action=vote&id='.$id.'">Try again</a>';
					  			$vote = 0;
					  		}
						}
				  		if ($vote == 1)
				  		{
				  			try {
					  			// Grab the current amount of votes
					  			$db = getDatabase();
								$stmt = $db->prepare("SELECT * FROM rit_poems WHERE id=:id");
								$stmt->execute(array(':id' => $id));
								$row = $stmt->fetch(PDO::FETCH_ASSOC);
								$votes = $row['votes'];
								
								// Increment it
								$votes++;
								
								// And save it!
								$stmt = $db->prepare("UPDATE rit_poems SET votes=:votes WHERE id=:id");
								$stmt->execute(array(':id' => $id, ':votes' => $votes));
								
					  			echo 'Thank you for voting.<br />';
					  			$action = "view";
				  			} catch (PDOException $e) { 
								printDatabaseError($e);
							}
				  		}
					}
				}
				else if ($action == "delete")
				{
					// Make sure a poem ID is set
					$id = getIdFromRequest();
					if ($id != -1)
					{
						if (hasPermission("poem.delete"))
						{
							try {
								$db = getDatabase();
								$stmt = $db->prepare("DELETE FROM rit_poems WHERE id=:id");
								$stmt->execute(array(':id' => $id));
								
								echo 'The poem has been deleted.<br />';
								$action = "view";
								
							} catch (PDOException $e) {
								printDatabaseError($e);
							}
						}
					}
				}
				else
				{
					if ($action != "view")
					{
						$action = "create";
					}
				}
				
				// Allow other actions to "fall through" to the view action.
				
				if ($action == "view")
				{
					// Default order
					$order = "votesdesc";
					
					// Check if order has been changed.
					if (isset($_REQUEST['by']))
					{
						$order = $_REQUEST['by'];
					}
					
					// Array of possible order types
					$fields = array(
						"votes" => array(
								"name" => "Votes", 
								"field" => "votes", 
							), 
						"time" => array(
								"name" => "Time Submitted", 
								"field" => "created"
							),
					);
					echo '<pre class="order">Organize by: ';
					$sqlField = "";
					$sqlOrder = "";
					foreach ($fields as $name => $field)
					{
						$ascName = $name . 'asc';
						$descName = $name . 'desc';
						$active = 0;
						$by = $descName;
						if ($order == $ascName)
						{
							$active = 1;
							$sqlField = $field['field'];
							$sqlOrder = "ASC";
						}
						else if ($order == $descName)
						{
							$active = 1;
							$by = $ascName;
							$sqlField = $field['field'];
							$sqlOrder = "DESC";
						}
						echo '<a href="?action=view&by='.$by.'">'.$field['name'].'</a> ';
					}
					echo '</pre><hr />';
					try {
						$db = getDatabase();
						if (true)
						{
							$offset = 1;
							$stmt = $db->prepare("SELECT * FROM rit_poems WHERE created>:created ORDER BY ".$sqlField." ".$sqlOrder." ");
							$stmt->execute(array(":created" => date("Y-m-d H:i:s", strtotime("-".$offset." Friday"))));
						}
						else
						{
							$stmt = $db->prepare("SELECT * FROM rit_poems");
							$stmt->execute();
						}
						$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
						foreach($rows as $row)
						{
							// Quick fix for poems that don't have a timestamp for whatever reason.
							$created = "";
							if ($row['created'] != "0000-00-00 00:00:00")
							{
								$created = '<pre class="poem time">Submitted on '.$row['created'].'</pre>';
							}
							
							// TODO: Some kind of authentication check.
							if (true)
							{
								$actions = '<pre class="poem actions">';
								
								if (hasPermission("poem.delete"))
								{
									$actions .= '<a href="?action=delete&id='.$row['id'].'" onclick="return confirm(\'Are you sure you want to delete this poem?\') ">Delete</a>';
								}
								
								$actions .= '</pre>';
							}
							
							$voteAction = "vote";
							// Check if they have permission to bypass reCaptcha
							if (hasPermission("poems.fastVote"))
							{
								$voteAction = "confirmvote";
							}
							?>
							<div class="poem">
								<?php echo $created; ?>
								<h2 class="poem"><?php echo $row['title']; ?></h2>
								<h3 class="poem">by <?php echo $row['author']; ?></h3>
								<hr />
								<pre class="poem"><?php echo nl2br($row['text']); ?>
								</pre>
								<hr />
								<span class="poem">Votes: <?php echo $row['votes']; ?><br />
									<a href="?action=<?php echo $voteAction; ?>&id=<?php echo $row['id']; ?>">Cast vote</a>
								</span>		
								<?php echo $actions; ?>
							</div>
							<?php
						}
					} catch (PDOException $e) { 
						printDatabaseError($e);
					}
				}
				else if ($action == "create")
				{
					// Display the poem creation form.
					?>
					<form method="post" action="?action=submit">
						
						Poem Title<br />
						<input type="text" name="title" size="35" /><br />
						
						Author Name<br />
						<input type="text" name="author" size="35" /><br />
						
						Poem Content:
						<br />
					
						<textarea name="content" rows="5" cols="50"></textarea>
						<br />
						
						<?php
							if (hasPermission("poem.fastSubmit") != 1)
							{
					        	echo recaptcha_get_html($recatpchaPublicKey);
							}
						?>
						
						<input type="submit" />
						
					</form>
					<?php
				}
			?>
			</div>
		</div>
		<div class="footer">
			RIT Honors Poems Build #<?php echo $release; ?><br />
			created by the Honors Technology Committee
		</div>
	</body>

</html>

