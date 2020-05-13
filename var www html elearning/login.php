<?php
require_once('Template.php');
include 'ChromePhp.php';

define('TEMPLATES_PATH', 'templates');

$template = new Template('index.html'); // instantiates new object

$intro = '<p>
            This online learning platform has been developed to introduce students to the materials covered in first three units of the course text <strong>"Internet and World Wide Web: How to Program 5th Ed."</strong>
          <br>
          <br>
        </p>';
$body = '<div>
            <div id="unitlist">
                <p>
                    <strong>Lesson 1: </strong>Introduction to the Web, HTML5, and CSS
                </p>
                <p>
                    <strong>Lesson 2: </strong>Introduction to Client-side Scripting in JavaScript
                </p>
                <p>
                    <strong>Lesson 3: </strong>Introduction to XML and Ajax
                    <br>
                    <br>
                </p>
            </div>
            <p>
            </p>
            <p>
                ⮕ Please log in then select a lesson from the left navigation bar to get started.
            </p>
        </div>';


$controls = <<< HEREDOC
    <div class="innertube" id="navtube">
        <div id="formContentLogin">
            <div id="logintext">
                Account Login:
            </div>
            <form id="loginform">
                <input type="text" id="login" class="logininput" placeholder="Login">
                <input type="password" id="password" class="logininput" name="login" placeholder="Password">
                <button type="button" class="navbutton" id="loginbutton" onclick="authAccount()">Log in</button>
            </form>
         
            <div class="formFooter">
                <div>
                    <a class="underlineHover" id="forgot" href="#" onclick="forgotPasswordView()">Forgot Password?</a>
                </div>
                <div>
                    <a class="underlineHover" id="create" href="#" onclick="createAccountView()">Create New Account</a>
                    <br>
                    <br>
                </div>
            </div>
        </div>            
   </div>
HEREDOC;

$script = /** @lang JavaScript */
    <<< HEREDOC
    let forgot = true;
    let create = true;
    let login = true;
    
    $(document).ready(function() {
        if(sessionStorage['authUser'] !== "null"){
            $('#navtube').html(
            '<br><div id="lessontitle"><strong>'+sessionStorage.getItem("authUser").charAt(0).toUpperCase() + sessionStorage.getItem("authUser").slice(1)+'\'s Lessons:</strong></div><br>'+
            '<ul>'+
            '<li><a href="Unit1.php"><strong>Lesson 1: Intro to the Web, HTML5, and CSS</strong></a></li><br>'+
            '<li><a href="Unit2.php"><strong>Lesson 2: Intro to Client-side Scripting in JavaScript</strong></a></li><br>'+
            '<li><a href="Unit3.php"><strong>Lesson 3: Intro to XML and Ajax</strong></a></li><br>'+
            '</ul>');
            $('#rightfooter').show();

        }
    });
    
    function forgotPasswordView(){
        
        if(forgot){  // clicked 'forgot password' from login view
            $('#logintext').html("Account Recovery");
            $('#login').attr("placeholder", "Username");
            $('#password').attr("placeholder", "Email");
            $('#loginbutton').html("Send Recovery Email");
            $('#forgot').html("Return to Login");
            $('#create').attr("hidden", true);
            forgot = false;
        }else{  // clicked 'return to login' from account recovery view
            forgot = true;
            $('#logintext').html("Account Login");
            $('#login').attr("placeholder", "Login");
            $('#password').attr("placeholder", "Password");
            $('#loginbutton').html("Log in");
            $('#forgot').html("Forgot Password?");
            $('#create').attr("hidden", false);
            
        }
    }
    
    function createAccountView(){
        
        if(create){ // clicked 'create account' 
            $('#loginform').html(
                '<input type="text" id="login" class="logininput" placeholder="Username">'+
                '<input type="password" id="password" class="logininput" name="login" placeholder="Password">'+
                '<input type="password" id="password2" class="logininput" name="login" placeholder="Confirm Password">'+
                '<input type="text" id="email" class="logininput" placeholder="Email">'+
                '<input type="text" id="email2" class="logininput" placeholder="Confirm Email">'+
                '<button type="button" class="navbutton" id="loginbutton" onclick="createAccount()">Create Account</button>');
            $('#forgot').attr("hidden", true);
            $('#create').html("Return to Login");
            create = false;
        }else{
            create = true;
            $('#loginform').html(
                '<input type="text" id="login" class="logininput" placeholder="Username">'+
                '<input type="password" id="password" class="logininput" name="login" placeholder="Confirm Password">'+
                '<button type="button" class="navbutton" id="loginbutton">Create Account</button>');
            $('#forgot').attr("hidden", false);
            $('#create').html("Create New Account");
        }
    }
    
    function createAccount(){
         let usr = $('#login').val().toLowerCase();
         let pwd = $('#password').val();
         let pwd2 = $('#password2').val();
         let email = $('#email').val().toLowerCase();
         let email2 = $('#email2').val().toLowerCase();
 
         let validation = ""; // holds error messages for client-side input validation
         if (pwd !== pwd2){
             validation+="Error: Provided passwords do not matchn";
         }else if (pwd.length < 8){
             validation+="Error: Password must be 8+ charactersn";
         }
         if (usr.length < 5){
             validation+="Error: Username must be 5+ charactersn";
         }
         if (email !== email2){
             validation+="Error: Provided emails do not match";
         }
         let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
 
         if (!re.test(String(email))){
             validation+="Error: Email appears to be improperly formatted";
         }
         if (validation.length > 0){
             alert(validation);
             
         }else {
             let requestMedia = $.ajax({
                 url: "http://35.182.255.246:8082", // ec2-35-182-255-246.ca-central-1.compute.amazonaws.com
                 type: 'POST',
                 data: '{"requesttype": "create","user": "'+usr+'", "pass": "'+pwd+'", "mail": "'+email+'"}',
                 success: function (data) {
                     const result = data.substring(1, data.length-1); // TRIMS OFF SOMETHING
                     switch(result){
                         case "username":
                             alert("Error: username already in use");
                             break;
                         case "email":
                             alert("Error: email already in use");
                             break;
                         case "success":
                             alert("Account creation successful");
                             sessionStorage.setItem("authUser", usr);
                            $('#navtube').html(
                            '<br><div id="lessontitle"><strong>'+usr.charAt(0).toUpperCase() + usr.slice(1)+'\'s Lessons:</strong></div><br>'+
                            '<ul>'+
                            '<li><a href="Unit1.php"><strong>Lesson 1: Intro to the Web, HTML5, and CSS</strong></a></li><br>'+
                            '<li><a href="Unit2.php"><strong>Lesson 2: Intro to Client-side Scripting in JavaScript</strong></a></li><br>'+
                            '<li><a href="Unit3.php"><strong>Lesson 3: Intro to XML and Ajax</strong></a></li><br>'+
                            '</ul>');
                             break;
                     }
                 },
                 dataType: "text", // json jsonpCallback: 'callback', // this is not relevant to the POST anymore
                 cache: false,
                 contentType: "text/plain" // "application/json"
             });
         }
    } // end createAccount()
    
    function authAccount(){ 
        if(login){
            let usr = $('#login').val().toLowerCase();
             let pwd = $('#password').val();
             let requestMedia = $.ajax({
                 url: "http://35.182.255.246:8082", // ec2-35-182-255-246.ca-central-1.compute.amazonaws.com
                 type: 'POST',
                 data: '{"requesttype": "login","user": "'+usr+'", "pass": "'+pwd+'"}',
                 success: function (data) {
                     if(data == "true"){
                         alert("Login successful");
                         sessionStorage.setItem("authUser", usr);
                         $('#navtube').html(
                          '<br><div id="lessontitle"><div id="usertitle">'+usr.charAt(0).toUpperCase() + usr.slice(1)+'\'s Lessons:</div></div><br>'+
                            '<ul>'+
                            '<li><a href="Unit1.php"><strong>Lesson 1: Intro to the Web, HTML5, and CSS</a></li><br>'+
                            '<li><a href="Unit2.php"><strong>Lesson 2: Intro to Client-side Scripting in JavaScript</strong></a></li><br>'+
                            '<li><a href="Unit3.php"><strong>Lesson 3: Intro to XML and Ajax</strong></a></li><br>'+
                            '</ul>');
                         initLessonXML("1", "lessoneml", "Unit1.php");
                         initQuizXML("1", "quizeml", "Unit1.php");
                         initLessonXML("2", "lessoneml", "Unit2.php");
                         initQuizXML("2", "quizeml", "Unit2.php");
                         initLessonXML("3", "lessoneml", "Unit3.php");
                         initQuizXML("3", "quizeml", "Unit3.php");
                     }else{
                         alert("Error: invalid login credentials")
                     }
                 },
                 dataType: "text", // json jsonpCallback: 'callback', // this is not relevant to the POST anymore
                 cache: false,
                 contentType: "text/plain" // "application/json"
             });
        }else{
            // send recovery email with nodemailer
        }
    }
    
    function initLessonXML(lessonid, emltype, url){
        let requestMedia = $.ajax({
             url: "http://35.182.255.246:8082", // ec2-35-182-255-246.ca-central-1.compute.amazonaws.com
             type: 'POST',
             data: '{"requesttype": "getxml","lessonid": "'+lessonid+'", "emltype": "'+emltype+'"}',
             success: function (data) {
                 var objJSON = JSON.parse(data);
                 console.log(objJSON.lessoneml);
                 $.ajax({
                    type : "POST",  //type of method
                    url  : url,  //your page
                    data : { lessoneml : objJSON.lessoneml},// passing the values
                    success: function(res){  
                            console.log("sent");
                     }
                 });
             },
             dataType: "text", 
             cache: false,
             contentType: "text/plain" 
         });
    }  // end 'getXML()'

     function initQuizXML(lessonid, emltype, url){
        let requestMedia = $.ajax({
             url: "http://35.182.255.246:8082", // ec2-35-182-255-246.ca-central-1.compute.amazonaws.com
             type: 'POST',
             data: '{"requesttype": "getxml","lessonid": "'+lessonid+'", "emltype": "'+emltype+'"}',
             success: function (data) {
                 var objJSON = JSON.parse(data);
                 console.log(objJSON.quizeml);
                 $.ajax({
                    type : "POST",  
                    url  : url,  
                    data : { quizeml : objJSON.quizeml},// passing the values
                    success: function(res){  
                            console.log("sent");
                     }
                 });
             },
             dataType: "text", 
             cache: false,
             contentType: "text/plain" 
         });
    }  // end 'getXML()'
HEREDOC;

$pag = ""; // prevents the replacement tag from being displayed
$template->assign('title', 'Welcome to the eLearning Portal:'); // assign content values
$template->assign('text', 'COMP 466 - Advanced Technologies for Web-based Systems');
$template->assign('pag', $pag);
$template->assign('script', $script);
$template->assign('summary', "");
$template->assign('intro', $intro);
$template->assign('body', $body);
$template->assign('buttons', $buttons);
$template->assign('controls', $controls);
$template->show();