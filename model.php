<?php

	/*
	** Statuses:
	** 0 - No user associated with name
	** 1 - user is pending approval
	** 2 - user is a normal user
	** 3 - user is admin
	*/
	
	class Model{
	
		private $databaseUser = "quartzadmin";
		private $databasePass = "quartzpassword";
		private $databaseName = 'quartz';
		private $serverName = 'localhost';
		private $rootname = "root";
		private $rootpass = "";
	
		function __construct(){
			$mysqli = new mysqli($this->serverName, $rootname, $rootpass);
			
			$query = "CREATE DATABASE IF NOT EXISTS quartz;";
			$mysqli->query($query) or die($mysqli->error);
			
			$query = "USE quartz;";
			$mysqli->query($query) or die($mysqli->error);
			
			$query = "GRANT ALL ON quartz.* TO '$this->databaseUser'@'$this->serverName';";
			$mysqli->query($query) or die($mysqli->error);
			
			$query = "SET PASSWORD FOR '$this->databaseUser'@'$this->serverName' = PASSWORD('$this->databasePass');";
			$mysqli->query($query) or die($mysqli->error);
			
			$mysqli->close();
			
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);
			
			$query = "CREATE TABLE IF NOT EXISTS persons
				(id TEXT, username TEXT, password TEXT, status int);";
			$mysqli->query($query) or die($mysqli->error);
			
			$query = "CREATE TABLE IF NOT EXISTS info
				(id TEXT, email TEXT, address TEXT, name TEXT, bio TEXT, phone TEXT,
				fax TEXT, officehours TEXT, jobtitle TEXT, teach TEXT,
				research TEXT, awards TEXT);";
			$mysqli->query($query) or die($mysqli->error);
			
			$query = "CREATE TABLE IF NOT EXISTS emails
				(recipient TEXT, subject TEXT, body TEXT);";
			$mysqli->query($query) or die($mysqli->error);
			
			$mysqli->close();
		}
	
		public function addValidUser($name){
		
			// adds a username to the list of valid users (Note: this does
			// not create an account for them, it simply makes it possible
			// for an account to be created for them)
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "INSERT INTO persons SET username=?, password='', status=0";
			
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $name);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			
			$mysqli->close();
			
			return True;
		}

		public function isNameAllowed($name){
		
			// determines if the username specified is among the list of
			// valid usernames
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT id FROM persons WHERE username=? AND status=0";
			
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $name);
			
			$stmt->execute();
			
			$stmt->bind_result($ret);
			
			$result = $stmt->fetch();
		
			$mysqli->close();
			
			return $result == True;
		}
		
		public function storeRegistrationData($name, $password){
			
			// takes a username and password, if the username is valid,
			// creates the account, setting the password as specified and
			// setting the status of the account from no user to pending
			// approval
			
			if (!$this->isNameAllowed($name))
				return False;
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$newid = $name.date('d-m-Y/G:i:s'); // NEEDS TO HAVE THE ID FUNCTION IMPLEMENTED
			$newid = md5($newid);
			
			$query = "UPDATE persons SET id=?, password=?, status=1 WHERE username=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("sss", $newid, $password, $name);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$mysqli->close();
			
			$this->createInfo($newid, $name); // to store his information
			return $newid;
		}
		
		public function login($name, $password){
		
			// takes as input the username and password for the user
			// returns false if the user does not exist or the password
			// was invalid, returns -1 if the user has not yet been approved
			// if successful, returns the id number of the user.
			// if the user is the admin, returns 'admin'
			
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT id,password,status FROM persons WHERE username=? AND status<>0";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $name);
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			$stmt->bind_result($id, $pass, $status);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if (!$result)
				return False;
			
			if ($pass == $password){
				if ($status == 1)
					return -1;
				if ($status == 3 || $status > getrandmax()/2) // if the status is larger than getrandmax()/2 then the admin has requested a password reset
					return 'admin';
				return $id;
			} else {
				return False;
			}
		}
			
		public function activateAccount($id){
			
			// sets the user's approval from pending to normal user
			// returns False if an invalid user is given
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE persons SET status=2 WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
			
		public function deleteUser($name){
			// takes a user names and removes the user
			// this is done by removing the password for the entry
			// and setting the status to 0 (unused account)
		
			if (!$this->isNameAllowed($name))
				return True;
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE persons SET status=0, password='' WHERE username=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $name);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			} 
			
			$id = $this->getId($name);
			
			$result = $this->removeInfo($id); // any info which exists for the user must also be removed.
			
			return $result;
		}
		
		public function removeValidUser($name){
		
			// removes from the list of valid usernames the given
			// name and any info associated with that name
		
			if (!$this->isNameAllowed($name))
				return True;
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$id = getId($name);
			
			$result = removeInfo($id); // any info which exists for the user must also be removed.
			
			$query = "DELETE FROM persons WHERE username=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $name);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function userExists($name){
		
			// checks to see if a given user is current user
			// their status is non-zero
			// takes the username and returns true if the user exists
		
			if (!$this->isNameAllowed($name))
				return False;
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT status FROM persons WHERE username=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param('s', $name);
			
			if (!$stmt->execute()){
				$stmt->close();
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($status);
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				if ($status != 0)
					return True;
				return False;
			} else
				return False;
		}
		
		public function setResetStatus($id){
		
			// creates a reset password status for a user with the
			// given id. the reset password status is a randomly
			// generated integer between 4 and getrandmax()/2 for 
			// a non-admin user, and between getrandmax()/2 +1 and
			// getrandmax() for the admin
			// this changes the status field to the reset status
			// note that this does not change the ability of the user
			// to log in or make changes to their account, and does not
			// keep them from showing up as a valid user
			// return true if successful
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT status FROM persons WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param('i', $id);
			
			if (!$stmt->execute()){
				$stmt->close();
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($status);
			
			$result = $stmt->fetch();
			
			if (!$result){
				$stmt->close();
				$mysqli->close();
				return False;
			}
			$stmt->close();
			
			$newstatus = $status;
			
			if ($status == 0 || $status == 1){
				$mysqli->close();
				return False;
			} else if ($status > 3){
				$mysqli->close();
				return True;
			} else if ($status == 2){
				$newstatus = rand(4, getrandmax()/2);
			} else
				$newstatus = rand(getrandmax()/2 + 1, getrandmax());
			
			$query = "UPDATE persons SET status=? WHERE id=?";
			$stmt = $mysqli->prepare($query) or die($mysqli->error);
			$stmt->bind_param("ii", $newstatus, $id);
			
			if (!$stmt->execute()){
				$stmt->close();
				$mysqli->close();
				return False;
			}
			
			$stmt->close();
			$mysqli->close();
			
			return True;
		}
		
		public function getResetStatus($id){
		
			// returns the reset status of the user with a given
			// id, if the status is not a reset status (less than 4)
			// it will return false
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT status FROM persons WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("i", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($status);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if (!$result){
				return False;
			}
			
			if ($status < 4){ // don't return the status if it is not a reset status
				return False;
			}
			
			return $status;
		}
		
		public function checkReset($id, $reset){
		
			// checks if a given reset integer matches the reset 
			// status for a user with given id. if the status of 
			// the user is less than 4 (a non-reset status), then
			// it will return false
			// returns true if the reset integers match.
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT status FROM persons WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("i", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($status);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if (!$result)
				return False;
			
			if ($status < 4) // if the status is normal for the user, do not check if it is equal
				return False;
				
			if ($status == $reset)
				return True;
			
			return False;
		}

		public function resetStatus($id){
		
			// resets the status field for a user with the given id
			// to its default value, 2 for a regular user and 3 for the
			// admin 
			// note. non-approved users should not be allowed to set
			// a reset status
			// return true if successful
		
			$status = $this->getResetStatus($id);
			
			if (!$status) // status does not need to be changed
				return True;
				
			$newstatus = 0;
			if ($status > getrandmax()/2) // admin reset status
				$newstatus = 3;
			else // normal user reset status
				$newstatus = 2;
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE persons SET status=$newstatus WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("i", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			
			$mysqli->close();
			
			return True;
		}
		
		public function resetPassword($id, $reset, $newpassword){
		
			// resets the password for a given user
			// first checks to make sure that the reset status 
			// matches, if it does then the status of the user
			// will be set to the default normal.
			// the password for the given user will be changed
			// to the new password
			// return true if successful
		
			if (!$this->checkReset($id, $reset))
				return False;
				
			$this->resetStatus($id); // resets user status to normal
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE persons SET password=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("si", $newpassword, $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			
			$mysqli->close();
			
			return True;
		}
		
		public function setName($name, $id){
				
			// sets the name for a user with the given id to
			// the given name
			// note: this is not changing the username, this
			// changes the actual name of the person in info
			// ie. from Robert McManus to John McManus
			// return true if successful
			
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET name=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $name, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function setAddress($address, $id){
			// sets the office address for a user with the given id to
			// the given address
			// return true if successful
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET address=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $awards, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function setBio($bio, $id){
				
			// sets the bio for a user with the given id to
			// the given bio
			// return true if successful
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET bio=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $bio, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function setPhone($phone, $id){
				
			// sets the phone number for a user with the given id to
			// the given phone number
			// return true if successful
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET phone=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $phone, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function setFax($fax, $id){
			
			// sets the fax for a user with the given id to
			// the given fax
			// return true if successful
			
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET fax=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $fax, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function setOfficeHours($office, $id){
				
			// sets the office hours for a user with the given id to
			// the given office hours
			// return true if successful
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET officehours=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $office, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function setJobTitle($job, $id){
				
			// sets the job title for a user with the given id to
			// the given job title
			// return true if successful
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET jobtitle=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $job, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function setTeaching($teach, $id){
				
			// sets the teaching for a user with the given id to
			// the given teaching
			// return true if successful
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET teach=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $teach, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function setResearch($research, $id){
				
			// sets the research for a user with the given id to
			// the given research
			// return true if successful
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET research=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $research, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function setAwards($awards, $id){
				
			// sets the awards for a user with the given id to
			// the given awards
			// return true if successful
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "UPDATE info SET awards=? WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $awards, $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else{
				$mysqli->close();
				return False;
			}
		}
		
		public function getName($id){
				
			// gets the name for a user with the given id
			// note: this is not the username, this is
			// the actual name of the person in info
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT name FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($name);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $name;
			}
			
			return False;
		}
		
		public function getAddress($id){
			// gets the address for a user with the given id
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT address FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($address);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $address;
			}
			
			return False;
		}
		
		public function getEmail($id){
		
			// gets the email for a user with the given id
			// this is equivalent to getting the username
			// for the user
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT email FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($email);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $email;
			}
			
			return False;
		}
		
		public function getBio($id){
				
			// gets the bio for a user with the given id
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT bio FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($bio);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $bio;
			}
			
			return False;
		}
		
		public function getPhone($id){
				
			// gets the phone number for a user with the given id
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT phone FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($phone);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $phone;
			}
			
			return False;
		}
		
		public function getFax($id){
			
			// gets the fax for a user with the given id
			
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT fax FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($fax);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $fax;
			}
			
			return False;
		}
		
		public function getOfficeHours($id){
				
			// gets the office hours for a user with the given id
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT officehours FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($office);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $office;
			}
			
			return False;
		}
		
		public function getJobTitle($id){
				
			// gets the job title for a user with the given id
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT jobtitle FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($job);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $job;
			}
			
			return False;
		}
		
		public function getTeaching($id){
				
			// gets the teaching for a user with the given id
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT teach FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($teach);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $teach;
			}
			
			return False;
		}
		
		public function getResearch($id){
				
			// gets the research for a user with the given id
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT research FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($research);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $research;
			}
			
			return False;
		}
		
		public function getAwards($id){
				
			// gets the awards for a user with the given id
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT awards FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($awards);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $awards;
			}
			
			return False;
		}
		
		public function getAll($id){
			
			// gets the all the information for a user with the given id
			// returns an associated array with all the information
			
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT * FROM info WHERE id='$id';";
			
			$result = $mysqli->query($query);
			
			$row = $result->fetch_assoc();
			
			$mysqli->close();
			
			return $row;
		}
		
		public function getId($name){

			// gets the id of the user with the given username
			// returns false if the user does not exist
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT id FROM persons WHERE username=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $name);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return False;
			}
			
			$stmt->bind_result($id);
			
			$result = $stmt->fetch();
			
			$mysqli->close();
			
			if ($result){
				return $id;
			}
			
			return False;
		}
		
		private function removeInfo($id){
		
			// removes any entry in the info table for a user with
			// the given id
			// note: this should not be used to remove something
			// like a name or bio, for those use the "set" functions
			// above, this deletes the user from the database
			// and should not be used outside of model.php
			// returns true if successful
		
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "DELETE FROM info WHERE id=?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("s", $id);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else {
				$mysqli->close();
				return False;
			}
		}
		
		private function createInfo($id, $name){
				
			// creates an info entry in info table for a user
			// with a given id and name
			// note: this creates a new entry in the table for
			// the associated user, this function is used automatically
			// by the create account functions and should not
			// be used outside of model.php
			// returns true if successful
				
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "INSERT INTO info (id, email, name, bio, phone, fax, officehours, jobtitle, teach, research, awards) VALUES (?, ?, '', '', '', '', '', '', '', '', '')";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $id, $name);
			
			if ($stmt->execute()){
				$mysqli->close();
				return True;
			} else {
				$mysqli->close();
				return False;
			}
		}
	
		public function queueEmailForAdmin($email, $subject, $body){
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "INSERT INTO emails VALUES (?, ?, ?);";
			$stmt = $mysqli->prepare($query);
			print $mysqli->error;
			$stmt->bind_param("sss", $email, $subject, $body);
			
			if ($stmt->execute()){
				$mysqli->close();
				return true;
			}
			
			$mysqli->close();
			
			return false;
		}
		
		public function createAdmin($username, $password){
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "DELETE FROM persons WHERE id='admin';";
			$result = $mysqli->query($query);
			if (!$result)
				return false;
			
			$query = "INSERT INTO persons VALUES ('admin',?,?,3);";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("ss", $username, $password);
			
			if (!$stmt->execute()){
				$mysqli->close();
				return false;
			}
			
			$mysqli->close();
			return true;
		}
		
		public function getEmails(){
			$mysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			if ($mysqli->connect_error)
			{
				print("PHP unable to connect to MySQL server; error (" . $mysqli->connect_errno . "): "
				. $mysqli->connect_error);

				exit();
			}
			
			$query = "SELECT * FROM emails;";
			$result = $mysqli->query($query);
			
			$rows = $result->fetch_assoc();
			
			$mysqli->close();
			
			return $row;
		}
		
		public function importdata($username, $password, $database){
			$mysqli = new mysqli($this->serverName, $database, $username, $password);
			$mymysqli = new mysqli($this->serverName, $this->databaseUser,
			$this->databasePass, $this->databaseName);

			$query = "SELECT * FROM loginset;";
			$result = $mysqli->query($query);
			if (!$result)
				return false;
			
			while ($loginset = $result->fetch_assoc()){
				$username = $loginset['email'];
				$approved = $loginset['isApproved'];
				$id = $loginset['hash'];
				
				$query = "SELECT * FROM nLogin WHERE email='".$username."';";
				$newresult = $mysqli->query($query);
				$row = $newresult->fetch_assoc();
				$active = $row['isactive'];
				$status = 0;
				
				if ($active == 2)
					$status = 3;
				else{
					if ($approved)
						$status = 2;
					else
						$status = 1;
				}
				$password = $row['password'];
				$query = "INSERT INTO persons VALUES ('$id', '$username', '$password', $status);";
				$mymysqli->query($query);
				
				$this->createInfo($id, $row['name']);
				
				$query = "SELECT * FROM webData WHERE email='$username';";
				$newresult = $mysqli->query($query);
				$row = $newresult->fetch_assoc();
				
				$this->setAddress($row['office'], $id);
				$this->setBio($row['bio'], $id);
				$this->setPhone($row['phone'], $id);
				$this->setFax($row['fax'], $id);
				$this->setOfficeHours($row['ofhours'], $id);
				$this->setJobTitle($row['jobtitle'], $id);
				$this->setTeaching($row['teaching'], $id);
				$this->setResearch($row['researchsum'], $id);
				$this->setAwards($row['awards'], $id);
			}
			return true;
		}
		
		public function removeData(){
			$mysqli = new mysqli($this->serverName, $rootname, $rootpass);
			
			$query = "DROP DATABASE quartz;";
			$result = $mysqli->query($query);
			
			return $result;
		}
		
		public function setMailServer($server){
			return true;
		}
		
		public function getMailServerStatus(){
			return false;
		}
	}
	
?>
