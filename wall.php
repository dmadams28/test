<?php

	include("process.php");

	if(!isset($_SESSION['logged_in']))
	{
		header("Location: index.php");
	}
?>

Welcome <?= $_SESSION['user']['first_name'] ." " . $_SESSION['user']['last_name'] ?>!

<form action="process.php" method="post">
	<input type="hidden" name="action" value="logoff">
	<input type="submit" value="Log Off">
</form>

<html>
	<head>
		
	</head>
	<body>
		<div>
			<h2>List of Friends</h2>
			<table>
				<?php
				foreach ($_SESSION['friender'] as $friend) { ?>
					<tr>
						<td><?php echo $friend['first_name']; ?></td>
						<td><?php echo $friend['last_name']; ?></td>
						<td><?php echo $friend['email']; ?></td>
					</tr><?php
				}
				foreach ($_SESSION['friendee'] as $friend) { ?>
					<tr>
						<td><?php echo $friend['first_name']; ?></td>
						<td><?php echo $friend['last_name']; ?></td>
						<td><?php echo $friend['email']; ?></td>
					</tr><?php
				}?>
			</table>
			<h2>List of Users Subscribed to Friend Finder</h2>
			<table>
				<?php
				//var_dump($_SESSION['user_data']);
				//var_dump($_SESSION['user']['id']);
				$process->friend_test();
				foreach ($_SESSION['users'] as $user ) {
					?><tr>
						<td><?php echo $user['first_name']; ?></td>
						<td><?php echo $user['last_name']; ?></td>
						<td><?php echo $user['email']; ?></td>
						<td>
							<?php
							
							if (in_array($user['email'],$_SESSION['friends'])) { echo "Friend"; }
							else if ($user['email'] == $_SESSION['user']['email']) { echo "Me"; }
							else { ?>
								<form action="process.php" method="post">
									<input type="hidden" name="action" value="friend">
									<input type="hidden" name="id" value="<?php echo $user['id']; ?>">
									<input type="submit" value="Add As Friend">
								</form>
							<?php } ?>
						</td>
					</tr><?php	
				}
				
				
				
				?>
			</table>
		</div>
	</body>
</html>