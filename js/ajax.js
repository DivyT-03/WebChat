	//Opening the modal on opening website
	$(window).on('load', function() {
        $('#myModal').modal('show');
    });
	
	//Angular Starts
	var app=angular.module('myapp',[]);
	app.controller('myctrl',['$scope',function($scope)
	{
		$scope.open='login';
		$scope.start=function(val)
		{$scope.open=val};
		
		//Updating active user
		$scope.func= function(arg){
			$scope.$apply($scope.active=arg)
		}
		
		//Getting time for the messages
		$scope.getTime= function(arg){
			let d = new Date(0);
			d.setUTCSeconds(arg);
			let hours= d.getHours();
            let min= d.getMinutes();
			if (String(min).length==1){
				min="0"+String(min)
			}
			let time= String(hours)+":"+String(min)
			return time
		}
		//Ajax Starts
		
        //Login
		$("#login_submit").on('click', function(){
    	    let uname=document.getElementById("signin_username").value;
			let pass=document.getElementById("signin_password").value;
			if (uname.trim()!="" && pass.trim()!=""){
				$.ajax({  
					type:"POST",  
					url:"account.php",  
					data:"function=login&uname="+uname+"&pass="+pass,  
					success:function(data){ 
						console.log(data);
						if(data=="ERROR"){
							alert("Invalid Login Credentials");
						}
						else{
							if (data=="NO CHATS"){
								//No chats
							}
							else{
								data=JSON.parse(data);
								$scope.$apply($scope.data=data);
								$scope.$apply($scope.user='on');
								$scope.$apply($scope.uname=uname);
								setInterval(fetchdata,5000);
							}
							$('#login_close').click();
						} 
					}
				});
			}
        }); 
		
		//Signup
		$("#signup_submit").on('click', function(){
    	    let name=document.getElementById("signup_name").value;
			let dob=document.getElementById("dob").value;
			let uname=document.getElementById("signup_username").value;
			let email=document.getElementById("signup_email").value;
			let p1=document.getElementById("signup_password").value;
			let p2=document.getElementById("signup_password2").value;
			if (name.trim()=="" || uname.trim()=="" || email.trim()=="" || p1.trim()=="" || p2.trim()==""){
				alert("One or more required fields are empty")
			}
			else if (p1!=p2){
				alert("Passwords do not match!")
			}
			else if ((/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/).test(email)){
				$.ajax({  
					type:"POST",  
					url:"account.php",  
					data:"function=signup&uname="+uname+"&pass="+p1+"&dob="+dob+"&email="+email+"&name="+name,  
					success:function(data){  
						if(data=="ERROR"){
							alert("Some unexpected error occoured, Please Try Again later.");
						}
						else{
							alert("Account Successfully Created");
							if (data=="NO CHATS"){
								$('#signup_close').click();
							}
						} 
					}
				});
			}
			else {
				alert("Please enter a valid email address")
			}
				
        }); 
		
		//Forget Password
		$("#forgot_submit").on('click', function(){
    	    let uname=document.getElementById("forgot_username").value;
			if (uname.trim()!=""){
				$.ajax({  
					type:"POST",  
					url:"account.php",  
					data:"function=forgot&uname="+uname,
					success:function(data){  
						if(data=="ERROR"){
							alert("Some unexpected error occoured, Please Try Again later.");
						}
						else{
							//Reset pass ka jo bhi h
							$('#forgot_close').click();
						} 
					} 
				});
			}
        }); 
		
		//Add Friend
		$("#add_friend").on('click', function(){
    	    let uname=document.getElementById("search_friends").value;
			if (uname.trim()!=""){
				$.ajax({  
					type:"POST",  
					url:"account.php",  
					data:"function=add&uname="+uname,
					success:function(data){ 
						console.log(data)
						if(data=="ERROR"){
							alert("The user does not exist");
						}
						else{
							alert("User "+data+" was added as friend");
							fetchdata();
						} 
					} 
				});
			}
        }); 
		
		//Signout
		$("#sign_out").on('click', function(){
				$.ajax({  
					type:"POST",  
					url:"account.php",  
					data:"function=signout",
					success:function(data){ 
						console.log(data)
						if(data=="success"){
							$('#myModal').modal('show');
							$scope.$apply($scope.user='off');
						}
						else {
							alert("Some unexpected error occoured");
						} 
					} 
				});
        }); 
		
			//Fetch messages at regular intervals
			function fetchdata(){
			 $.ajax({  
					type:"POST",  
					url:"account.php",  
					data:"function=fetch",  
					success:function(data){ 
						console.log(data);
							if (data=="NO CHATS"){
								//No chats
							}
							else{
								data=JSON.parse(data);
								$scope.$apply($scope.data=data);
							}
					}
				});
			}
		
			//Send Message
			var new_msgs=1;
			
			$("body").on('click', "div.dynamicElement > div.fixed-bottom > span.submit", function(event) {
			//$(".submit").on('click', function(event){
				//Preventing function call multiple times
                //event.stopPropagation();
                //event.stopImmediatePropagation();
				
                //Checking which chat is active
                let active_chat=$scope.active;
				
                //Getting the message
                let message=document.getElementById(active_chat+'_msg').value;
				
				//Sending the message to the database
                if (message.trim()!=""){
                    //1. Adding message to the chat screen with the clock sign
					
					//Getting the Unix epoch 
                    let ts = Math.round((new Date()).getTime() / 1000);
					
					let new_msg_id='new_msg'+String(new_msgs);
					new_msgs=new_msgs+1;
					
					//Creating the message to be appended
                    var add_msg='<div id="'+new_msg_id+'" class="chat_mess chat_mess1"> \
        	 			'+message+'\
        	 			<span class="time">'+$scope.getTime(ts)+'</span> \
        	 			<span id="msg_status"><i class="far fa-clock"></i></span> \
        	 		</div>'

					//Appending the message to chat screen
                    $("#"+$scope.active).append(add_msg)
					
					//Emptying the text area
					document.getElementById(active_chat+'_msg').value="";

					//Create an entry in database
                	$.ajax({  
                	    type:"POST",  
                	    url:"account.php",  
                	    data:"function=message_add&message="+message+"&receiver="+$scope.active+"&sender="+$scope.uname+"&timestamp="+ts,  
                	    success:function(data){  
							console.log(data);
							$('#'+new_msg_id).remove();
							fetchdata();
                	    } 
                	});
 
                }
            });
			

			
	//Angular Ends
	}]);
