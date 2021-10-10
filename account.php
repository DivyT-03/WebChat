<?php
// Login PHP
if ($_POST["function"]=="login"){
	$conn = new mysqli("localhost","root","","ourchat");
	if ($conn->connect_error) {
		die("ERROR");
	}
	// getting the value of name field
	$uname = $_POST["uname"];
	$pass = $_POST["pass"];

	//Requesting Data
	$sql = "SELECT * FROM Users where username='".$uname."'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		
		// output data of each row
		while($row = $result->fetch_assoc()) {
			if ($pass==$row["password"]){
				
				//Creating login session
				session_start();
				$_SESSION['my_login_var']=$row["username"];
				
				//Fetching Table Ids for the chats of this user
				$query = "SELECT * FROM chats where user_1=".$row["user_id"]." or user_2=".$row["user_id"];
				$chats = $conn->query($query);
				
				//If user has some chats
				if ($chats->num_rows > 0) {
					$JSON = new stdClass();
					//Accessing each chat one by one
					while($chat = $chats->fetch_assoc()) {
						
						//Getting chat details
						$table_id=$chat["table_id"];
						$this_user=$row["user_id"];
						$other_user = $row["user_id"] == $chat["user_1"] ? $chat["user_2"] : $chat["user_1"];
						
						//Fetching username of other user
						$fetch_uname = "SELECT * FROM Users where user_id=".$other_user;
						$others = $conn->query($fetch_uname);
						if ($others->num_rows > 0) {
							// output data of each row
							while($other = $others->fetch_assoc()) {
								//Storing the username of the other user
								$other_username=$other["username"];
							}
						} 
						
						//Fetch Messages from the table of the user
						$fetch_messages = "SELECT * FROM `".$table_id."`";

						$messages = $conn->query($fetch_messages);
						
						//Declaring empty array to store messages
						$arr=array();
						
						//If messages present then iterating them
						if ($messages->num_rows > 0) {
							// output data of each row
							while($message = $messages->fetch_assoc()) {
								$Obj = new stdClass();
								//Storing the username of the other user
								$Obj->sender=$message["sender"];
								$Obj->id=$message["message_id"];
								$Obj->receiver=$message["receiver"];
								$Obj->msg=$message["message"];
								$Obj->timestamp=$message["timestamp"];
								$Obj->status=$message["status"];
								
								//Pushing the values in the array
								array_push($arr,$Obj);
							}
						}
						else{}

						$JSON->{$other_username}=$arr;
						
					}
					echo json_encode($JSON);
				} 
				else {
					//No chats for the user
					echo "NO CHATS";
				}
			}
			else{
				echo 'ERROR';
			}
		}
	} 
	else {
		echo 'ERROR';
	}
	$result->close();
	$conn->close();
}

//SignUp PHP
else if ($_POST["function"]=="signup"){
	$conn = new mysqli("localhost","root","","ourchat");
	if ($conn->connect_error) {
		die("ERROR");
	}
	// getting the value of name field
	$uname = $_POST["uname"];
	$pass = $_POST["pass"];
	$email = $_POST["email"];
	$name = $_POST["name"];
	$dob = $_POST["dob"];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      echo 'ERROR';$conn->close();die('');
    }
    else{
    	$sql = "SELECT password FROM Users where email='".$email."'";
    	$result = $conn->query($sql);
    	$sql_1 = "SELECT password FROM Users where username='".$uname."'";
    	$result_1 = $conn->query($sql_1);
    
    	if ($result->num_rows > 0) {
    		echo 'ERROR';$conn->close();die('');
    	}
    	else if ($result_1->num_rows > 0){
    		echo 'ERROR';$conn->close();die('');
    	}
    	else{
        		$query="insert into Users(username, name, email, password, dob) values ('".$uname."','".$name."','".$email."','".$pass."','".$dob."')";
        		$stmt=$conn->prepare($query);
        		$stmt->execute();
        		$stmt->close();
    			$conn->close();
				session_start();
				$_SESSION['my_login_var']=$uname;
        		//No chats for the user
				echo "NO CHATS";
    	}
    }
} 

//Forgot Password PHP
else if ($_POST["function"]=="forgot"){
	$conn = new mysqli("localhost","root","","ourchat");
	if ($conn->connect_error) {
		die("ERROR");
	}
	// getting the value of name field
	$uname = $_POST["uname"];

	//Requesting Data
	$sql = "SELECT username,password FROM Users where username='".$uname."'";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$username=$row["username"];
			$password=$row["password"];
			//Show user the password somehow
		    //echo "Password has been sent to your registered email address";
		}
	} 
	else {
		echo 'ERROR';
	}
	$result->close();
	$conn->close();
} 

//Add friend PHP
else if ($_POST["function"]=="add"){
	$conn = new mysqli("localhost","root","","ourchat");
	if ($conn->connect_error) {
		die("ERROR");
	}
	// getting the value of name field
	$uname = $_POST["uname"];

	//Requesting Data
	$sql = "SELECT username FROM Users where username='".$uname."'";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			
			//Checking if the user is logged in
			session_start();
			if(isset($_SESSION["my_login_var"])){
				
				//Fetching user_id for the users
				$sql = "SELECT * FROM Users where username='".$uname."'";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					
					// output data of each row
					while($row = $result->fetch_assoc()) {
						$user_1_id=$row["user_id"];
					}
				}
				$sql = "SELECT * FROM Users where username='".$_SESSION["my_login_var"]."'";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					
					// output data of each row
					while($row = $result->fetch_assoc()) {
						$user_2_id=$row["user_id"];
					}
				}
				
				//Creating entry for the users in chats table
				$query="INSERT INTO `chats`(`user_1`, `user_2`) VALUES ('".$user_1_id."','".$user_2_id."')";
        		$stmt=$conn->prepare($query);
        		$stmt->execute();
        		$stmt->close();
				
				//
				$sql = "SELECT table_id FROM `chats` where `user_1`='".$user_1_id."' and `user_2`='".$user_2_id."'";
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					
					// output data of each row
					while($row = $result->fetch_assoc()) {
						$table_id=$row["table_id"];
					}
				}
				
				//Creating Messages table for the users
				$query="CREATE TABLE `".$table_id."` (
				  `message_id` int(11) AUTO_INCREMENT PRIMARY KEY,
				  `sender` VARCHAR(50) NOT NULL,
				  `receiver` VARCHAR(50) NOT NULL,
				  `message` VARCHAR(1000) NOT NULL,
				  `timestamp` int(11) NOT NULL,
				  `status` int(11) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
				$stmt=$conn->prepare($query);
        		$stmt->execute();
        		$stmt->close();
				
				echo $uname;
			}
			else{
				echo "ERROR";
			}
		}
	} 
	else {
		echo 'ERROR';
	}
	$result->close();
	$conn->close();
} 

//Signout PHP
else if ($_POST["function"]=="signout"){
	session_start();
	if(isset($_SESSION["my_login_var"])){
		//Removing user session
		unset($_SESSION["my_login_var"]);
		echo "success";
	}
	else{
		echo "ERROR";
	}
}

else if ($_POST["function"]=="message_add"){
	echo "Helllo";
	$conn = new mysqli("localhost","root","","ourchat");
	if ($conn->connect_error) {
		die("ERROR");
	}
	// getting the value of name field
	$timestamp = $_POST["timestamp"];
	$receiver = $_POST["receiver"];
	$message = $_POST["message"];
	$sender = $_POST["sender"];
	
	//Getting User Ids
	$sql = "SELECT * FROM `Users` where `username`='".$sender."'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$user_1=$row["user_id"];
		}
	}
	
	$sql = "SELECT * FROM `Users` where `username`='".$receiver."'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$user_2=$row["user_id"];
		}
	}
	
	//Getting table_id
	$sql = "SELECT table_id FROM `chats` where `user_1`=".$user_1." and `user_2`=".$user_2;
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$table_id_1=$row["table_id"];
		}
	}
	
	$sql = "SELECT table_id FROM `chats` where `user_2`='".$user_1."' and `user_1`='".$user_2."'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$table_id_2=$row["table_id"];
		}
	}
	
	$table_id= isset($table_id_2) ? $table_id_2 : $table_id_1;

	//Creating entry for the users in chats table
	$query="INSERT INTO `".$table_id."`(`sender`, `receiver`, `message`, `timestamp`, `status`) VALUES ('".$sender."','".$receiver."','".$message."',".$timestamp.",1)";
    $stmt=$conn->prepare($query);
    $stmt->execute();
    $stmt->close();
	$conn->close();
} 

//Fetch Data
else if ($_POST["function"]=="fetch"){
	$conn = new mysqli("localhost","root","","ourchat");
	if ($conn->connect_error) {
		die("ERROR");
	}
	
	//Creating login session
	session_start();
	// getting the value of name field
	$uname = $_SESSION["my_login_var"];

	//Requesting Data
	$sql = "SELECT * FROM Users where username='".$uname."'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		
		// output data of each row
		while($row = $result->fetch_assoc()) {
				
				//Fetching Table Ids for the chats of this user
				$query = "SELECT * FROM chats where user_1=".$row["user_id"]." or user_2=".$row["user_id"];
				$chats = $conn->query($query);
				
				//If user has some chats
				if ($chats->num_rows > 0) {
					$JSON = new stdClass();
					//Accessing each chat one by one
					while($chat = $chats->fetch_assoc()) {
						
						//Getting chat details
						$table_id=$chat["table_id"];
						$this_user=$row["user_id"];
						$other_user = $row["user_id"] == $chat["user_1"] ? $chat["user_2"] : $chat["user_1"];
						
						//Fetching username of other user
						$fetch_uname = "SELECT * FROM Users where user_id=".$other_user;
						$others = $conn->query($fetch_uname);
						if ($others->num_rows > 0) {
							// output data of each row
							while($other = $others->fetch_assoc()) {
								//Storing the username of the other user
								$other_username=$other["username"];
							}
						} 
						
						//Fetch Messages from the table of the user
						$fetch_messages = "SELECT * FROM `".$table_id."`";
						$messages = $conn->query($fetch_messages);
						
						//Declaring empty array to store messages
						$arr=array();
						
						//If messages present then iterating them
						if ($messages->num_rows > 0) {
							// output data of each row
							while($message = $messages->fetch_assoc()) {
								$Obj = new stdClass();
								//Storing the username of the other user
								$Obj->sender=$message["sender"];
								$Obj->id=$message["message_id"];
								$Obj->receiver=$message["receiver"];
								$Obj->msg=$message["message"];
								$Obj->timestamp=$message["timestamp"];
								$Obj->status=$message["status"];
								
								//Pushing the values in the array
								array_push($arr,$Obj);
							}
						}
						else{}

						$JSON->{$other_username}=$arr;
						
					}
					echo json_encode($JSON);
				} 
				else {
					//No chats for the user
					echo "NO CHATS";
				}
		}
	} 
	else {
		echo 'ERROR';
	}
	$result->close();
	$conn->close();
}
?>