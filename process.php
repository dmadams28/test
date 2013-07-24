<?php

session_start();
include_once("connection.php");

class Process{

	var $connection;

	public function __construct(){

		$this->connection = new Database();

		//see if the user wants to login
		if(isset($_POST['action']) and $_POST['action'] == "login")
		{
			$this->loginAction();
		}
		else if(isset($_POST['action']) and $_POST['action'] == "register")
		{
			$this->registerAction();
		}
		else if(isset($_POST['action']) and $_POST['action'] == "friend")
		{
			$this->friendAction();
		}
		else if(isset($_POST['action']) and $_POST['action'] == "logoff")
		{
			//assume that the user wants to log off
			session_destroy();
			header("Location: index.php");
		}
		else /*(isset($_POST['action']) and $_POST['action'] == "friend")*/
		{
			$this->user_load();
		}
	}

	private function loginAction()
	{
		$errors = array();

		if(!(isset($_POST['email']) and filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)))
		{
			$errors[] = "email is not valid";
		}

		if(!(isset($_POST['password']) and strlen($_POST['password'])>=6))
		{
			$errors[] = "please double check your password (length must be greater than 6)";
		}

		//see if there are errors
		if(count($errors) > 0)
		{
			$_SESSION['login_errors'] = $errors;
			header('Location: index.php');
		}
		else
		{
			//check if the email and the password is valid
			$query = "SELECT * FROM users WHERE email = '{$_POST['email']}' AND password ='".md5($_POST['password'])."'";
			$users = $this->connection->fetch_all($query);
			
			if(count($users)>0)
			{
				$_SESSION['logged_in'] = true;
				$_SESSION['user']['first_name'] = $users[0]['first_name'];
				$_SESSION['user']['last_name'] = $users[0]['last_name'];
				$_SESSION['user']['email'] = $users[0]['email'];
				$_SESSION['user']['id'] = $users[0]['id'];
				header("Location: wall.php");
			}
			else
			{
				$errors[] = "Invalid login information";
				$_SESSION['login_errors'] = $errors;
				header('Location: index.php');
			}
		}
	}

	private function registerAction()
	{
		$errors = array();
		//let's see if the first_name is a string
		if(!(isset($_POST['first_name']) and is_string($_POST['first_name']) and strlen($_POST['first_name'])>0))
		{
			$errors[] = "first name is not valid!";
		}

		//let's see if the last_name is a string
		if(!(isset($_POST['last_name']) and is_string($_POST['last_name']) and strlen($_POST['last_name'])>0))
		{
			$errors[] = "last name is not valid!";
		}

		if(!(isset($_POST['email']) and filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)))
		{
			$errors[] = "email is not valid";
		}

		if(!(isset($_POST['password']) and strlen($_POST['password'])>=6))
		{
			$errors[] = "please double check your password (length must be greater than 6)";
		}

		if(!(isset($_POST['confirm_password']) and isset($_POST['password']) and $_POST['password'] == $_POST['confirm_password']))
		{
			$errors[] = "please confirm your password";
		}

		if(count($errors)>0)
		{
			$_SESSION['register_errors'] = $errors;
			header("Location: index.php");
		}
		else
		{
			//see if the email address already is taken
			$query = "SELECT * FROM users WHERE email = '{$_POST['email']}'";
			$users = $this->connection->fetch_all($query);	

			//see if someone already registered with that email address
			if(count($users)>0)
			{
				$errors[] = "someone already registered with this email address";
				$_SESSION['register_errors'] = $errors;
				header("Location: index.php");
			}
			else
			{
				$query = "INSERT INTO users (first_name, last_name, email, password, created_at) VALUES ('{$_POST['first_name']}', '{$_POST['last_name']}', '{$_POST['email']}', '".md5($_POST['password'])."', NOW())";
				mysql_query($query);

				$_SESSION['message'] = "User was successfully created!";
				header("Location: index.php");
			}
		}
	}
	
	function user_load(){
		//pull people you have friended
		$query = "SELECT t1.first_name, t1.last_name, t1.email FROM users AS t1 LEFT JOIN friends AS t2 ON t1.id=t2.friend_id WHERE t2.users_id='{$_SESSION['user']['id']}'";
		$_SESSION['friender'] = $this->connection->fetch_all($query);
		$_SESSION['friender'] = array_map("unserialize", array_unique(array_map("serialize", $_SESSION['friender'])));
		?><pre><?php
			//var_dump($_SESSION['friender']);
		?></pre><?php
		
		//pull people who have friended you
		$query = "SELECT t1.first_name, t1.last_name, t1.email FROM users AS t1 LEFT JOIN friends AS t2 ON t1.id=t2.users_id WHERE t2.friend_id='{$_SESSION['user']['id']}'";
		$_SESSION['friendee'] = $this->connection->fetch_all($query);
		$_SESSION['friendee'] = array_map("unserialize", array_unique(array_map("serialize", $_SESSION['friendee'])));
		?><pre><?php
			//var_dump($_SESSION['friendee']);
		?></pre><?php
		
		//pull people by id
		$query = "SELECT id, first_name, last_name, email FROM users";
		$_SESSION['users'] = $this->connection->fetch_all($query);
		$_SESSION['users'] = array_map("unserialize", array_unique(array_map("serialize", $_SESSION['users'])));
		?><pre><?php
			//var_dump($_SESSION['users']);
		?></pre><?php
	}
	
	function friendAction() {
		
		if(isset($_POST['id'])) {
			$query = "INSERT INTO friends (users_id, friend_id) VALUES ('{$_SESSION['user']['id']}','{$_POST['id']}')";
			//echo $query;
			mysql_query($query);
		}
		
		//$query = "SELECT t2.friend_id,t1.first_name, t1.last_name, t1.email FROM users AS t1 LEFT JOIN friends AS t2 ON t1.id=t2.friend_id WHERE t1.id = t2.friend_id";	
		//$_SESSION['friend_data'] = $this->connection->fetch_all($query);
		//var_dump($_SESSION['friend_data']);
		
		header("Location: wall.php");
	}
	
	function friend_test() {
		$_SESSION['friends'] = array();
		foreach($_SESSION['friender'] as $user) {
		    if(in_array($user['email'], $user)) {
			array_push($_SESSION['friends'],$user['email']);
		    }
		}
		foreach($_SESSION['friendee'] as $user) {
		    if(in_array($user['email'], $user)) {
			array_push($_SESSION['friends'],$user['email']);
		    }
		}
		//var_dump($_SESSION['friends']);
	}
}

$process = new Process();

?>