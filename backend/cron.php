<?php

// Check execution time - Start time
$time_start = microtime(true);

set_time_limit(3600);

require_once('../setup/config.php');


// Create new items table
$unixTime = time();
$newTable = "items_".$unixTime;

$sql = "CREATE TABLE `".$newTable."` (
  `place` int(20) NOT NULL,
  `id` int(20) NOT NULL,
  `parent` int(20) NOT NULL,
  `kids` int(20) NOT NULL,
  `score` int(20) NOT NULL,
  `time` int(20) NOT NULL,
  `title` text NOT NULL,
  `text` varchar(420) NOT NULL,
  `type` text NOT NULL,
  `url` text NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1";

if ($conn->query($sql) === TRUE) {
  echo "New table created\n";
} else {
  echo "Error creating table: " . $conn->error;
}


$url = "https://hacker-news.firebaseio.com/v0/topstories.json?print=pretty"; 
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
$result=curl_exec($ch);

$resultArray = json_decode($result, true);

//create the multiple cURL handle
$mh = curl_multi_init();

$i = 1;
foreach ($resultArray as $item) {
  if ($i < 61) {
    ${'ch'.$i} = curl_init();
    $url = "https://hacker-news.firebaseio.com/v0/item/".$item.".json?print=pretty"; 
    curl_setopt(${'ch'.$i}, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt(${'ch'.$i}, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(${'ch'.$i}, CURLOPT_URL, $url);
    curl_multi_add_handle($mh, ${'ch'.$i});
    $i++;
  }
  else { break; }
}

// Start performing the request
do {
  $execReturnValue = curl_multi_exec($mh, $runningHandles);
} while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);

// Loop and continue processing the request
while ($runningHandles && $execReturnValue == CURLM_OK) {
  do {
    $execReturnValue = curl_multi_exec($mh, $runningHandles);
  } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
}

// Check for any errors
if ($execReturnValue != CURLM_OK) {
  trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
}

$i = 1;
foreach ($resultArray as $item) {
  if ($i < 61) {
    // Check for errors
    $curlError = curl_error(${'ch'.$i});

    if ($curlError == "") {
      $responseContent = curl_multi_getcontent(${'ch'.$i});
      $res[$i] = $responseContent;
    }
    else {
      print "Curl error on handle $i: $curlError\n";
    }
    // Remove and close the handle
    curl_multi_remove_handle($mh, ${'ch'.$i});
    curl_close(${'ch'.$i});

    $i++;
  }
  else { break; }
}

// Clean up the curl_multi handle
curl_multi_close($mh);

foreach ($res as $i=>$itemArray) {
  $itemArray = json_decode($itemArray, true);

  
  // Item

  $itemKids = "";
  if (isset($itemArray["kids"])) { $itemKids = $itemArray["descendants"]; }

  $itemUrl = "";
  if (isset($itemArray["url"])) { $itemUrl = $itemArray["url"]; }

  // Clean up
  $old = array('“', '”', "’", "—", " ", "…", "―", "–", "–", "‘", "’");
  $new = array('"', '"', "'", "-", " ", "...", "-", "-", "-", "'", "'");
  $itemTitle = str_replace($old, $new, $itemArray["title"]);

  $itemTitle = mysqli_real_escape_string($conn, $itemArray["title"]);

  $sql = "INSERT INTO ".$newTable." (place, id, kids, score, time, title, type, url) 
  VALUES ( '".$i."', '".$itemArray["id"]."', '".$itemKids."', '".$itemArray["score"]."', 
  '".$itemArray["time"]."', '".$itemTitle."', '".$itemArray["type"]."', '".$itemUrl."' )";

  if ($conn->query($sql) === TRUE) { echo "Insert item: ". $itemArray["id"] ."\n"; }
  else { echo "Error: [".$itemArray["id"]." item] " . $sql . "<br>" . $conn->error; }


  // Item summary

  $sql = "SELECT * FROM summaries WHERE item = '".$itemArray["id"]."'";
  $result = $conn->query($sql);

  if (mysqli_num_rows($result) == 0) {
    // Get item summary and update db
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.aylien.com/api/v1/summarize");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "sentences_number=3&url=".$itemUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    $headers = array();
    $headers[] = "X-Aylien-Textapi-Application-Key: ".$GLOBALS['appKey'];
    $headers[] = "X-Aylien-Textapi-Application-Id: ".$GLOBALS['appId'];
    $headers[] = "Content-Type: application/x-www-form-urlencoded";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $result = json_decode($result, true);

    if (isset($result["sentences"])) {
      if (!isset($result["sentences"][0]) || !isset($result["sentences"][1]) || !isset($result["sentences"][2]) ) {
        $summary = "";
      }
      else if ($result["sentences"][0] == "For inquiries related to this message please contact support.") {
        $summary = "";
      }
      else {
        $summary = $result["sentences"][0] . " " . $result["sentences"][1] . " " . $result["sentences"][2];
      }

      // Clean up
      $old = array('“', '”', "’", "—", " ", "…", "―", "–", "–", "‘", "’");
      $new = array('"', '"', "'", "-", " ", "...", "-", "-", "-", "'", "'");
      $summary = str_replace($old, $new, $summary);

      $summary = mysqli_real_escape_string($conn, $summary);
    }
    else {
      $summary = "";
    }

    $sql = "INSERT INTO summaries (item, summary) VALUES ('".$itemArray["id"]."', '".$summary."')";

    if ($conn->query($sql) === TRUE) { echo "Insert summary!!!\n"; }
    else { echo "Error: [".$item." summary] " . $sql . "<br>" . $conn->error; }
  }


  // Item comments

  if (isset($itemArray["kids"])) {

    //create the multiple cURL handle
    $mh = curl_multi_init();

    foreach ($itemArray["kids"] as $i=>$kid) {

      ${'ch'.$i} = curl_init();
      $url = "https://hacker-news.firebaseio.com/v0/item/".$kid.".json?print=pretty"; 
      curl_setopt(${'ch'.$i}, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt(${'ch'.$i}, CURLOPT_RETURNTRANSFER, true);
      curl_setopt(${'ch'.$i}, CURLOPT_URL, $url);
      curl_multi_add_handle($mh, ${'ch'.$i});

    }

    // Start performing the request
    do {
      $execReturnValue = curl_multi_exec($mh, $runningHandles);
    } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);

    // Loop and continue processing the request
    while ($runningHandles && $execReturnValue == CURLM_OK) {
      do {
        $execReturnValue = curl_multi_exec($mh, $runningHandles);
      } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
    }

    // Check for any errors
    if ($execReturnValue != CURLM_OK) {
      trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
    }

    foreach ($itemArray["kids"] as $i=>$kid) {
      // Check for errors
      $curlError = curl_error(${'ch'.$i});

      if ($curlError == "") {
        $responseContent = curl_multi_getcontent(${'ch'.$i});
        $kidArray = json_decode($responseContent, true);

        $kidkids = "";
        if (isset($kidArray["kids"])) { $kidkids = sizeof($kidArray["kids"]); }

        $kidText = "";
        if (isset($kidArray["text"])) { 
          
          // Clean up
          $old = array('“', '”', "’", "—", " ", "…", "―", "–", "–", "‘", "’");
          $new = array('"', '"', "'", "-", " ", "...", "-", "-", "-", "'", "'");
          $kidArray["text"] = str_replace($old, $new, $kidArray["text"]);

          $kidText = mysqli_real_escape_string($conn, $kidArray["text"]); 
        }

        $sql = "INSERT INTO ".$newTable." (place, id, parent, kids, time, text, type) 
        VALUES ( 99, '".$kidArray["id"]."', '".$kidArray["parent"]."', '".$kidkids."', '".$kidArray["time"]."', 
        '".$kidText."', '".$kidArray["type"]."' )";

        if ($conn->query($sql) === TRUE) { echo "Insert comment!\n"; }
        else { echo "Error: [".$kidArray["id"]." comment] " . $sql . "<br>" . $conn->error; }

      }
      else {
        print "Curl error on handle $i: $curlError\n";
      }
      // Remove and close the handle
      curl_multi_remove_handle($mh, ${'ch'.$i});
      curl_close(${'ch'.$i});
    }

    // Clean up the curl_multi handle
    curl_multi_close($mh);

  }    

}


// Get old table name
$sql = "SELECT table_name FROM current WHERE ID = '1'";
$result = $conn->query($sql);
$result = mysqli_fetch_object($result);
$oldTable = $result->table_name;


// Update 'current' table
$timestamp = date("Y-m-d H:i:s");
$sql = "UPDATE current SET table_name = '".$newTable."', timestamp = '".$timestamp."' WHERE ID = '1'";
if ($conn->query($sql) === TRUE) {
  echo "Current table name updated!\n";
} else {
  echo "Error updating table: " . $conn->error;
}


// Drop old table
$sql = "DROP TABLE `".$oldTable."`";
if ($conn->query($sql) === TRUE) {
  echo "Old table deleted!\n";
} else {
  echo "Error dropping table: " . $conn->error;
}


curl_close($ch);
$conn->close();


// End time
$time_end = microtime(true);

// Total time
$execution_time = ($time_end - $time_start);
echo ("\r\nExecution time: " . $execution_time . " sec");

?>