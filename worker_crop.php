<?php
session_start();

if (isset($_POST['image'])) {
  
  $data = array();
  
  $data['name'] = preg_replace("/[\<\>`,\[\]]/", 's', $_POST['image']);
  $data['ext'] = strtolower(mb_substr($data['name'], mb_strrpos($data['name'], '.') + 1));
  $data['x'] = intval($_POST['x1']);
  $data['y'] = intval($_POST['y1']);
  $data['crop_width'] = intval($_POST['w']);
  $data['crop_height'] = intval($_POST['h']);
  $data['dir'] = 'crop_images';
  $data['path'] = $data['dir'] . '/' . microtime(true) . '.' . $data['ext'];
  $data['fullpath'] = __DIR__ . '/' . $data['path'];
  if (!file_exists($data['dir'])) mkdir($data['dir'], 0755, true);
  cropImage(__DIR__ . '/' . $data['name'], $data['fullpath'], $data['crop_width'], $data['crop_height'], $data['x'], $data['y']);
  $_SESSION['cropimg'][] = $data['path'];
  $data['ses'] = $_SESSION['cropimg'];
  if (count($_SESSION['cropimg']) > 1) {
    for ($i = 0; $i < count($_SESSION['cropimg']) - 1; $i++) {
      if (file_exists($data['ses'][$i])) {
        unlink($data['ses'][$i]);
      }
    }
  }

  echo "<img src='" . $data['path'] . "'>";
}

function cropImage($sourse, $path, $width, $height, $x, $y)
{
  $result = false;
  try {
    $img = new Imagick($sourse);
    if ($img->getImageMimeType() == 'image/gif') {
      $new = new Imagick();
      $new->newImage($width, $height, new ImagickPixel('transparent') , 'gif');
      foreach($img as $key => $frame) {
        $frame->cropImage($width, $height, $x, $y);
        if ($key == 0) {
          $new->compositeImage($frame, Imagick::COMPOSITE_DEFAULT, 0, 0);
          $speed = getDelay($sourse);
          $new->setImageDelay($speed);
        }
        else {
          $new->newImage($width, $height, new ImagickPixel('transparent') , 'gif');
          $new->compositeImage($frame, Imagick::COMPOSITE_DEFAULT, 0, 0);
          $new->setImageDelay($speed);
          $new->setImageCompression(Imagick::COMPRESSION_JPEG);
          $new->setImageCompressionQuality(70);
        }
      }

      // Обратите внимание, writeImages вместо writeImage

      $result = $new->writeImages($path, true);
    }
    else {
      $img->cropImage($width, $height, $x, $y);
      $img->setImageCompression(Imagick::COMPRESSION_JPEG);
      $img->setImageCompressionQuality(70);
      $result = $img->writeImage($path);
    }

    $img->clear();
  }

  catch(ImagickException $e) {
    echo 'У нас проблема ' . $e->getMessage() . " в файле " . $e->getFile() . ", строка " . $e->getLine();
  }

  return $result;
}

function getDelay($src)
{
  $animation = new Imagick(realpath($src));
  $del = array();
  foreach($animation as $frame) {
    $delay = $animation->getImageDelay();
    $del[] = $delay;
  }

  return $del[0];
}

function deleteAllFiles($dir)
{
  $list = glob($dir . "/*");
  for ($i = 0; $i < count($list) - 11; $i++) {
    if (is_dir($list[$i])) deleteAllFiles($list[$i]);
    else unlink($list[$i]);
  }
}