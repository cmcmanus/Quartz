<?php

	$usestub = true;
	
	if ($usestub){
		include_once "modelstub.php";
	} else {
		include_once "model.php";
	}
	
	function checkCredentials(){
	
	}
	
	include_once "server.php";
	
	class RegistrationActivity{
		
	}
	
	class ViewAccountActivity{
	
		private $context;
		private $model;
		
		private $id = null;
		private $name = null;
		private $jobtitle = null;
		private $address = null;
		private $tel = null;
		private $fax = null;
		private $officehours = null;
		private $bio = null;
		private $research = null;
		private $awards = null;
		private $courseinfo = null;
		private $courses = null;
		private $invalidentries = false;
		private $dataerror = false;
		private $server = null;
		private $newaduser = null;
		private $newadpass = null;
		private $newconfpass = null;
		private $nomatch = false;
		private $successchange = false;
	
		function __construct(){
			$this->model = new Model();
			
			if (isset($_POST['saveinfo']))
				$this->context = "saveinfo";
			else if (isset($_POST['upload']))
				$this->context = 'upload';
			else if (isset($_GET['mysite']))
				$this->context = 'mysite';
			else if (isset($_GET['teaching']))
				$this->context = 'teaching';
			else if (isset($_GET['research']))
				$this->context = 'research';
			else if (isset($_GET['awards']))
				$this->context = 'awards';
			else if (isset($_GET['settings']))
				$this->context = 'settings';
			else if (isset($_POST['server']))
				$this->context = 'setserver';
			else if (isset($_POST['saveuser']))
				$this->context = 'saveadminuser';
			else if( isset($_GET['emails']))
			{
				$this->context = 'emailview';
			}
			else if(isset($_POST['removeEmail']))
			{
				$this->context = 'removeE';
			}
			else if (isset($_GET['users']))
			{
				$this->context = 'adminuser';
			}
			else if (isset($_POST['delete']))
			{
				$this->context = 'deleteuser';
			}
			else if (isset($_POST['deauthorize']))
			{
				$this->context = 'deauthorizeuser';
			}
			else if (isset($_POST['addemail']))
			{
				$this->context = 'addemail';
			}
			
			else
				$this->context = "mymanage";
				
			$this->getInput();
		}
		
		public function run(){
			$this->process();
			$this->show();
		}
		
		private function getInput(){
			if ($this->context == 'saveinfo'){
				if (isset($_POST['id']))
					$this->id = $_POST['id'];
				if (isset($_POST['course']))
					$this->courses = $_POST['course'];
				if (isset($_POST['displayname']))
					$this->name = $_POST['displayname'];
				if (isset($_POST['jobtitle']))
					$this->jobtitle = $_POST['jobtitle'];
				if (isset($_POST['address']))
					$this->address = $_POST['address'];
				if (isset($_POST['tel']))
					$this->tel = $_POST['tel'];
				if (isset($_POST['fax']))
					$this->fax = $_POST['fax'];
				if (isset($_POST['officehours']))
					$this->officehours = $_POST['officehours'];
				if (isset($_POST['bio']))
					$this->bio = $_POST['bio'];
				if (isset($_POST['research']))
					$this->research = $_POST['research'];
				if (isset($_POST['awards']))
					$this->awards = $_POST['awards'];
			}
			else if ($this->context == 'mymanage'){
				if (isset($_GET['id'])){
					$this->id = $_GET['id'];
				}	
			} else if ($this->context == 'upload'){
				if (isset($_POST['id']))
					$this->id = $_POST['id'];
			} else if ($this->context == 'mysite'){
				if (isset($_GET['id']))
					$this->id = $_GET['id'];
			} else if ($this->context == 'teaching'){
				if (isset($_GET['id']))
					$this->id = $_GET['id'];
			} else if ($this->context == 'research'){
				if (isset($_GET['id']))
					$this->id = $_GET['id'];
			} else if ($this->context == 'awards'){
				if (isset($_GET['id']))
					$this->id = $_GET['id'];
			} else if ($this->context == 'setserver'){
				if (isset($_POST['server'])){
					$this->server = $_POST['server'];
				}
			} else if ($this->context == 'settings'){
				if (isset($_GET['id'])){
					$this->id = $_GET['id'];
				}
			} else if ($this->context == 'saveadminuser'){
				if (isset($_POST['newuser'])){
					$this->newaduser = $_POST['newuser'];
				}
				if (isset($_POST['newpass'])){
					$this->newadpass = $_POST['newpass'];
				}
				if (isset($_POST['confpass'])){
					$this->newconfpass = $_POST['confpass'];
				}
			}
			else if($this->context == 'emailview')
			{
				if(isset($_GET['id']))
				{
					$this->id= $_GET['id'];
				}
			}
			else if($this->context == 'removeE')
			{
				if(isset($_POST['emailID']))
				{
					$this->id = $_POST['emailID'];
				}
			}
			else if($this->context == 'adminuser')
			{
				if(isset($_GET['id']))
				{
					$this->id= $_GET['id'];
				}
			}
			else if($this->context == 'deleteuser')
			{
				if(isset($_POST['userID']))
				{
					$this->id = $_POST['userID'];
				}
			}
			
			else if($this->context == 'deauthorizeuser')
			{
				if(isset($_POST['userID']))
				{
					$this->id = $_POST['userID'];
				}
			}
			else if($this->context == 'addemail')
			{
				if(isset($_POST['newEmail']))
				{
					$this->name = $_POST['newEmail'];
				}
			}
		}
		
		private function process(){
			if ($this->context == 'saveinfo'){
				if ($this->id == null or $this->id == '')
					$this->invalidentries = true;
				if ($this->invalidentries)
					return;
				if ($this->courses == null)
					$this->courses = "";
				if ($this->name == null)
					$this->name = '';
				if ($this->jobtitle == null)
					$this->jobtitle = '';
				if ($this->address == null)
					$this->address = '';
				if ($this->tel == null)
					$this->tel = '';
				if ($this->fax == null)
					$this->fax = '';
				if ($this->officehours == null)
					$this->officehours = '';
				if ($this->bio == null)
					$this->bio = '';
				if ($this->research == null)
					$this->research = '';
				if ($this->awards == null)
					$this->awards = '';
				// [MAN.04]
				if (!$this->model->setName($this->name, $this->id)){
					$this->dataerror = true;
					return;
				}
				if (!$this->model->setJobTitle($this->jobtitle, $this->id)){
					$this->dataerror = true;
					return;
				}
				if (!$this->model->setBio($this->bio, $this->id)){
					$this->dataerror = true;
					return;
				}
				if (!$this->model->setPhone($this->tel, $this->id)){
					$this->dataerror = true;
					return;
				}
				if (!$this->model->setFax($this->fax, $this->id)){
					$this->dataerror = true;
					return;
				}
				if (!$this->model->setOfficeHours($this->officehours, $this->id)){
					$this->dataerror = true;
					return;
				}
				if (!$this->model->setResearch($this->research, $this->id)){
					$this->dataerror = true;
					return;
				}
				if (!$this->model->setAwards($this->awards, $this->id)){
					$this->dataerror = true;
					return;
				}
				if (!$this->model->setAddress($this->address, $this->id)){
					$this->dataerror = true;
					return;
				}
				if (!$this->model->setTeaching($this->courses, $this->id)){
					$this->dataerror = true;
					return;
				}
			}
			else if ($this->context == 'upload'){
				if ($this->id == null or $this->id == ''){
					print "<script>alert(\"Invalid log in information\");</script>";
					return;
				}
				switch ($_FILES['file']['error']) { // [PHOTO.01]
					case UPLOAD_ERR_OK:
						break;
					case UPLOAD_ERR_NO_FILE:
						throw new RuntimeException('No file sent.');
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						throw new RuntimeException('Exceeded filesize limit.');
					default:
						throw new RuntimeException('Unknown errors.');
				}
				$target_path = "resources/".$this->id.'.jpg'; // [PHOTO.02]
				@unlink($target_path); // [PHOTO.03]
				if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)){ // [PHOTO.04]
					header("Location: http://$serverlocation/viewaccount.php?id=".$this->id);
					return;
				} else {
					print "<script>alert(\"File could not be properly uploaded\")</script>";
				}
			}
			else if ($this->context == 'mymanage') {
				if ($this->id == null || $this->id == ''){
					header("Location: http://$serverlocation/login.php");
				}
				if (!$this->name = $this->model->getName($this->id))
					$this->dataerror = true;
				if (!$this->jobtitle = $this->model->getJobTitle($this->id))
					$this->dataerror = true;
				if (!$this->address = $this->model->getAddress($this->id))
					$this->dataerror=true;
				if (!$this->tel = $this->model->getPhone($this->id))
					$this->dataerror = true;
				if (!$this->fax = $this->model->getFax($this->id))
					$this->dataerror = true;
				if (!$this->officehours = $this->model->getOfficeHours($this->id))
					$this->dataerror = true;
				if (!$this->bio = $this->model->getBio($this->id))
					$this->dataerror = true;
				if (!$this->research = $this->model->getResearch($this->id))
					$this->dataerror = true;
				if (!$this->awards = $this->model->getAwards($this->id))
					$this->dataerror = true;
				if (!$this->courses = $this->model->getTeaching($this->id))
					$this->dataerror = true;
			} 
			else if ($this->context == 'setserver'){
				if ($this->server == null or $this->server == ''){
					$this->invalidentries = true;
				}
				$mailserv = true;
				if ($this->server == 'false')
					$mailserv = false;
				if (!$this->model->setMailServer($mailserv))
					$this->dataerror = true;
			}
			else if ($this->context == 'settings'){
				if ($this->id != 'admin')
					header("Location: http://$serverlocation/login.php");
			}
			else if ($this->context == 'saveadminuser'){
				$this->context = 'settings';
				if ($this->newaduser == null || $this->newaduser == ''){
					$this->invalidentries = true;
					return;
				}
				if ($this->newadpass == null || $this->newadpass == ''){
					$this->invalidentries = true;
					return;
				}
				if ($this->newconfpass == null || $this->newconfpass == ''){
					$this->invalidentries = true;
					return;
				}
				if ($this->newadpass != $this->newconfpass){
					$this->nomatch = true;
					return;
				}
				if (!$this->model->createAdmin($this->newaduser, $this->newadpass)){
					$this->dataerror = true;
				} else {
					$this->successchange = true;
				}
			}
			else if( $this->context == 'emailview'){
				if ($this->id == null || $this->id == '' || !$this->model->getEmail($this->id) || $this->id != 'admin'){
					header("Location: http://$serverlocation/login.php");
				}
			}
			else if($this->context == 'adminuser')
			{
				if ($this->id == null || $this->id == '' || !$this->model->getEmail($this->id) || $this->id != 'admin'){
					header("Location: http://$serverlocation/login.php");
				}
			}
				
			else if($this->context == 'removeE'){
				if ($this->id == null || $this->id == '')
				{
					$this->invalidentries = true;
					$this->context = 'emailview';
					return;
				}
				if(!$this->model->removeEmail($this->id))
				{
					$this->dataerror = true;
				}	
				else
				{
					$this->successchange = true;
				}
				$this->context = 'emailview';  
			}
			else if($this->context == 'deleteuser'){
				if ($this->id == null || $this->id == '')
				{
					$this->invalidentries = true;
					$this->context = 'adminuser';
					return;
				}
				// [AD.02]
				if(!$this->model-> deleteUser($this->id))
				{
					$this->dataerror = true;
				}	
				else
				{
					$this->successchange = true;
				}
				$this->context = 'adminuser';  
			}
			else if($this->context == 'deauthorizeuser'){
				if ($this->id == null || $this->id == '')
				{
					$this->invalidentries = true;
					$this->context = 'adminuser';
					return;
				}
				// [AD.01]
				if(!$this->model-> deauthUser($this->id))
				{
					$this->dataerror = true;
				}	
				else
				{
					$this->successchange = true;
				}
				$this->context = 'adminuser';  
			}
			else if($this->context == 'addemail'){
				if ($this->name == null || $this->name == '')
				{
					$this->invalidentries = true;
					$this->context = 'adminuser';
					return;
				}
				if(!$this->model-> addValidUser($this->name))
				{
					$this->dataerror = true;
				}	
				else
				{
					$this->successchange = true;
				}
				$this->context = 'adminuser';  
			}
			else {
				if ($this->id == null || $this->id == '' || !$this->model->getEmail($this->id)){
					header("Location: http://$serverlocation/login.php");
				}
			}
		}
		
		private function show(){
			global $serverlocation;
			if ($this->context == 'saveinfo'){
				if ($this->invalidentries){
					print "invalid entries";
				} else if ($this->dataerror){
					print "database error";
				} else {
					print "success";
				}
			} 
			else if ($this->context == 'mymanage'){
				if ($this->dataerror){
					header("Location: http://$serverlocation/login.php");
				}
				$image = 'images/picture.jpg';
				if (file_exists('resources/'.$this->id.'.jpg'))
					$image = 'resources/'.$this->id.'.jpg';
				print '<html>
						<head>
							<title>MyManage</title>

							<link type="text/css" rel="stylesheet" href="style.css" />
						</head>';
				print '<body bgcolor="EEEEEE">
							<div id="header">
								<div class="toplink">
									<a href="viewaccount.php?id='.$this->id.'&mysite=1">MySite</a>
									<a href="login.php">Logout</a>
								</div>
							</div>
							<div id="mymanage">
								<div class="managemain">
								<h1>Website Management Panel</h1>
								<p>Welcome '.$this->name.'</p>
								<hr width=\'880px\'/>';
				print '			<h3>Picture:</h3>';
				// [PHOTO.05]
				print'			<div class="crop" style="width:60px;height:90px;overflow:hidden;">
								<img src="'.$image.'" height="90px"';
				if (!($image === 'images/picture.jpg'))
					// [PHOTO.06]
					print 'style="position:relative;left:100%;margin-left:-200%;"';
				print '			><br/></div>
								<form enctype="multipart/form-data" action="" method="post">
								Choose a picture to upload: <input type="file" name="file" size="64"></input><br/>
								<input type="hidden" value="'.$this->id.'" name="id"></input>
								<input type="submit" name="upload" id="upload" value="Upload Picture"></input><br/>
								</form>
								<hr width="880px"/>';
				print '			<h3>General Information:</h3>
								Display Name: <input type="text" value="'.$this->name.'" name="displayname" id="displayname" size="40"></input><br/><br/>
								Job Title: <input type="text" value="'.$this->jobtitle.'" name="jobtitle" id="jobtitle" size="40"></input><br/><br/>
								Office Address: <input type="text" value="'.$this->address.'" name="address" id="address" size="40"></input><br/><br/>
								Tel: <input type="text" value="'.$this->tel.'" name="tel" id="tel" size="40"></input><br/><br/>
								Fax: <input type="text" value="'.$this->fax.'" name="fax" id="fax" size="40"></input><br/><br/>
								Office hours: <input type="text" value="'.$this->officehours.'" name="officehours" id="officehours" size="40"></input><br/><br/>
								<input type="button" onclick="saveInfo();" class=".savebtn" value="Save" name="savebutton" id="savebutton"></input><br/><br/>
				';
				print '			<hr width="880px"/>
								<h3>Short Biography:</h3>
								<textarea name="bio" id="bio" rows=7 wrap="physical" cols=100>'.$this->bio.'</textarea><br/><br/>
								<input type="button" onclick="saveInfo();" class=".savebtn" value="Save" name="savebutton" id="savebutton"></input><br/><br/>
				';
				print '			<hr width="880px"/>
								<h3>Research:</h3>
								<textarea name="research" id="research" rows=7 wrap="physical" cols=100>'.$this->research.'</textarea><br/><br/>
								<input type="button" onclick="saveInfo();" class=".savebtn" value="Save" name="savebutton" id="savebutton"></input><br/><br/>
			
								<hr width="880px"/>
								<h3>Awards:</h3>
								<textarea name="awards" id="awards" rows=7 wrap="physical" cols=100>'.$this->awards.'</textarea><br/><br/>
			
								<input type="button" onclick="saveInfo();" class=".savebtn" value="Save" name="savebutton" id="savebutton"></input><br/><br/>
								<hr width="880px"/>
			
								<h3>Edit Courses:</h3>
								<div id="course">';
				$classes = explode(';', $this->courses);
				foreach ($classes as $value){
					if ($value == '')
						continue;
					$class = explode('~', $value);
					print '<input type="checkbox" value="" name="checkclass" id="checkclass"></input>
									Course Name: <input type="text" name="coursename" value="'.$class[0].'" id="coursename" size="100"></input><br/><br/>
									Course Description<br/>
									<textarea name="coursedesc" id="coursedesc" rows=7 wrap="physical" cols=100>'.$class[1].'</textarea><br/><br/>';
				}
				print '			</div>
			
								<input type="button" name="newbutton" id="newbutton" onclick="newCourse();" value="New Course"></input>
								<input type="button" name="deletebutton" id="deletebutton" onclick="deleteCourse();" value="Delete"></input>
								<input type="button" name="savecoursebutton" id="savecoursebutton" onclick="saveInfo();" value="Save"></input><br/><br/>
								</div>
							</div>
							<div id="footer"></div>
				';
				print '		<script>
		
							var id = "'.$this->id.'";
							var displayname = "";
							var jobtitle = "";
							var address = "";
							var tel = "";
							var fax = "";
							var officehours = "";
							var bio = "";
							var research = "";
							var awards = "";
							var courseinfo = "";
								
							function onclose(e) {
								
								if(!e) 
									e = window.event;
								e.cancelBubble = true;
								e.returnValue = \'You have unsaved changes, are you sure you wish to leave?\';

								if (e.stopPropagation) {
									e.stopPropagation();
									e.preventDefault();
								}
							}
							window.onbeforeunload=onclose;
							';
				// [MAN.02]			
				print '		function parseCourses(){
								names = document.getElementsByName("coursename");
								descs = document.getElementsByName("coursedesc");
								var info = "";
								for (i = 0; i < names.length; i++){
									if (names[i].value === \'\')
										continue;
									var str = names[i].value.replace(";",",").replace("~"," ");
									str += "~" + descs[i].value.replace(";",",").replace("~"," ");
									info += str + ";";
								}
								
								return info;
							}';
				// [MAN.05]			
				print '		function newCourse(){
								var add = "<input type=\\"checkbox\\" value=\\"\\" name=\\"checkclass\\" id=\\"checkclass\\"></input>";
								add += "Course Name: <input type=\\"text\\" name=\\"coursename\\" value=\\"\\" id=\\"coursename\\" size=\\"100\\"></input><br/><br/>";
								add += "Course Description<br/>";
								add += "<textarea name=\\"coursedesc\\" id=\\"coursedesc\\" rows=7 wrap=\\"physical\\" cols=100></textarea><br/><br/>";
								var courses = document.getElementById("course");
								var newchild = document.createElement(\'div\');
								newchild.innerHTML = add;
								courses.appendChild(newchild);
							}';
				// [MAN.06]			
				print '		function deleteCourse(){
								var checks = document.getElementsByName("checkclass");
								var names = document.getElementsByName("coursename");
								var descs = document.getElementsByName("coursedesc");
								if (checks.length == 0){
									alert("There are no courses to be deleted");
									return;
								}
								var anychecked = false;
								for (i = 0; i < checks.length; i++){
									if (checks[i].checked)
										anychecked = true;
								}
								if (!anychecked){
									alert("No courses selected for deletion! Please check off the courses you would like to delete.");
									return;
								}
								var r = confirm("Are you sure you want to delete all selected courses?");
								if (!r)
									return;
								var val = "";
								for (i = 0; i < checks.length; i++){
									if (!checks[i].checked){
										val += "<input type=\"checkbox\" value=\"\" name=\"checkclass\" id=\"checkclass\"></input>";
										val += "Course Name: <input type=\"text\" name=\"coursename\" value=\"" + names[i].value + "\" id=\"coursename\" size=\"100\"></input><br/><br/>";
										val += "Course Description<br/>";
										val += "<textarea name=\"coursedesc\" id=\"coursedesc\" rows=7 wrap=\"physical\" cols=100>" + descs[i].value + "</textarea><br/><br/>";
									}
								}
								document.getElementById("course").innerHTML = val;
								alert("Courses successfully deleted!");
							}';
				// [MAN.03]			
				print '		function postInfo(){
								var xmlhttp;

								if (window.XMLHttpRequest)
								{
									xmlhttp=new XMLHttpRequest();
								}

								xmlhttp.onreadystatechange=function()
								{
									if (xmlhttp.readyState==4 && xmlhttp.status==200)
									{
										var resp = xmlhttp.responseText;
										if (resp === "invalid entries")
											alert("There was an error in your log in information");
										else if (resp === \'database error\')
											alert("There was an error in saving your information, please try again later");
										else if (resp === \'success\')
											alert("All information has been saved successfully!");
									}
								}

								xmlhttp.open("POST","http://'.$serverlocation.'/viewaccount.php",true);
								xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
								
								var sendstring = "saveinfo=1&id=" + id + "&course=" + courseinfo;
								sendstring += "&displayname=" + displayname + "&jobtitle=" + jobtitle;
								sendstring += "&address=" + address + "&tel=" + tel + "&fax=" + fax + "&officehours=" + officehours;
								sendstring += "&bio=" + bio + "&research=" + research + "&awards=" + awards;
								
								xmlhttp.send(sendstring);
							}';
				// [MAN.01]			
				print '		function saveInfo(){
								displayname = document.getElementById("displayname").value;
								jobtitle = document.getElementById("jobtitle").value;
								address = document.getElementById("address").value;
								tel = document.getElementById("tel").value;
								fax = document.getElementById("fax").value;
								bio = document.getElementById("bio").value;
								research = document.getElementById("research").value;
								awards = document.getElementById("awards").value;
								officehours = document.getElementById("officehours").value;
								
								courseinfo = parseCourses();
								postInfo();
							}';
							
				print'		</script
						</body>
					</html>';
			} 
			else if ($this->context == 'mysite'){
				$image = 'images/picture.jpg';
				if (file_exists('resources/'.$this->id.'.jpg'))
					$image = 'resources/'.$this->id.'.jpg';
				print '<html>
	<head>
		<title>'.$this->model->getName($this->id).' @ Boston University';
				print '</title>

		<link type="text/css" rel="stylesheet" href="style.css" />				
	</head>
	<body bgcolor="#EEEEEE">
		<div id="header">
			<div class="toplink">
				<a href="viewaccount.php?id='.$this->id.'">MyManage</a>
				<a href="login.php">Logout</a>
			</div>
		</div>
		
		<div id="mysite">
			<table>
				<tr>
					<td>';
					// [PHOTO.05]
					print '<div class="crop" style="width:200px;height:300px;overflow:hidden;">
							<img src="'.$image.'" height="300px"';
				if (!($image === 'images/picture.jpg'))
					// [PHOTO.06]
					print 'style="position:relative;left:100%;margin-left:-200%;"';			
				print '>
						</div>
					</td>
				</tr>
			</table>
			<div class="sidebar">
				<div class="weblink">
					<div class="linkbox"><a href="viewaccount.php?id=123&mysite=1">Home</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&teaching=1">Teaching</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&research=1">Research</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&awards=1">Awards</a></div><br>        
				</div>
			</div>
			<div class="maincontent">
				<span class="name">'.$this->model->getName($this->id).'</span><br/>
				<span class="title">'.$this->model->getJobTitle($this->id).'</span><br/>
				<hr noshade width="440px" align="left"/>
				<h5>Contact Information:</h5>
				<span class="phone">'.$this->model->getAddress($this->id).'
					<br/>
					Tel: '.$this->model->getPhone($this->id).', Fax: '.$this->model->getFax($this->id).'<br/>
					<img src="email_img.php?user='.$this->model->getEmail($this->id).'">
					<h5>Office Hours:</h5>'.$this->model->getOfficeHours($this->id).'
				</span>
				<h5>Short Biography:</h5>
				<span class="nlink" style=" font-size:12px; text-align:justify">
					'.$this->model->getBio($this->id).'
				</span>
			</div>
		</div>
		
		<div id="footer"></div>
	</body>
</html>';
			}
			else if ($this->context == 'teaching'){
				print '<html>
	<head>
		<title>'.$this->model->getName($this->id).' @ Boston University</title>

		<link type="text/css" rel="stylesheet" href="style.css" />				
	</head>
	<body bgcolor="#EEEEEE">
		<div id="header">
			<div class="toplink">
				<a href="viewaccount.php?id='.$this->id.'">MyManage</a>
				<a href="login.php">Logout</a>
			</div>
		</div>
		
		<div id="mysite">
			<div class="sidebar">
				<div class="weblink">
					<div class="linkbox"><a href="viewaccount.php?id=123&mysite=1">Home</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&teaching=1">Teaching</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&research=1">Research</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&awards=1">Awards</a></div><br>        
				</div>
			</div>
			<div class="maincontent">
				<span class="class">Course List:</span><br/>
				<hr/>
				<span class="nlink" style="font-size:12px;">';
				$classes = explode(';', $this->model->getTeaching($this->id));
				foreach ($classes as $value){
					if ($value == '')
						continue;
					$class = explode('~', $value);
					print $class[0].' - '.$class[1].'<br/><br/>';
				}
				print '</span>
			</div>
		</div>
		
		<div id="footer"></div>
	</body>
</html>';
			}
			else if ($this->context == 'research'){
				print '<html>
	<head>
		<title>'.$this->model->getName($this->id).' @ Boston University</title>

		<link type="text/css" rel="stylesheet" href="style.css" />				
	</head>
	<body bgcolor="#EEEEEE">
		<div id="header">
			<div class="toplink">
				<a href="viewaccount.php?id='.$this->id.'">MyManage</a>
				<a href="login.php">Logout</a>
			</div>
		</div>
		
		<div id="mysite">
			<div class="sidebar">
				<div class="weblink">
					<div class="linkbox"><a href="viewaccount.php?id=123&mysite=1">Home</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&teaching=1">Teaching</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&research=1">Research</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&awards=1">Awards</a></div><br>        
				</div>
			</div>
			<div class="maincontent">
				<span class="class">Research:</span><br/>
				<hr/>
				<h5>Research Summary:</h5>
				<span class="nlink" style="font-size:12px; text-align:justify">
					'.$this->model->getResearch($this->id).'
				</span>
			</div>
		</div>
		
		<div id="footer"></div>
	</body>
</html>';
			}
			else if ($this->context == 'awards'){
				print '<html>
	<head>
		<title>'.$this->model->getName($this->id).' @ Boston University</title>

		<link type="text/css" rel="stylesheet" href="style.css" />				
	</head>
	<body bgcolor="#EEEEEE">
		<div id="header">
			<div class="toplink">
				<a href="viewaccount.php?id='.$this->id.'">MyManage</a>
				<a href="login.php">Logout</a>
			</div>
		</div>
		
		<div id="mysite">
			<div class="sidebar">
				<div class="weblink">
					<div class="linkbox"><a href="viewaccount.php?id=123&mysite=1">Home</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&teaching=1">Teaching</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&research=1">Research</a></div><br>
					<div class="linkbox"><a href="viewaccount.php?id=123&awards=1">Awards</a></div><br>        
				</div>
			</div>
			<div class="maincontent">
				<span class="class">Awards:</span><br/>
				<hr/>
				<span class="nlink" style="font-size:12px; text-align:justify">
					'.$this->model->getAwards($this->id).'
				</span>
			</div>
		</div>
		
		<div id="footer"></div>
	</body>
</html>';
			}
			else if ($this->context == 'setserver'){
				if ($this->invalidentries)
					print 'invalid entries';
				else if ($this->dataerror)
					print 'database error';
				else
					print 'success';
			}
			else if ($this->context == 'settings'){
				print '<!DOCTYPE html>
<html>
	<head>
		<title>Admin Settings</title>
		<link type="text/css" rel="stylesheet" href="style.css" />
	</head>';
				if ($this->invalidentries){
					print '<script>
					alert("All fields must be filled out to change username/password");
					</script>';
				} else if ($this->nomatch){
					print '<script>
					alert("Passwords must match!");
					</script>';
				} else if ($this->dataerror){
					print '<script>
					alert("Could not connect to the database");
					</script>';
				} else if ($this->successchange){
					print '<script>
					alert("Successfully changed the admin username/password");
					</script>';
				}
				print '
	<body bgcolor="#EEEEEE">
		<div id="header">
			<div class="toplink">
				<a href="viewaccount.php?emails=1&amp;id=admin">Emails</a>
				<a href="viewaccount.php?users=1&amp;id=admin">Users</a>
				<a href="viewaccount.php?settings=1&amp;id=admin">Settings</a>
				<a href="login.php">Logout</a>
			</div>
		</div>
		
		<div id="adminsettings">
			<div class="adsetmain">
				<h1>Administrator Settings</h1>
				<hr/>
				Mail:<br/>
				<input type="radio" name="server" id="mailserver" onchange="onRadioClick();" ';
				if ($this->model->getMailServerStatus())
					print 'checked';
				print '>Server has the ability to send mail<br/>
				<input type="radio" name="server" onclick="onRadioClick();" ';
				if (!$this->model->getMailServerStatus())
					print 'checked';
				print'>Server does not have the ability to send mail<br/>
				<br/>
				Account Information:<br/>
				Username: '.$this->model->getEmail("admin").'<br/><br/>
				<div id="usernamepass">
					<a onclick="onUserPassClick();" style="text-decoration:underline;">Change Username/Password</a><br/>
				</div>
				Current Quartz Version: 1.0.0<br/>
			</div>
		</div>
		<div id="adminfooter"></div>
		
		<script>
		
function onUserPassClick(){
	document.getElementById("usernamepass").innerHTML = "<form action=\"viewaccount.php\" method=\"post\">New Username:<input type=\"text\" name=\"newuser\"></input><br/>New Password:<input type=\"password\" name=\"newpass\"></input><br/>Confirm Password:<input type=\"password\" name=\"confpass\"></input><br/><input type=\"submit\" name=\"saveuser\" value=\"Save\"></input></form>";
}

function test(){
	document.getElementById("test").innerHTML = "here";
}

function onRadioClick(){
	var yesradio = document.getElementById("mailserver");
	var val = "false";
	if (yesradio.checked){
		val = "true";
	}
	var xmlhttp;

	if (window.XMLHttpRequest)
	{
		xmlhttp=new XMLHttpRequest();
	}

	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			var resp = xmlhttp.responseText;
			if (resp === "invalid entries")
				alert("There was an error in your log in information");
			else if (resp === \'database error\')
				alert("There was an error in saving your information, please try again later");
			else if (resp === \'success\')
				alert("Successfully changed the mail server status!");
		}
	}

	xmlhttp.open("POST","http://'.$serverlocation.'/viewaccount.php",true);
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
															
	xmlhttp.send("server=" + val);
}
		
		</script>
	</body>

</html>';
			}
			else if($this->context == 'emailview'){
				print('<!DOCTYPE html>
<html>
	<head>
		<title>Email Notifications</title>
		<link type="text/css" rel="stylesheet" href="style.css" />	    
	</head>
	<body bgcolor="#EEEEEE">');
	if($this->invalidentries)
	{
		print('<script> alert("Invalid Email") </script>');
	}
	else if( $this->dataerror)
	{
		print('<script> alert("Cannot connect to database") </script>');
	}
	else if( $this->successchange)
	{
		print('<script> alert("Successfully removed the email!") </script>');
	}
	print('
		<div id="header">
			<div class="toplink">
				<a href="viewaccount.php?emails=1&amp;id=admin">Emails</a>
				<a href="viewaccount.php?users=1&amp;id=admin">Users</a>
				<a href="viewaccount.php?settings=1&amp;id=admin">Settings</a>
				<a href="login.php">Logout</a>
			</div>
		</div>
		<div id="adminother">
			<div class="adsetmain">
				
				

		<h1>Email Notifications </h1>');
		$emails = $this->model->getEmails();
		for($i = 0; $i < count($emails); $i++)
		{
			print('To: '. $emails[$i][1].'
			<form method ="post" action = "viewaccount.php">
			<input id = "remove" type="submit" name = "removeEmail" value="Remove">
			<input type = "hidden" name= "emailID" value = "'.$emails[$i][0].'"></form><br/>
			Subject: '.$emails[$i][2].'<br/><br/>
			'.$emails[$i][3].'<br/><br/>');
			
		}
		
		print('
		<br/><br/><br/><br/><br/>
				
				
				
			</div>
		</div>
		<div id="adminfooter"></div>
	</body>
</html>');
			}
			else if($this->context == 'adminuser'){
				print('<!DOCTYPE html>
<html>
	<head>
		<title>User Administration Panel</title>
		<link type="text/css" rel="stylesheet" href="style.css" />	    
	</head>
	<body bgcolor="#EEEEEE">');
	if($this->invalidentries)
	{
		print('<script> alert("Invalid User") </script>');
	}
	else if( $this->dataerror)
	{
		print('<script> alert("Cannot connect to database") </script>');
	}
	else if( $this->successchange)
	{
		print('<script> alert("Action successful!") </script>');
	}
	print('<div id="header">
			<div class="toplink">
				<a href="viewaccount.php?emails=1&amp;id=admin">Emails</a>
				<a href="viewaccount.php?users=1&amp;id=admin">Users</a>
				<a href="viewaccount.php?settings=1&amp;id=admin">Settings</a>
				<a href="login.php">Logout</a>
			</div>
		</div>
		<div id="adminother">
			<div class="adsetmain">
				
				
			<h1>User Administration Panel </h1>
<table id= “myTable” border="1">
<tr>
  <td>User email</td>
  <td>Created</td> 
  <td>Activated</td>
</tr>');

$users= $this->model->getUsers();
for($i = 0; $i < count($users) ; $i++)
{
	print('<tr><td>'.$users[$i][1].'</td>');
	if($users[$i][0] != 'admin')
	{
		print('
		  
		  <td> <form method ="post" action = "viewaccount.php">
		  <input id = "del" type="submit" name = "delete" value="Delete"><input type = "hidden" name= "userID" value = "'.$users[$i][1].'"></form></td>
		  <td>  <form method ="post" action = "viewaccount.php">
		<input id = "dea" type="submit" name = "deauthorize" value="De-authorize"><input type = "hidden" name= "userID" value = "'.$users[$i][1].'"></form></td>');
	}	
	print('</tr>');

}
print('
</table>
<table border = "1">
<tr>
 <form method ="post" action = "viewaccount.php">

<td><input id = "add" type="submit" name = "addemail" value="Add"></td> 
<td><input id = "email" type="text" name="newEmail"></td></form>
</tr>
</table><br/><br/>
				
			
				
			</div>
		</div>
		<div id="adminfooter"></div>
	</body>
</html>');
			}
		}
		
	}
	
	class LoginActivity{
	
		private $context;
	
		function __construct(){
			$this->getInput();
		}
		
		public function run(){
			$this->process();
			$this->show();
		}
		
		private function getInput(){
		
		}
		
		private function process(){
		
		}
		
		private function show(){
			print "<html>
			<body>
			You are now at the Login page
			</body>
			</html>
			";
		}
		
	}

	class InstallActivity{
	
		private $context;
		private $model;
		
		private $adminuser;
		private $adminpass;
		private $user;
		private $pass;
		private $database;
		
		private $invaliduser = false;
		private $invalidpass = false;
		private $faildatabase = false;
		private $importerror = false;
		
		function __construct(){
			$this->model = new Model();
			
			if (isset($_GET['import'])){
				$this->context = "import";
			} else {
				$this->context = "setup";
			}
			
			$this->getInput();
		}
		
		private function getInput(){
			if ($this->context == "setup"){
				if (isset($_GET['username'])){
					$this->adminuser = $_GET['username'];
				}
				if (isset($_GET['password'])){
					$this->adminpass = $_GET['password'];
				}
			} else if ($this->context == 'import'){
				if (isset($_GET['username']))
					$this->user = $_GET['username'];
				if (isset($_GET['password']))
					$this->pass = $_GET['password'];
				if (isset($_GET['database']))
					$this->database = $_GET['database'];
			}
		}
		
		public function run(){
			$this->process();
			$this->show();
		}
		
		private function process(){
			if ($this->context == "setup"){
				if ($this->adminuser == Null or $this->adminuser == ''){
					$this->invaliduser = true;
				}
				if ($this->adminpass == NULL or $this->adminpass == ''){
					$this->invalidpass = true;
				}
				if (!$this->invaliduser and !$this->invalidpass)
					if (!$this->model->createAdmin($this->adminuser, md5($this->adminpass)))
						$this->faildatabase = true;
			} if ($this->context == 'import'){
				if ($this->user == null or $this->user == ''){
					$this->importerror = true;
				}
				if ($this->pass == null)
					$this->pass = "";
				if ($this->database == null or $this->database == '')
					$this->importerror = true;
				if (!$this->importerror){
					if (!$this->model->importData($this->user, $this->pass, $this->database))
						$this->importerror = true;
				}
			}
		}
		
		private function show(){
			if ($this->context == 'setup'){
				if ($this->invaliduser or $this->invalidpass or $this->faildatabase){
					print '<html><body>Error</body></html>';
				} else 
					print '<html><body>Success</body></html>';
			} else if ($this->context == 'import'){
				if ($this->importerror)
					print '<html><body>Error</body></html>';
				else 
					print "<html><body>Success</body></html>";
			}
		}
	}

	class UninstallActivity{
		
		private $context;
		private $model;
		
		private $databasefail = false;
		
		function __construct(){
			$this->model = new Model();
			
			$this->getInput();
			$this->context = "uninstall";
		}
		
		private function getInput(){}
		
		private function process(){
			if ($this->context == 'uninstall'){
				if(!$this->model->removeData())
					$this->databasefail = true;
			}
		}
		
		private function show(){
			if ($this->context == 'uninstall'){
				if ($this->databasefail){
					print "<html><body>Error</body></html>";
				} else
					print "<html><body>Success</body></html>";
			}
		}
		
		public function run(){
			$this->process();
			$this->show();
		}
	}
	
?>
