<?php

require_once('../setup/config.php');

// sql to create summaries table
$sql = "CREATE TABLE `summaries` (
  `item` int(20) NOT NULL,
  `summary` text NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`item`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1";

// sql to create current table
$sql2 = "CREATE TABLE `current` (
  `ID` int(11) NOT NULL,
  `table_name` tinytext NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1";

// sql to create current table
$sql2 = "CREATE TABLE `current` (
  `ID` int(11) NOT NULL,
  `table_name` tinytext NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1";

// New record for 'current' table
$sql3 = "INSERT INTO `current` (`ID`, `table_name`, `timestamp`) VALUES ('1', '', '')";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Execute queries
if ($conn->query($sql) === TRUE) {
  echo "Table 'summaries' created successfully\n";
} else {
  echo "Error creating table: " . $conn->error;
}

if ($conn->query($sql2) === TRUE) {
  echo "Table 'current' created successfully\n";
} else {
  echo "Error creating table: " . $conn->error;
}

if ($conn->query($sql3) === TRUE) {
  echo "New record created";
} else {
  echo "Error: " . $conn->error;
}

$conn->close();

?>