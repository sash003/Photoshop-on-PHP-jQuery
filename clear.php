<?php
session_start();
$tmpArr = array(
  $_SESSION['img'],
  $_SESSION['cropimg'],
  $_SESSION['textimg'],
  $_SESSION['tmp']
);
foreach($tmpArr as $path) {
  foreach($path as $file) {
    if (file_exists($file)) {
      echo unlink($file);
    }
  }
}
