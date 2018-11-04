<?php
// Update this file with your own login details
require_once('../setup/config.php');


// Read post data
$data = json_decode(file_get_contents("php://input"));
$request = $data->request;


// Return stories
if ($request == "getItems") {

  $sql = "SELECT table_name FROM current WHERE ID = '1'";
  $result = $conn->query($sql);
  $result = mysqli_fetch_object($result);
  $currentTable = $result->table_name;

  $sql = "SELECT * FROM ".$currentTable." WHERE place != 99";
  $result = $conn->query($sql);

  while($row = mysqli_fetch_assoc($result)){
    $json[] = $row;
  }
  echo json_encode($json);

  $conn->close();
  exit;
}

// Return comments
if ($request == "getComments") {

  $sql = "SELECT table_name FROM current WHERE ID = '1'";
  $result = $conn->query($sql);
  $result = mysqli_fetch_object($result);
  $currentTable = $result->table_name;

  $sql = "SELECT * FROM ".$currentTable." WHERE type = 'comment'";
  $result = $conn->query($sql);

  while($row = mysqli_fetch_assoc($result)){
    if (strlen($row["text"]) > 0) {
      $row["text"] = utf8_encode($row["text"]);
      $json[] = $row;
    }
  }

  echo json_encode($json);

  $conn->close();
  exit;
}


// Return summaries
if ($request == "getSummaries") {

  $sql = "SELECT table_name FROM current WHERE ID = '1'";
  $result = $conn->query($sql);
  $result = mysqli_fetch_object($result);
  $currentTable = $result->table_name;
  
  $sql = "SELECT * FROM ".$currentTable." WHERE type = 'story'";
  $result = $conn->query($sql);

  while($row = mysqli_fetch_assoc($result)){
    
    $sqlSum = "SELECT * FROM summaries WHERE item = '".$row["id"]."'";
    $resultSum = $conn->query($sqlSum);
    while($rowSum = mysqli_fetch_assoc($resultSum)){
      if(strlen($rowSum["summary"]) > 0) {
        $rowSum["summary"] = utf8_encode($rowSum["summary"]);
        $json[$rowSum["item"]] = $rowSum;
      }
    }
  }

  echo json_encode($json);

  $conn->close();
  exit;
}


// Archive ideas - send email
if ($request == "sendEmail") {

  $email = $data->email;
  $content = $data->content;
  $gRecaptchaResponse = $data->gRecaptchaResponse;

  //
  // Verify $gRecaptchaResponse
  //

  $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';
	
  // Collect and build POST data
  $post_data = http_build_query(
    array(
      'secret' => $GLOBALS['reCaptchaSecret'],
      'response' => $gRecaptchaResponse,
      'remoteip' => (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR'])
    )
  );
    
  // Send data on the best possible way
  if(function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec')) {
    $ch = curl_init($verifyURL);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-type: application/x-www-form-urlencoded'));
      $response = curl_exec($ch);
    curl_close($ch);
  } else {
    // If server not have active cURL module, use file_get_contents
    $opts = array('http' =>
      array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $post_data
      )
    );
    $context  = stream_context_create($opts);
    $response = file_get_contents($verifyURL, false, $context);
  }
  
  // Verify all reponses and avoid PHP errors
  if($response) {
    
    $result = json_decode($response);

    if ($result->success===true) {

      //
      // Send email
      //

      $contentString = "";

      foreach ($content as $dig) {
        $contentString .= $dig->text . "\r\nhttps://news.ycombinator.com/item?id=" . $dig->item  . "\r\n\r\n\r\n";
      }
      $contentString .= "--" . "\r\n" . "https://hackerdig.com";

      $mail->Subject  = "[hackerdig] Ideas";
      $mail->Body = $contentString;

      $mail->addAddress($email);

      // Send + success or error
      if(!$mail->Send()) {
        echo 'Message could not be sent. ';
        echo 'Mailer Error: ' . $mail->ErrorInfo . "\r\n";

        // inform admin if error
        mail($GLOBALS['adminaddress'], "Hacker Dig mail sending error", $mail->ErrorInfo . ' Email: ' . $email);

        echo "email-error";
      }
      else {
        echo "email-sent";
      }

      $mail->ClearAllRecipients();
      exit;
    } 
    else {
      // var_dump($result);
      echo "captcha-error";
      exit;
    }
  }
}

?>