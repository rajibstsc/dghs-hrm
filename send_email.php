
<?php
//error_reporting(E_ALL);
//ini_set('display_errors','On');
if ($_POST["email"]<>'') {
     
    $EmailSubject = $_POST["subject"];
	$ToEmail = $_POST["mailto"];
	$mailheader = "From: ".$_POST["email"]."\r\n";
    $mailheader .= "Reply-To: ".$_POST["email"]."\r\n";
	$mailheader = "CC: ".$_POST["emailcc"]."\r\n";
    $mailheader .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $MESSAGE_BODY = "Name: ".$_POST["name"]."";
	$MESSAGE_BODY .= "Mobile: ".$_POST["mobile"]."";
    $MESSAGE_BODY .= "Email: ".$_POST["email"]."";
    $MESSAGE_BODY .= "Message: ";
	
    //mail($ToEmail, $EmailSubject, $MESSAGE_BODY, $mailheader) or die ("Failure");
	mail($ToEmail,$EmailSubject, $MESSAGE_BODY, $mailheader) or die ("Failure");
?>
Your message was sent

<?php
}