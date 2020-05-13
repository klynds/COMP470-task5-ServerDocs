<?php
require_once('Template.php');
include 'ChromePhp.php';
define('TEMPLATES_PATH', 'templates');

$template = new Template('index.html'); // instantiates new object
$bodyquiz = "";

if (isset($_GET['page'])) { // determines whether the page variable is set
    $prop_id = $_GET['page'];
}else{
    $prop_id = 1;
}

$intro = "";
$body = "";
$questionQuiz = [];
$answerQuiz = [];
$summaryPart = [];
$lessonUnit = []; // array of arrays containign <Info> <Markdown> and <Example>
$lessoneml = "";

if (isset($_POST['lessoneml'])){
    $_SESSION['lessoneml1'] = $_POST['lessoneml'];
    return;
}

if (isset($_POST['quizeml'])){
    $_SESSION['quizeml1'] = $_POST['quizeml'];
    return;
}

$xml = simplexml_load_string($_SESSION['lessoneml1']);
$intro = $xml->attributes()->{'Intro'};
foreach ($xml->Part as $Part) {
    array_push($summaryPart, array("partsummary" => $Part->Summary));
    foreach ($Part->Lesson as $Lesson) {
        $lesson = array(
            "info" => (string)$Lesson->Info,
            "markup" =>(string)$Lesson->Markup,
            "example" =>(string)$Lesson->Example);
        array_push($lessonUnit, $lesson);
    }
};

$controls = <<< HEREDOC
    <div class="innertube" id="navtube"></div>
HEREDOC;

switch ($prop_id) { // parses xml data from array into html document
    case null:
    case 1:
        $pag = '<div class="pagination">
        <a class=active href="Unit1.php?page=1">1</a>
        <a href="Unit1.php?page=2">2</a>
        <a href="Unit1.php?page=3">3</a>
        <a href="Unit1.php?page=4">Quiz</a>
        </div>';
        for ($x = 0; $x < 3; $x++) { // iterates the required lessons in part 1 of the Unit
            $body.='<p style="margin: 20px;">' . $lessonUnit[$x]["info"];
            $body.='<br><br><b>Markup:</b><br><br><img src=' . $lessonUnit[$x]["markup"] .'>'; // MIGHT NEED QUOTES HERE FOR FILEPATH
            $body.='<br><br><br><b>Result:</b><br><br><img src=' . $lessonUnit[$x]["example"] .'></p>';
        }
        break;
    case 2:
        $pag = '<div class="pagination">
        <a href="Unit1.php?page=1">1</a>
        <a class=active href="Unit1.php?page=2">2</a>
        <a href="Unit1.php?page=3">3</a>
        <a href="Unit1.php?page=4">Quiz</a>
    </div>';
        for ($x = 3; $x < 6; $x++) { // iterates the required lessons in part 2 of the Unit
            $body.='<p style="margin: 20px;">' . $lessonUnit[$x]["info"];
            $body.='<br><br><b>Markup:</b><br><br><img src=' . $lessonUnit[$x]["markup"] .'>'; // MIGHT NEED QUOTES HERE FOR FILEPATH
            $body.='<br><br><br><b>Result:</b><br><br><img src=' . $lessonUnit[$x]["example"] .'></p>';
        }
        break;
    case 3:
        $pag = '<div class="pagination">
        <a href="Unit1.php?page=1">1</a>
        <a href="Unit1.php?page=2">2</a>
        <a class=active href="Unit1.php?page=3">3</a>
        <a href="Unit1.php?page=4">Quiz</a>
    </div>';
        for ($x = 7; $x < 9; $x++) { // iterates the required lessons in part 3 of the Unit
            $body.='<p style="margin: 20px;">' . $lessonUnit[$x]["info"];
            $body.='<br><br><b>Markup:</b><br><br><img src=' . $lessonUnit[$x]["markup"] .'>'; // MIGHT NEED QUOTES HERE FOR FILEPATH
            $body.='<br><br><br><b>Result:</b><br><br><img src=' . $lessonUnit[$x]["example"] .'></p>';
        }
        break;
    case 4:
        $pag = '<div class="pagination">
        <a href="Unit1.php?page=1">1</a>
        <a href="Unit1.php?page=2">2</a>
        <a href="Unit1.php?page=3">3</a>
        <a class=active href="Unit1.php?page=4">Quiz</a>
        </div>';

        // pull XML from database

        $xml = simplexml_load_string($_SESSION['quizeml1']);
        $intro = $xml->attributes()->{'Intro'};

        foreach ($xml->Question as $Question) {
            array_push($answerQuiz, $Question->Answer);
            $q = array(
                "prompt" => (string)$Question->Prompt,
                "a" => (string)$Question->A,
                "b" =>(string)$Question->B,
                "c" =>(string)$Question->C,
                "d" =>(string)$Question->D);
            array_push($questionQuiz, $q);
        };
        $cookie_name = 'answers';
        setcookie($cookie_name, implode(" ",$answerQuiz), time() + (86400 * 30), '/'); // 86400 = 1 day

        $body = <<<EOT
            <section>
                
                <div id="grade"></div>
                <form name="formQuiz">            
                          {QUESTION}
                      <img id="submitimg" src="./shared/img/submit.png" onclick="submitQuiz()">
                </form>
            </section>
EOT;
        $summary="";
        $bodyquiz = "";
        for($i=1; $i<=sizeOf($answerQuiz); $i++){ // populates the quiz with using the array of questions in '$quiz'
            $question = '<div id='."head".strval($i).' style="align-content: flex-end"><h5>QUESTION</h5></div>
                    <input type="radio" name='."Q".strval($i).' value="a" id=='."Q".strval($i)."a".'><b> a.</b> A1<br>
                    <input type="radio" name='."Q".strval($i).' value="b" id=='."Q".strval($i)."b".'><b> b.</b> A2<br>
                    <input type="radio" name='."Q".strval($i).' value="c" id=='."Q".strval($i)."c".'><b> c.</b> A3<br>
                    <input type="radio" name='."Q".strval($i).' value="d" id=='."Q".strval($i)."d".'><b> d.</b> A4<br>
                    <br>';
            $question = str_replace("QUESTION",$questionQuiz[$i-1]['prompt'],$question);
            $question = str_replace("A1",$questionQuiz[$i-1]['a'],$question);
            $question = str_replace("A2",$questionQuiz[$i-1]['b'],$question);
            $question = str_replace("A3",$questionQuiz[$i-1]['c'],$question);
            $question = str_replace("A4",$questionQuiz[$i-1]['d'],$question);
            $bodyquiz = $bodyquiz . $question;
        }

        break;
} // end switch


$script = /** @lang JavaScript */
    <<< HEREDOC

     $(document).ready(function() {
        $('#navtube').html(
        '<br><div id="lessontitle"><strong>'+sessionStorage.getItem("authUser").charAt(0).toUpperCase() + sessionStorage.getItem("authUser").slice(1)+'\'s Lessons:</strong></div><br>'+
        '<ul>'+
        '<li><a href="Unit1.php"><strong>Lesson 1: Introduction to the Web, HTML5, and CSS</strong></a></li><br>'+
        '<li><a href="Unit2.php"><strong>Lesson 2: Introduction to Client-side Scripting in JavaScript</strong></a></li><br>'+
        '<li><a href="Unit3.php"><strong>Lesson 3: Introduction to XML and Ajax</strong></a></li><br>'+
        '</ul>');
        
       
        
    });

    function retrieve_cookie(name) {
      var cookie_value = "",
        current_cookie = "",
        name_expr = name + "=",
        all_cookies = document.cookie.split(';'),
        n = all_cookies.length;
      for(var i = 0; i < n; i++) {
        current_cookie = all_cookies[i].trim();
        if(current_cookie.indexOf(name_expr) == 0) {
          cookie_value = current_cookie.substring(name_expr.length, current_cookie.length);
          break;
        }
      }
      return cookie_value;
    }
    
    function submitQuiz() {
        
        var cookie_name = 'answers';
        var res = retrieve_cookie(cookie_name);
        console.log("res: "+res);
        let solutions = res.replace( /[^a-z]/g, '').split('');
        console.log("solutions: "+solutions);

        let studentAnswers = []; // a,c,b
        let marks = 0;
    
        for(let i = 1; i <= solutions.length; i++) { // retrieves the users answers
            let name = "Q"+i.toString();
            studentAnswers.push(document.forms['formQuiz'][name].value)
        }
    
        for(let i = 0; i < solutions.length; i++) { // form validation
            if(studentAnswers[i] === null || studentAnswers[i] =="") {
                alert('Quiz question #' + (i+1).toString() + ' has not been answered.');
                return false;
            }
        }
    
        for(let i = 0; i < solutions.length; i++) { // marking the quiz
            let name = "head"+(i+1).toString();
            if (studentAnswers[i] == solutions[i]) { // LOWER CASE
                marks++;
                document.getElementById(name).innerHTML+="<span style='color: #39FF14; '><b>✓</b></span>";
            }else {
                document.getElementById(name).innerHTML+="<span style='color: #ff073a; '><b>X</b></span>";
            }
        }
    
        var results = document.getElementById('grade');
        var percent = Math.round(marks/solutions.length*100);
        results.innerHTML = "<span style='color: #F37021;'><h3>Quiz complete: your grade is: "+percent+" % </h3></span><br><br>";
        $('form img').css('display','none');
        return false;  // required to prevent refresh
    }
    
HEREDOC;

ChromePhp::log("page replaement");

$template->assign('script', $script);
$template->assign('summary', $summary);
$template->assign('controls', $controls);
$template->assign('title', 'Lesson 1: Intro to the Web, HTML5, and CSS'); // assign content values
$template->assign('text', 'COMP 470 - Web Server Management<br><br>');
$template->assign('pag', $pag);
$template->assign('intro', $intro);
$template->assign('body', $body);
$template->assign('question', $bodyquiz);
$template->show();









