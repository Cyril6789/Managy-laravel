<?php
if($_POST)
{
    
    
##########################################################################################################################################################
##########################################################################################################################################################
    
    $success_mssg   = "You have been successfully subscribed. Thank you.";                  // Success message
    $empty_fields   = "Input field is empty! Please enter your email.";                     // Empty fields
    $email_mssg     = "Please enter a valid email!";                                        // Valid email
    
##########################################################################################################################################################
##########################################################################################################################################################


    
    //Sanitize input data using PHP filter_var(). *PHP 5.2.0+
    $subscriber_email   =   filter_var($_POST["subscriber_email"], FILTER_SANITIZE_EMAIL);

    
    //Check Email
    if(!filter_var($subscriber_email, FILTER_VALIDATE_EMAIL)) //email validation
    {
        $output = json_encode(array('type'=>'error', 'text' => $email_mssg));
        die($output);
    }
    
    
    // output subscribers email in email-list.txt
    $email = $_POST['subscriber_email'] . "," . "\n";
    $savedmail = file_put_contents('email-list.txt', $email, FILE_APPEND | LOCK_EX);
    
    if(!$savedmail)
    {
        $output = json_encode(array('type'=>'error', 'text' => $error_mssg));
        die($output);
    }else{
        $output = json_encode(array('type'=>'message', 'text' => $success_mssg));
        die($output);
    }
}else{
    header('Location: ../404-fullscreen.html');
}
?>