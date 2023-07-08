<?php
/**
 * @author      Raiza Rhamdan (Leonardo DaVchezt) <davchezt@gmail.com>
 * @copyright   Copyright (c), 2021 Raiza Rhamdan
 * @license		MIT public license
 */

namespace app\Lib;

defined("__DAVCHEZT") or die("{ \"response\" : \"error 403\"}");

class Image
{
    protected $config = [];
    const DS = DIRECTORY_SEPARATOR;

    public function __construct($config = array())
    {
        $this->configure($config);
    }

    public function configure($config = array())
    {
        $this->config = $config;
        $uploadDir = $this->config["baseDir"].self::DS.$this->config["uploadDir"];
        if (!is_dir($uploadDir)) {
            mkdir($this->config["baseDir"].self::DS.$this->config["uploadDir"]);
        
            // Crops
            mkdir($this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."crops");
            // Thumbs
            mkdir($this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."thumbs");
            // Normals
            mkdir($this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."normals");
            // Originals
            mkdir($this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."originals");
        }
    }

    public function saveImage($key, $type = "crop", $watermark = false, $jpg = false)
    {
        if (empty($_FILES[$key]["tmp_name"])) {
            return;
        }
        // $fileName = $jpg ? substr($_FILES[$key]["name"], 0, -4).".jpg" : basename($_FILES[$key]["name"]);
        $newName = 'img_' . pathinfo($_FILES[$key]["name"], PATHINFO_FILENAME);
        $fileName = $jpg ? strtolower(preg_replace("/\W+/", "_", $newName)).".jpg" : basename($_FILES[$key]["name"]);
        $image = null;
        switch ($type) {
        case "crop":
            $destination = $this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."crops".self::DS.$fileName;
            break;
        case "min":
            $destination = $this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."thumbs".self::DS.$fileName;
            break;
        case "max":
            $destination = $this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."normals".self::DS.$fileName;
            break;
        default:
            $destination = $this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."originals".self::DS.$fileName;
            break;
        }
        try {
            $file = $this->getUploadedFile($key);
            $image = $this->saveAsImage(
                $file,
                $destination,
                $this->config["imageWidth"],
                $this->config["imageHeight"],
                $type,
                $watermark
            );
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        if ($image) {
            chmod($image, 0777);
        }

        return $image;
    }

    public function saveImages($key, $type = "crop", $watermark = false, $jpg = false)
    {
        if (empty($_FILES[$key]["tmp_name"][0])) {
            return;
        }

        $images = array();
        $totalFiles = count($_FILES[$key]["tmp_name"]);

        for ($i = 0; $i < $totalFiles; $i++) {
            $newName = 'img_' . pathinfo($_FILES[$key]["name"][$i], PATHINFO_FILENAME);
            $fileName = $jpg ? strtolower(preg_replace("/\W+/", "_", $newName)).".jpg" : basename($_FILES[$key]["name"][$i]);

            switch ($type) {
                case "crop":
                    $destination = $this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."crops".self::DS.$fileName;
                    break;
                case "min":
                    $destination = $this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."thumbs".self::DS.$fileName;
                    break;
                case "max":
                    $destination = $this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."normals".self::DS.$fileName;
                    break;
                default:
                    $destination = $this->config["baseDir"].self::DS.$this->config["uploadDir"].self::DS."originals".self::DS.$fileName;
                    break;
            }

            try {
                $file = $this->getUploadedFiles($key, $i);
                $image = $this->saveAsImage(
                    $file,
                    $destination,
                    $this->config["imageWidth"],
                    $this->config["imageHeight"],
                    $type,
                    $watermark
                );
                if ($image) {
                    chmod($image, 0777);
                    $images[] = $image;
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        return $images;
    }

    public function getUploadedFile($key, $allowedTypes = array())
    {
        $error = false;
        if (!isset($_FILES[$key]) or !is_uploaded_file($_FILES[$key]["tmp_name"])) {
            $error = "Upload Failed";
        } elseif (count($allowedTypes) != null and in_array(pathinfo($_FILES[$key]["name"], PATHINFO_EXTENSION), $allowedTypes)) {
            $error = "Invalid file type";
        } else {
            $file = $_FILES[$key];
            switch ($file["error"]) {
            case 1:
            case 2:
                $error = sprintf("file Upload is Too Big Max Is %d", ini_get("upload_max_filesize"));
                break;
            case 3:
            case 4:
            case 6:
            case 7:
            case 8:
                $error = "[UPLOAD:FAIL]";
        }
        }
        if ($error) {
            throw new \Exception($error);
        } else {
            return $file["tmp_name"];
        }
    }

    public function getUploadedFiles($key, $index, $allowedTypes = array())
    {
        $error = false;
        if (!isset($_FILES[$key]) or !is_uploaded_file($_FILES[$key]["tmp_name"][$index])) {
            $error = "Upload Failed";
        } elseif (count($allowedTypes) != null and in_array(pathinfo($_FILES[$key]["name"][$index], PATHINFO_EXTENSION), $allowedTypes)) {
            $error = "Invalid file type";
        } else {
            $file = $_FILES[$key];
            switch ($file["error"]) {
            case 1:
            case 2:
                $error = sprintf("file Upload is Too Big Max Is %d", ini_get("upload_max_filesize"));
                break;
            case 3:
            case 4:
            case 6:
            case 7:
            case 8:
                $error = "[UPLOAD:FAIL]";
        }
        }
        if ($error) {
            throw new \Exception($error);
        } else {
            return $file["tmp_name"][$index];
        }
    }

    public function save($key, $destination)
    {
        if (empty($_FILES[$key]['tmp_name'])) {
            return;
        }
        $fileName = basename($_FILES[$key]['name']);
        try {
            $file = $this->getUploadedFile($key);
            $save = $this->saveAs(
                $file,
                $this->config['baseDir'].self::DS.$destination.self::DS.$fileName
            );
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $save;
    }

    public function saveAs($source, $destination)
    {
        if (!move_uploaded_file($source, $destination)) {
            throw new \Exception(sprintf("Failed to upload file to %s", $destination));
        
            return;
        }
        return $destination;
    }

    public function getImageInfo($source)
    {
        $size = getimagesize($source);
        if ($size === false) {
            throw new \Exception("File Is Not Image");
        }
    
        return $size;
    }

    public function createImageFrom($source)
    {
        $type = $this->getImageInfo($source)[2];

        switch ($type) {
        case 1:
            $image = @\imagecreatefromgif($source);
            break;
        case 2:
            $image = @\imagecreatefromjpeg($source);
            break;
        case 3:
            $image = @\imagecreatefrompng($source);
        }
        if (!$image) {
            throw new \Exception("File Is Not Image");
        }

        return $image;
    }

    public function createImage($width, $height, $outputType = "png")
    {
        $newImage = imagecreatetruecolor($width, $height);
        if ($outputType == "png") {
            imagecolortransparent($newImage, imagecolorallocate($newImage, 0, 0, 0));
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }
    
        return $newImage;
    }

    public function saveAsImage($source, $destination, $width, $height, $sizeMode = "max", $watermark = false)
    {
        if ($sizeMode == "origin") {
            return $this->saveAs($source, $destination);
        }
    
        $image = $this->createImageFrom($source);
        list($sourceWidth, $sourceHeight, $type) = $this->getImageInfo($source);
        $outputType = pathinfo($destination, PATHINFO_EXTENSION);
        $types = [1 => "gif", 2 => "jpg", 3 => "png"];

        if (!$outputType or !in_array($outputType, $types)) {
            $outputType = $types[$type];

            if ($outputType == "gif") {
                $outputType = "png";
            }
        } else {
            $destination = substr($destination, 0, -strlen($outputType) - 1);
        }

        $widthRatio = $width / $sourceWidth;
        $heightRatio = $height / $sourceHeight;

        if ($sizeMode == "crop") {
            $ratio = max($widthRatio, $heightRatio);
        } else {
            $ratio = min($widthRatio, $heightRatio);
        }

        if ($sizeMode == "min") {
            $ratio = min(1, $ratio);
        } elseif ($sizeMode == "max") {
            $ratio = max(1, $ratio);
        }

        $newWidth = ceil($ratio * $sourceWidth);
        $newHeight = ceil($ratio * $sourceHeight);

        if ($sizeMode == "max" or $sizeMode == "min") {
            $width = $newWidth;
            $height = $newHeight;
        }

        $newImage = $this->createImage($width, $height, $outputType);

        $x = $newWidth / 2 - $width / 2;
        $y = $newHeight / 2 - $height / 2;
        imagecopyresampled($newImage, $image, -$x, -$y, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
        if ($watermark) {
            $newImage = $this->setWaterMark($newImage);
        }
    
        switch ($outputType) {
        case "png":
            imagepng($newImage, $outputFile = $destination.".png");
            break;

        case "gif":
            imagegif($newImage, $outputFile = $destination.".gif");
            break;

        default:
            imagejpeg($newImage, $outputFile = $destination.".jpg", $this->config["jpegQuality"]);
        }
        imagedestroy($newImage);
        imagedestroy($image);

        return $outputFile;
    }

    public function setWaterMark($source)
    {
        if (isset($this->config["watermarkImage"]) and
        $this->config["watermarkImage"] != null and
        $copy = @imagecreatefromstring(
            file_get_contents(
                $this->config["baseDir"].self::DS.$this->config["watermarkImage"]
            )
        )) {
            $imageX = imagesx($source);
            $imageY = imagesy($source);

            $watermarkX = imagesx($copy);
            $watermarkY = imagesy($copy);

            $width = intval(min($imageX / 2.5, $watermarkX, 128));
            $height = intval(min($imageY / 2.5, $watermarkY, 64));

            $ratioX = $width / $watermarkX; // width
            $ratioY = $height / $watermarkY; // height

        if (($watermarkX <= $width) and ($imageY <= $height)) {
            $destinationW = $watermarkX;
            $destinationH = $watermarkY;
        } elseif (($ratioX * $watermarkY) < $height) {
            $destinationH = ceil($ratioX * $watermarkY);
            $destinationW = $width;
        } else {
            $destinationW = ceil($ratioY * $watermarkX);
            $destinationH = $height;
        }
            imagecopyresampled($source, $copy, $imageX-$destinationW, $imageY-$destinationH, 0, 0, $destinationW, $destinationH, $watermarkX, $watermarkY);
            imagedestroy($copy);
        }
        return $source;
    }
}
