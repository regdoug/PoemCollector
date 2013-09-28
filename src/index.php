<?php
	/**
	* @author Ryan Rule-Hoffman
	**/
	$release = 3; // Build number
	
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
		return new PDO('mysql:host='.getConfigValue('dbHost_w_hon').';dbname=w_hon;charset=utf8', 'w-hon', getConfigValue('dbPass_w_hon'));
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
			<?php /* <a href="?action=view">View submissions</a><br /> */ ?>
			<div class="editor">
			<?php
				// Check what our current action is a display the relevent page.
				if ($action == "create")
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
							require_once('recaptchalib.php');
					        $publickey = "6LewcucSAAAAAJOxCV4h_KDIqc019Zb77C2BEVPp"; // you got this from the signup page
					        echo recaptcha_get_html($publickey);
						?>
						
						<input type="submit" />
						
					</form>
					<?php
				}
				else if ($action == "submit")
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
					
					// Check that reCaptcha!
					require_once('recaptchalib.php');
			  		$privatekey = "6LewcucSAAAAAJ64W2-qJbBuFeN3bK_j3HL0BE3Y";
			  		$resp = recaptcha_check_answer ($privatekey,
			                                $_SERVER["REMOTE_ADDR"],
			                                $_POST["recaptcha_challenge_field"],
			                                $_POST["recaptcha_response_field"]);
			                                
	                if (!$resp->is_valid)
			  		{
			  			displayError("Sorry, the reCaptcha was not solved correctly.");
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
						// Submit a poem into the database.
						$db = getDatabase();
						$stmt = $db->prepare("INSERT INTO rit_poems(title,author,text) VALUES(:title,:author,:text)");
						$stmt->execute(array(':title' => $title, ':author' => $author, ':text' => $content));
						
						echo "Thank you; your poem has been submitted.<br />";
					}
					echo '
						<a href="?">Back to form</a>
					';
				}
				else if ($action == "view")
				{
					$db = getDatabase();
					$stmt = $db->prepare("SELECT * FROM rit_poems");
					$stmt->execute();
					$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
					foreach($rows as $row)
					{
						echo '
							<div class="poem">
								<h2 class="poem">'.$row['title'].'</h2>
								<h3 class="poem">by '.$row['author'].'</h3>
								<pre class="poem">'.nl2br($row['text']).'</pre>
							</div>
						';
					}
				}
			?>
			</div>
		</div>
	</body>

</html>

