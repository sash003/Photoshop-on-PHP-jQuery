<?php

    ###########ПОЕХАЛИ#############

session_start();
error_reporting(E_ALL); // включаем вывод всех ошибок:
mb_internal_encoding('utf-8'); // устанавливаем внутреннюю кодировку скрипта
$version = $_SESSION['phpversion'];

function myErrorHandler($errno, $errstr, $errfile, $errline)
{
  echo json_encode(array(
    "error" => 'Error: ' . $errstr . " STRING " . $errline . " FILE " . $errfile . ", PHP " . PHP_VERSION
  ));
  file_put_contents("errors/errors.txt", 'Error: ' . $errstr . " STRING " . $errline . " FILE " . $errfile . ", PHP " . PHP_VERSION);
  exit;
}

set_error_handler('myErrorHandler');

// если файлы  и пути  пришли:

if(!$_POST['ajax']){
  exit(json_encode(array(
    'save' => null
  )));
}

if (is_uploaded_file($_FILES['image']['tmp_name'])) {
  
  $img = new ImageWorker();
  
  $data = array();
  
  $data['width'] = intval($_POST['width']);
  $data['height'] = intval($_POST['height']);
  $data['rlc'] = intval($_POST['rlc']);
  $data['size_bord'] = abs(intval($_POST['size_bord']));
  $data['color_bord'] = trim($_POST['color_bord']);
  $data['contrast'] = intval($_POST['contrast']);
  $data['brightness'] = intval($_POST['brightness']);
  $data['contrast'] = intval($_POST['contrast']);

  $data['filter'] = trim($_POST['filter']);
  $data['filterVal'] = intval($_POST['filterVal']);
  $data['speed'] = intval($_POST['speed']);
  $data['countFrames'] = intval(trim($_POST['countFrames']));
  $data['setCountFrames'] = intval(trim($_POST['setCountFrames']));
  $data['setSlowdown'] = floatval(trim($_POST['setSlowdown']));
  $data['proc'] = intval(trim($_POST['trimBackground']));
  $data['x'] = intval($_POST['x']);
  $data['y'] = intval($_POST['y']);
  $data['coeff'] = floatval($_POST['coeff']);
  $data['opacity'] = floatval($_POST['opacity']);
  $data['format'] = $_POST['format'];
  
  $data['fall'] = $img->get_mimeType($_FILES['image']['tmp_name'], '/image\/(jpeg|png|gif)/', '1') . 
          $img->validate($data['color_bord'], '/\#[a-z\d]{6}/i', '2') . 
          $img->validate($data['filter'], '/|^IMG_FILTER_.{6,17}$/', '3') .
          $img->validate($data['format'], '/(gif|png|jpg)?/', '4');
          
          
  if (strlen($data['rlc']) > 1 || 
    	$data['width'] > 1280 || 
      $data['height'] > 960 || 
      strlen($data['color_bord']) > 17 || 
      $data['size_bord'] > 100 || 
      abs($data['contrast']) > 100 || 
      abs($data['brightness']) > 100 ||
      abs($data['filterVal']) > 100 || 
      abs($data['speed']) > 100 || 
      abs($data['x']) > 1280 || 
      abs($data['y']) > 960 || 
      abs($data['coeff']) > 100 || 
      abs($data['opacity']) > 1 || 
      abs($data['setSlowdown']) > 5 || 
      $data['fall']) {
    
    exit(json_encode(array(
      'save' => null
    )));
  }

  // получаем имя файла без пути:

  $data['name'] = basename($_FILES['image']['name']);

  // получаем расширение файла:

  $data['ext'] = strtolower(mb_substr($data['name'], mb_strrpos($data['name'], '.') + 1));
  $data['filetypes'] = array(
    'jpg',
    'png',
    'gif',
    'jpeg'
  );

  // если расширения совпадают

  if (in_array($data['ext'], $data['filetypes'])) {
    
    $data['sourse'] = $_FILES['image']['tmp_name'];
    $data['path2'] = false;
    if (array_key_exists('watermark', $_FILES)) {
      if (is_uploaded_file($_FILES['watermark']['tmp_name'])) {
        $data['sourse2'] = $_FILES['watermark']['tmp_name'];
        $data['fall'] = $img->get_mimeType($data['sourse2'], '/image\/(jpeg|png|gif)/', '1');
        if ($data['fall']) {
          exit(json_encode(array(
            'save' => null
          )));
        }

        $data['name2'] = basename($_FILES['watermark']['name']);
        $data['ext2'] = strtolower(mb_substr($data['name2'], mb_strrpos($data['name2'], '.') + 1));
        $data['path2'] = $img->tmp . '/' . microtime(true) . '.' . $data['ext2'];
        move_uploaded_file($_FILES['watermark']['tmp_name'], $data['path2']);
      }
    }

    if ($data['ext'] == 'jpeg' || $data['ext'] == 'jpg') $data['ext'] = 'png';
    $data['saveto'] = $img->papka . '/' . microtime(true) . '.' . $data['ext'];
    $data['ImagickSave'] = $img->abs . $data['saveto'];
    if ($data['rlc']) {
      $img->resize_crop($data['sourse'], $data['ImagickSave'], $data['width'], $data['height']);
    }
    else {
      $img->resize($data['sourse'], $data['ImagickSave'], $data['width'], $data['height']);
    }

    if ($data['speed']) {
      $img->delayImage($data['saveto'], $data['ImagickSave'], $data['speed']);
    }
    // ####
    if (file_exists($data['path2'])) {
      $data['saveto'] = $img->add_watermark($data['saveto'], $data['path2'], $data['coeff'], $data['x'], $data['y'], $data['opacity']);
    }
    if ($data['countFrames']) {
      $data['saveto'] = $img->setCountFrames($data['saveto'], $data['countFrames']);
    }

    if ($data['setCountFrames']) {
      $data['saveto'] = $img->setCountFrames2($data['saveto'], $data['setCountFrames']);
    }

    if ($data['setSlowdown']) {
      $data['saveto'] = $img->setSlowdown($data['saveto'], $data['setSlowdown']);
    }

    if ($data['proc']) {
      $data['saveto'] = $img->trimBackground($data['saveto'], $data['proc']);
    }

    

    if ($data['brightness'] || $data['contrast']) {
      $data['saveto'] = $img->brightnessContrastImage($data['saveto'], $data['brightness'], $data['contrast']);
    }

    if ($data['size_bord']) {
      $data['saveto'] = $img->set_border($data['saveto'], $data['size_bord'], $data['color_bord']);
    }

    if ($data['filter']) {
      $data['saveto'] = $img->filter($data['saveto'], $data['filter'], $data['filterVal']);
    }
    if($data['format']){
      $data['saveto'] = $img->setFormat($data['saveto'], $data['format']);
    }


    $_SESSION['img'][] = $data['saveto'];
    $data['ses'] = $_SESSION['img'];
    if (count($data['ses']) > 1) {
      for ($i = 0; $i < count($data['ses']) - 1; $i++) {
        if (file_exists($data['ses'][$i])) {
          unlink($data['ses'][$i]);
        }
      }
    }

    if ($data['ext'] === 'gif' || (isset($data['ext2']) && $data['ext2'] === 'gif')) {
      $data['delayArr'] = $img->getDelay($data['saveto']);
      
      echo json_encode(array(
        'save' => $data['saveto'],
        'delay' => $data['delayArr'][0],
        'countframes' => $data['delayArr'][1]
      ));
    }
    else echo json_encode(array(
      'save' => $data['saveto']
    ));
    //$img->rmDirs();
  }
}else exit(json_encode(array(
  'save' => null
)));


/**
 * @package Photoshop
 */
class ImageWorker

{
  public $papka = 'windows_images';

  public $tmp = 'tmp';

  public $session = '';

  public $tmp2 = '';

  public $abs = '';
  
  protected $tmp3 = '';
  
  function __construct(){
    $this->abs = dirname(__FILE__) . '/';
    $this->session = session_id();
    $this->tmp2 = $this->tmp . '/' . $this->session;
    $this->tmp3 = $this->tmp2 . '/setCountFrames';
    $dirs = array(
      $this->papka,
      $this->tmp,
      $this->tmp2,
      $this->tmp3
    );
    foreach($dirs as $dir) {
      if (!file_exists($dir)) mkdir($dir, 0755, true);
    }
  }
  

  // смена формата
  function setFormat($src, $format){
    $gif = new \Imagick(realpath($src));
    $newName = $this->papka . '/' . microtime(true) . '.' . $format;
    $saveTo = $this->abs . $newName;
    if($format === 'gif'){
      $gif->writeImages($saveTo, true);
    }else{
      $gif->writeImage($saveTo);
    }
    return $newName;
  }


  // удаление файлов
  function rmDirs(){
    $dirs = array(
      $this->tmp
    );
    foreach($dirs as $dir) {
      if (file_exists($dir)) {
        $this->deleteAllFiles($dir, true);
      }
    }
  }

  function setSlowdown($src, $count)
  {
    $gif = new Imagick(realpath($src));
    $delayArr = $this->getDelay($src);
    $delay = $delayArr [0];
    $countFr = $delayArr [1];
    if ($gif->getImageMimeType() == 'image/gif') {
      foreach($gif as $key => $frame) {
        $frame->setImageDelay($delay + $key * $count);
      }

      $gif->writeImages($this->abs . '/' . $src, true);
      $gif->clear();
    }

    return $src;
  }

  function setCountFrames2($src, $count)
  {
    $gif = new \Imagick(realpath($src));
    if ($gif->getImageMimeType() == 'image/gif') {
      $new = new Imagick();
      $delayArr = $this->getDelay($src);
      $delay = $delayArr [0];
      $countFr = $delayArr [1];
      $tmpArr = array();
      foreach($gif as $key => $frame) {
        if ($key < $count) {
          $tmpName = $this->tmp2 . '/' . $key . '.png';
          $frame->writeImage($this->abs . '/' . $tmpName);
          $tmpArr[] = $tmpName;
        }
      }

      $scan = $tmpArr;
      natsort($scan);
      foreach($scan as $file) {
        $img = new Imagick(realpath($file));
        $img->setImageDelay($delay);
        $new->addImage($img);
      }

      $new->writeImages($this->abs . '/' . $src, true);
      foreach($tmpArr as $file) {
        unlink($file);
      }

      $gif->clear();
      $new->clear();
    }

    return $src;
  }


  ///
  function setCountFrames($src, $count)
  {
    $gif = new Imagick(realpath($src));
    if ($gif->getImageMimeType() == 'image/gif') {
      $delayArr = $this->getDelay($src);
      $delay = $delayArr [0];
      $countFr = $delayArr [1];
      $tmpArr = array();
      for ($i = 0; $i < $count; $i++) {
        $tmpName = $this->tmp3 . '/' . $i . microtime(true) . '.gif';
        copy($src, $tmpName);
        $tmpArr[] = $tmpName;
      }

      $this->execute("convert -dispose background -delay {$delay} {$this->tmp3}/*.gif -loop 0 {$src}");
      $gif->clear();
      foreach($tmpArr as $file) {
        unlink($file);
      }
    }

    $_SESSION['tmp'][] = $src;
    return $src;
  }


  ///
  function execute($command)
  {
    $command = str_replace(array(
      "\n",
      "'"
    ) , array(
      '',
      '"'
    ) , $command);
    $command = escapeshellcmd($command);
    exec($command);
  }


  ///
  function trimBackground($src, $proc)
  {
    $type = pathinfo($src, PATHINFO_EXTENSION);
    $im = new Imagick(realpath($src));
    $color = $im->getImagePixelColor(0, 0);
    $color = $color->getColorAsString();

    $this->execute("convert {$src} -fuzz {$proc}% -transparent {$color} {$src}");
    return $src;
  }


  ///
  function brightnessContrastImage($src, $brightness, $contrast, $channel = Imagick::CHANNEL_DEFAULT)
  {
    $result = FALSE;
    $dest = $this->abs . $src;
    try {
      $img = new Imagick(realpath($src));
      if ($img->getImageMimeType() == 'image/gif') {
        if (method_exists('Imagick', 'brightnessContrastImage')) {
          foreach($img as $frame) {
            $frame->brightnessContrastImage($brightness, $contrast, $channel);
          }

          // Обратите внимание, writeImages вместо writeImage
          $result = $img->writeImages($dest, true);
        }
        else {
          $tmp = $this->tmp2 . '/' . uniqid() . microtime(true) . '.jpg';
          foreach($img as $frame) {
            $frame->writeImage($this->abs . '/' . $tmp);
            $im = imagecreatefromjpeg($tmp);
            imagefilter($im, IMG_FILTER_BRIGHTNESS, $brightness);
            imagefilter($im, IMG_FILTER_CONTRAST, -$contrast);
            imagejpeg($im, $tmp);
            $fr = new Imagick(realpath($tmp));
            $frame->compositeImage($fr, Imagick::COMPOSITE_DEFAULT, 0, 0);
            unlink($tmp);
          }

          $result = $img->writeImages($this->abs . '/' . $src, true);
        }
      }
      else {
        if (method_exists('Imagick', 'brightnessContrastImage')) {
          $img->brightnessContrastImage($brightness, $contrast, $channel);
          $img->setImageCompression(Imagick::COMPRESSION_JPEG);
          $img->setImageCompressionQuality(70);
          $result = $img->writeImage($dest);
        }
        else {
          $ext = strtolower(mb_substr($src, mb_strrpos($src, '.') + 1));
          if ($ext === 'jpg') $ext = 'jpeg';
          $func = 'imagecreatefrom' . $ext;
          $im = $func($src);
          imagefilter($im, IMG_FILTER_BRIGHTNESS, $brightness);
          imagefilter($im, IMG_FILTER_CONTRAST, -$contrast);
          $func = 'image' . $ext;
          $func($im, $src); // сохранение выходного изображения
          imagedestroy($im); // освобождение памяти
        }
      }

      $img->clear();
    }

    catch(ImagickException $e) {
      echo 'У нас проблема ' . $e->getMessage() . " в файле " . $e->getFile() . ", строка " . $e->getLine();
    }

    $_SESSION['tmp'][] = $src;
    return $src;
  }

  // ##########################

  function add_watermark($source_image_path, $watermark_path, $coeff, $x, $y, $opacity = 0)
  {
    $result = false;
    try {
      $papka = $this->papka;
      $first = new Imagick(realpath($source_image_path));
      $second = new Imagick(realpath($watermark_path));
      $ext1 = strtolower(mb_substr($source_image_path, mb_strrpos($source_image_path, '.') + 1));
      $ext2 = strtolower(mb_substr($watermark_path, mb_strrpos($watermark_path, '.') + 1));
      if ($ext1 == 'gif' || $ext2 == 'gif') {
        $out = $papka . '/' . microtime(true) . '.gif';
      }
      else {
        $out = $papka . '/' . microtime(true) . '.png';
      }

      $output = __DIR__ . '/' . $out;
      $width = $first->getImageWidth();
      $width = $width / 100 * $coeff;
      if (!$x && !$y) {
        $width_src = $first->getImageWidth();
        $width_mark = $second->getImageWidth();
        $height_src = $first->getImageHeight();
        $height_mark = $second->getImageHeight();
        $coef = $width_mark / $height_mark;
        $x = $width_src - $width - 5;
        $y = $height_src - $width / $coef - 5;
      }

      if ($first->getImageMimeType() == 'image/gif') {
        if ($second->getImageMimeType() == 'image/gif') {
          foreach($second as $frame) {
            $frame->thumbnailImage($width, 0);
          }

          $tmpName = $this->tmp2 . '/' . uniqid() . microtime(true) . '.gif';
          $tmppath = $this->abs . '/' . $tmpName;
          $second->writeImages($tmppath, true);
          $second = new Imagick($tmppath);
          $tmpArr = array();
          unlink($tmpName);
          foreach($second as $key => $frame) {
            $tmpName = $this->tmp2 . '/' . $key . '.png';
            $frame->writeImage($this->abs . '/' . $tmpName);
            $tmpArr[] = $tmpName;
          }

          $scan = $tmpArr;
          natsort($scan);
          foreach($first as $key => $frame) {
            if (array_key_exists($key, $scan)) {
              $new = new Imagick(realpath($scan[$key]));
              if ($opacity) $new->setImageOpacity($opacity);
            }
            else $new = new Imagick(realpath($scan[0]));

            // накладываем изображения
            $frame->compositeImage($new, imagick::COMPOSITE_DEFAULT, $x, $y);
          }

          $result = $first->writeImages($output, true);
          foreach($tmpArr as $file) {
            unlink($file);
          }
        }
        else {
          $second->thumbnailImage($width, 0);
          if ($opacity) $second->setImageOpacity($opacity);

          // то для каждого фрейма гифки делаем следующее:
          foreach($first as $frame) {

            // накладываем изображения
            $frame->compositeImage($second, imagick::COMPOSITE_DEFAULT, $x, $y);
          }

          $result = $first->writeImages($output, true);
        }
      }
      else {
        if ($second->getImageMimeType() == 'image/gif') {
          $new = new Imagick();
          $delayArr = $this->getDelay($watermark_path);
          $delay = $delayArr [0];
          foreach($second as $frame) {
            $frame->thumbnailImage($width, 0);
          }

          $tmpName = $this->tmp2 . '/' . uniqid() . microtime(true) . '.gif';
          $tmppath = __DIR__ . '/' . $tmpName;
          $second->writeImages($tmppath, true);
          $second = new Imagick($tmppath);
          unlink($tmpName);
          foreach($second as $frame) {
            if ($opacity) $frame->setImageOpacity($opacity);
            $firt = clone $first;

            // накладываем изображения
            $firt->compositeImage($frame, imagick::COMPOSITE_DEFAULT, $x, $y);
            $firt->setImageDelay($delay);
            $new->addImage($firt);
          }

          $result = $new->writeImages($output, true);
        }
        else {
          $second->thumbnailImage($width, 0);
          if ($opacity) $second->setImageOpacity($opacity);
          $first->compositeImage($second, imagick::COMPOSITE_DEFAULT, $x, $y);

          // устанавливаем степень сжатия
          $first->setImageCompression(Imagick::COMPRESSION_JPEG);

          // и качество
          $first->setImageCompressionQuality(90);
          $result = $first->writeImage($output);
        }
      }

      unlink($source_image_path);
      $first->clear();
      $second->clear();
    }

    catch(Exception $e) {
      echo 'У нас проблема ' . $e->getMessage() . " в файле " . $e->getFile() . ", строка " . $e->getLine();
    }

    return $out;
  }

  // фильтр Imagick+GD
  function filter($src, $filter, $arg)
  {
    $result = false;
    $filter = constant($filter);
    $dest = $this->abs . '/' . $src;
    try {
      $animation = new Imagick(realpath($src));
      $tmpName = $this->tmp2 . '/' . microtime(true) . '.jpg';
      if ($animation->getImageMimeType() == 'image/gif') {
        foreach($animation as $frame) {
          $tmpName = $this->tmp2 . '/' . microtime(true) . '.jpg';
          $frame->writeImage($this->abs . '/' . $tmpName);
          $im = imagecreatefromjpeg($tmpName);
          if ($filter == IMG_FILTER_PIXELATE && $arg) {
            imagefilter($im, $filter, $arg, true);
          }
          else {
            imagefilter($im, $filter, $arg);
          }

          imagejpeg($im, $tmpName);
          $fr = new Imagick(realpath($tmpName));
          $frame->compositeImage($fr, Imagick::COMPOSITE_DEFAULT, 0, 0);
          unlink($tmpName);
        }

        $result = $animation->writeImages($dest, true);
      }
      else {
        $animation->writeImage(__DIR__ . '/' . $tmpName);
        $im = imagecreatefromjpeg($tmpName);
        if ($filter == IMG_FILTER_PIXELATE && $arg) {
          imagefilter($im, $filter, $arg, true);
        }
        else {
          imagefilter($im, $filter, $arg);
        }

        imagejpeg($im, $tmpName);
        $fr = new Imagick(realpath($tmpName));
        $animation->compositeImage($fr, Imagick::COMPOSITE_DEFAULT, 0, 0);
        $result = $animation->writeImage($dest);
        unlink($tmpName);
      }
    }

    catch(ImagickException $e) {
      echo 'У нас проблема ' . $e->getMessage() . " в файле " . $e->getFile() . ", строка " . $e->getLine();
    }

    return $src;
  }


  ///
  function getDelay($src)
  {
    $animation = new Imagick(realpath($src));
    $del = array();
    $countFrames = 0;
    foreach($animation as $frame) {
      $delay = $animation->getImageDelay();
      $countFrames++;
      $del[] = $delay;
    }

    return array(
      $del[0],
      $countFrames,
      $del[0] * $countFrames
    );
  }

  function delayImage($src, $dest, $speed)
  {
    $result = FALSE;
    try {
      $imagick = new Imagick(realpath($src));
      $imagick = $imagick->coalesceImages();
      foreach($imagick as $frame) {
        $imagick->setImageDelay($speed);
      }

      $imagick = $imagick->deconstructImages();
      $result = $imagick->writeImages($dest, true);
    }

    catch(ImagickException $e) {
      echo 'У нас проблема ' . $e->getMessage() . " в файле " . $e->getFile() . ", строка " . $e->getLine();
    }

    return $result;
  }

  function get_mimeType($filename, $pattern, $message)
  {
    //$finfo = finfo_open(FILEINFO_MIME_TYPE); // возвращает mime-тип
    $mime = mime_content_type($filename);
    //finfo_close($finfo);
    if (!preg_match($pattern, $mime)) {
      return $mime;
    }
    else return '';
  }



  /**
   * @param string $orig - путь к обрабатываемой картинке
   * @param string $path - путь сохранения
   * @param int $width - ширина
   * @param int $height - высота
   *
   * @return результат выполнения, true или false
   */
  function resize($orig, $path, $width = 0, $height = 0)
  {
    $result = FALSE;
    try {
      $img = new \Imagick(realpath($orig));
      if ($img->getImageMimeType() == 'image/gif') {
        foreach($img as $frame) {
          $frame->thumbnailImage($width, $height);
        }

        // Обратите внимание, writeImages вместо writeImage

        $result = $img->writeImages($path, true);
      }
      else {
        $img->thumbnailImage($width, $height);
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
  function validate($value, $pattern, $message)
  {
    if (preg_match($pattern, $value)) return '';
    else return $message;
  }

  // РАМКА
  function set_border($src, $size, $color)
  {
    $result = FALSE;
    $dest = $this->abs . $src;
    try {
      $img = new Imagick(realpath($src));
      $bordercolor = new ImagickPixel($color);
      if ($img->getImageMimeType() == 'image/gif') {
        foreach($img as $frame) {
          $frame->borderImage($color, $size, $size);
        }

        // Обратите внимание, writeImages вместо writeImage
        $result = $img->writeImages($dest, true);
      }
      else {
        $img->borderImage($color, $size, $size);
        $img->setImageCompression(Imagick::COMPRESSION_JPEG);
        $img->setImageCompressionQuality(70);
        $result = $img->writeImage($dest);
      }

      $img->clear();
    }

    catch(ImagickException $e) {
      echo 'У нас проблема ' . $e->getMessage() . " в файле " . $e->getFile() . ", строка " . $e->getLine();
    }

    return $src;
  }

  // большой функ по резу
  function resize_crop($source, $output, $width, $height)
  {
    $result = FALSE;
    try {
      $img = new Imagick(realpath($source));
      if ($img->getImageMimeType() == 'image/gif') {
        foreach($img as $frame) {
          $frame->cropThumbnailImage($width, $height);
        }

        // Обратите внимание, writeImages вместо writeImage
        $result = $img->writeImages($output, true);
      }
      else {
        $img->cropThumbnailImage($width, $height);
        $img->setImageCompression(Imagick::COMPRESSION_JPEG);
        $img->setImageCompressionQuality(70);
        $result = $img->writeImage($output);
      }

      $img->clear();
    }

    catch(ImagickException $e) {
      echo 'У нас проблема ' . $e->getMessage() . " в файле " . $e->getFile() . ", строка " . $e->getLine();
    }

    return $result;
  }

  function getGeometry($src)
  {
    $animation = new Imagick(realpath($src));
    $w = $animation->getImageWidth();
    $h = $animation->getImageHeight();
    return array(
      $w,
      $h
    );
  }

  // удаление файлов
  function deleteAllFiles($dir, $param=false)
  {
    $list = glob($dir . "/*");
    
    for ($i = 0; $i < count($list); $i++) {
      if (file_exists($list[$i])) {
        if (is_dir($list[$i])) {
          $this->deleteAllFiles($list[$i]);
          rmdir($list[$i]);
        }
        else unlink($list[$i]);
      }
    }
  }
} // end class Worker