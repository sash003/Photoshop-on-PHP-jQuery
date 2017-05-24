<?php
session_start();
error_reporting(E_ALL);

$data = array();

$data['src'] = trim($_POST['src']);
$data['text'] = str_replace('<div>', '\n', $_POST['text']);
$data['text'] = str_replace('</div>', '', $data['text']);
$data['text'] = str_replace('&nbsp;', ' ', $data['text']);
$data['text'] = preg_replace('/\<br\>|\<br\/\>/', '\n', $data['text']);

// echo $text;

$data['x'] = intval($_POST['x']);
$data['y'] = intval($_POST['y']);
$data['font'] = trim($_POST['font']);
$data['size'] = intval($_POST['size']);
$data['color'] = trim($_POST['color']);

$obj = new workerText;

$data['fall'] = $obj->get_mimeType($data['src'], '/image\/(jpeg|png|gif)/', 'Файл не является изображением<br />') .
                $obj->validate($data['color'], '/#[a-z\d]{6}/i', 'неправильный цвет<br />') . 
                $obj->validate($data['font'], '/Shot\d{1,2}/', 'неправильный фонт<br />');

if ($data['fall']) exit($data['fall']);

$data['papka'] = 'textImages';

if (!file_exists($data['papka'])) mkdir($data['papka'], 0755, true);

$data['ext'] = mb_substr($data['src'], mb_strrpos($data['src'], '.') + 1);
$data['file'] = microtime(true) . '.' . $data['ext'];
$data['path'] = 'textImages/' . $data['file'];
$data['fullpath'] = __DIR__ . '/' . $data['path'];

if ($obj->setText($data['src'], $data['fullpath'], $data['text'], $data['font'], $data['color'], $data['size'], $data['x'], $data['y'])) {
  echo $data['path'];
}

$_SESSION['textimg'][] = $data['path'];



class workerText

{
  public function setText($orig, $path, $text, $font, $color, $size, $x, $y)
  {
    $result = FALSE;
    $draw = new ImagickDraw();
    $draw->setFillColor($color);
    /* Настройки шрифта */
    $draw->setFont(realpath('fonts/' . $font . '.ttf'));
    $draw->setFontSize($size);
    $y = $y + $size;
    try {
      $img = new Imagick(realpath($orig));
      if ($img->getImageMimeType() == 'image/gif') {
        foreach($img as $frame) {
          $textArr = explode('\n', $text);
          for ($i = 0; $i < count($textArr); $i++) {
            $frame->annotateImage($draw, $x, $y + $size * $i, 0, $textArr[$i]);
          }

          /* Создаем текст */
        }

        // Обратите внимание, writeImages вместо writeImage

        $result = $img->writeImages($path, true);
      }
      else {
        $textArr = explode('\n', $text);
        for ($i = 0; $i < count($textArr); $i++) {
          $img->annotateImage($draw, $x, $y + $size * $i, 0, $textArr[$i]);
        }

        // $img->annotateImage($draw, $x, $y, 0, $text);

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

  // проверка введённых данных

  public function validate($value, $pattern, $message)
  {
    if (preg_match($pattern, $value)) return '';
    else return $message;
  }

  public function get_mimeType($filename, $pattern, $message)
  {
    $finfo = finfo_open(FILEINFO_MIME_TYPE); // возвращает mime-тип
    $mime = finfo_file($finfo, $filename);
    finfo_close($finfo);
    if (!preg_match($pattern, $mime)) {
      return $message;
    }
    else return '';
  }

  // удаление файлов

  function deleteAllFiles($dir)
  {
    $list = glob($dir . "/*");
    for ($i = 0; $i < count($list) - 11; $i++) {
      if (is_dir($list[$i])) deleteAllFiles($list[$i]);
      else unlink($list[$i]);
    }
  }
}