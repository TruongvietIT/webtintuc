<?php

class Transfer {

    protected static $_instance;
    protected $_errors = array();

    public function getError() {
        return $this->_errors;
    }

    public function setError($error) {
        $this->_errors[] = $error;
    }

    public static function getInstance() {
        if (false === isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function setOption($url, $refer = null, $ispost = false, $data = null, $cookies = null, $timeout = 10) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.52 Safari/536.5');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_REFERER, $refer == null ? $url : $refer);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');

        if ($ispost && $data != null) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        if (null !== $cookies) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookies);
        }
        //var_dump(curl_getinfo($curl));
        //var_dump(curl_error($curl));
        return $curl;
    }

    public function getContent($url, $refer = null, $ispost = false, $data = null, $cookies = null, $timeout = 10) {
        $curl = self::setOption($url, $refer, $ispost, $data, $cookies, $timeout);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function getMultiContents($urls, $refer = null, $ispost = false, $data = null, $cookies = null, $timeout = 10) {
        if (!is_array($urls)) {
            return null;
        }

        $mch = curl_multi_init();
        $ch = array();
        $running = null;
        $nums = count($urls);
        $response = array();

        for ($i = 0; $i < $nums; ++$i) {
            if (is_array($urls[$i])) {
                $ref = isset($urls[$i]['refer']) ? $urls[$i]['refer'] : $refer;
                $isp = isset($urls[$i]['ispost']) ? $urls[$i]['ispost'] : $ispost;
                $coo = isset($urls[$i]['cookies']) ? $urls[$i]['cookies'] : $cookies;
                $da = isset($urls[$i]['data']) ? $urls[$i]['data'] : $data;
                $tim = isset($urls[$i]['timeout']) ? $urls[$i]['timeout'] : $timeout;
                $url = isset($urls[$i]['url']) ? $urls[$i]['url'] : $urls[$i][0];
                $ch[$i] = self::setOption($url, $ref, $isp, $da, $coo, $tim);
            } else {
                $ch[$i] = self::setOption($urls[$i], $refer, $ispost, $data, $cookies, $timeout);
            }
            curl_multi_add_handle($mch, $ch[$i]);
        }

        do {
            $status = curl_multi_exec($mch, $running);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $running);

        for ($i = 0; $i < $nums; ++$i) {
            $response[$i] = curl_multi_getcontent($ch[$i]);
        }

        for ($i = 0; $i < $nums; ++$i) {
            curl_multi_remove_handle($mch, $ch[$i]);
        }

        curl_multi_close($mch);
        return $response;
    }

    public function saveFile($url, $path, $fName = null, $currentPath = false) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);

        $path = $path . ($currentPath == false ? '/' . date('Y') . '/' . date('m') . '/' . date('d') : '');
        if (false == file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $parts = pathinfo($url);
        $fileExt = preg_replace('#[\?\&].*#is', '', $parts['extension']);
        $fileName = $path . '/' . ($fName !== null ? $fName . '.' . $fileExt : basename($url));
        $fp = fopen($fileName, 'w+');

        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_exec($curl);
        curl_close($curl);
        fclose($fp);
        return $fileName;
    }

    public function curlGetFileSize($url) {
        // Assume failure.
        $result = -1;
        $curl = curl_init($url);

        // Issue a HEAD request and follow any redirects.
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($curl);
        curl_close($curl);

        if ($data) {
            $content_length = "unknown";
            $status = "unknown";

            if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
                $status = (int) $matches[1];
            }

            if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
                $content_length = (int) $matches[1];
            }

            // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
            if ($status == 200 || ($status > 300 && $status <= 308)) {
                $result = $content_length;
            }
        }
        return $result;
    }

    public function UploadImageContent($image = array(), $fptPath, $makeDir = true) {
        $fileType = $image['type'];
        $fileName = $image['name'];
        $fileTmp = $image['tmp_name'];
        $fileErrors = $image['error'];

        if (empty($fileName)) {
            $this->setError('Have some error! Please check this file!');
            return null;
        }

        if ($makeDir == true) {
            if (isset($image['dir_path']) && !empty($image['dir_path'])) {
                $dirPath = $image['dir_path'];
            } else {
                $dirPath = date('Y') . '/' . date('m') . '/' . date('d') . '/';
                if (!is_dir($fptPath . $dirPath)) {
                    preg_match('#ftp://([^:]+):([^@]+)@([^/]+)#is', $fptPath, $acc);
                    $conn_id = ftp_connect($acc[3]);
                    if ($conn_id && ftp_login($conn_id, $acc[1], $acc[2])) {
                        ftp_mkdir($conn_id, $dirPath);
                        ftp_close($conn_id);
                        unset($conn_id);
                    }
                }
            }
        } else {
            $dirPath = '';
        }

        $ch = curl_init();
        $fp = $fileTmp;
        if (preg_match('#^([^\.]+)(\.\w+)$#is', $fileName, $match)) {
            $fileName = Util::toUnsign($match[1]) . $match[2];
        }

        curl_setopt($ch, CURLOPT_URL, $fptPath . $dirPath . $fileName);
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_exec($ch);
        fclose($fp);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->setError($error);
            return null;
        }

        return $dirPath . $fileName;
    }

    private function __curlFtpUpload($fileInfo, $fptPath, $makeDir = true) {
        $fileType = $fileInfo['type'];
        $fileName = $fileInfo['name'];
        $fileSize = $fileInfo['size'];
        $fileTmp = $fileInfo['tmp_name'];
        $fileErrors = $fileInfo['error'];

        if ($fileSize <= 0 || $fileErrors > 0 || empty($fileName)) {
            $this->setError('Have some error! Please check this file!');
            return null;
        }

        if ($makeDir == true) {
            $dirPath = date('Y') . '/' . date('m') . '/' . date('d') . '/';
            if (!is_dir($fptPath . $dirPath)) {
                preg_match('#ftp://([^:]+):([^@]+)@([^/]+)#is', $fptPath, $acc);
                $conn_id = ftp_connect($acc[3]);
                if ($conn_id && ftp_login($conn_id, $acc[1], $acc[2])) {
                    ftp_mkdir($conn_id, $dirPath);
                    ftp_close($conn_id);
                    unset($conn_id);
                }
            }
        } else {
            $dirPath = '';
        }
        $ch = curl_init();
        $fp = fopen($fileTmp, 'r');
        if (preg_match('#^([^\.]+)(\.\w+)$#is', $fileName, $match)) {
            $fileName = Util::toUnsign($match[1]) . $match[2];
        }

        curl_setopt($ch, CURLOPT_URL, $fptPath . $dirPath . $fileName);
        curl_setopt($ch, CURLOPT_UPLOAD, 1);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_exec($ch);
        fclose($fp);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            $this->setError($error);
            return null;
        }
        return array('src' => $dirPath . $fileName, 'size' => $fileSize);
    }

    public function curlFtpUpload($file, $fptPath, $makeDir = true) {
        if (isset($_FILES[$file])) {
            $fileTypes = $_FILES[$file]['type'];
            $fileNames = $_FILES[$file]['name'];
            $fileSizes = $_FILES[$file]['size'];
            $fileTmps = $_FILES[$file]['tmp_name'];
            $fileErrors = $_FILES[$file]['error'];
            if (is_array($fileNames)) {
                $count = count($fileNames);
                $data = array();
                for ($i = 0; $i < $count; $i ++) {
                    $data[] = $this->__curlFtpUpload(array('type' => $fileTypes[$i],
                        'name' => $fileNames[$i],
                        'size' => $fileSizes[$i],
                        'tmp_name' => $fileTmps[$i],
                        'error' => $fileErrors[$i]), $fptPath, $makeDir);
                }
                return $data;
            } else {
                return $this->__curlFtpUpload(array('type' => $fileTypes,
                            'name' => $fileNames,
                            'size' => $fileSizes,
                            'tmp_name' => $fileTmps,
                            'error' => $fileErrors), $fptPath, $makeDir);
            }
        } else if ($imageinfo = getimagesize($file)) { // upload from url
            $fileInfo = array();

            $fileInfo['type'] = $imageinfo['mime'];

            if (preg_match('#/([^/]+)$#is', $file, $fileName)) {

                $fileInfo['name'] = $fileName[1];
            } else
                return;

            $fileInfo['size'] = $this->curlGetFileSize($file);

            $fileInfo['tmp_name'] = $file;

            $fileInfo['error'] = 0;

            return $this->__curlFtpUpload($fileInfo, $fptPath, $makeDir);
        }

        return null;
    }

    public function ftpUpload($file, $fptPath, $makeDir = true) {
        if (!isset($_FILES[$file])) {
            return null;
        }

        $fileType = $_FILES[$file]['type'];
        $fileName = $_FILES[$file]['name'];
        $fileSize = $_FILES[$file]['size'];
        $fileTmp = $_FILES[$file]['tmp_name'];
        $fileErrors = $_FILES[$file]['error'];

        if ($fileSize <= 0 || $fileErrors > 0 || empty($fileName)) {
            $this->setError('Have some error! Please check this file!');
            return null;
        }


        preg_match('#ftp://([^:]+):([^@]+)@([^/]+)(/.*)?#is', $fptPath, $acc);
        $conn_id = ftp_connect($acc[3]);

        if ($conn_id && ftp_login($conn_id, $acc[1], $acc[2])) {
            if ($makeDir == true) {
                $prefix = (isset($acc[4]) ? ltrim($acc[4], '/') : '');
                $dirPath = date('Y') . '/' . date('m') . '/' . date('d') . '/';

                if (!is_dir($fptPath . $dirPath)) {
                    $parts = explode('/', $prefix . $dirPath); // 2013/06/11/username
                    $currentPart = '';
                    foreach ($parts as $part) {
                        $currentPart .= '/' . $part;
                        if ($part && !is_dir($fptPath . ltrim($currentPart, '/'))) {
                            ftp_mkdir($conn_id, $currentPart);
                        }
                    }
                    //ftp_mkdir($conn_id, $dirPath);
                }
            } else {
                $dirPath = '';
            }

            $fp = fopen($fileTmp, 'r');
            if (preg_match('#^([^\.]+)(\.\w+)$#is', $fileName, $match)) {
                $fileName = Util::toUnsign($match[1]) . $match[2];
            }

            $bool = ftp_fput($conn_id, $prefix . $dirPath . $fileName, $fp, FTP_BINARY);
            ftp_close($conn_id);
            unset($conn_id);
            fclose($fp);
            unset($fp);

            if (!$bool) {
                $this->setError('Can not upload!');
                return null;
            }

            return array('src' => $dirPath . $fileName, 'size' => $fileSize);
        }

        $this->setError('Can not Ftp login to upload!');
        return null;
    }

    public function ftpRemoveFile($fptPath, $filePath) {
        preg_match('#ftp://([^:]+):([^@]+)@([^/]+)#is', $fptPath, $acc);
        $conn_id = ftp_connect($acc[3]);
        if ($conn_id && ftp_login($conn_id, $acc[1], $acc[2])) {
            @ftp_delete($conn_id, $filePath);
            ftp_close($conn_id);
            unset($conn_id);
        }
    }

    public function upload($file, $path, $fName = null) {
        if (!isset($_FILES[$file])) {
            return null;
        }

        $fileType = $_FILES[$file]['type'];
        $fileName = $_FILES[$file]['name'];
        $fileSize = $_FILES[$file]['size'];
        $fileTmp = $_FILES[$file]['tmp_name'];
        $fileErrors = $_FILES[$file]["error"];

        if ($fileSize <= 0 || $fileErrors > 0 || empty($fileName)) {
            $this->setError('Have some error! Please check this file!');
            return null;
        }

        $path = $path . '/' . date('Y') . '/' . date('m') . '/' . date('d');
        if (false === file_exists($path)) {
            @mkdir($path, 0777, true);
        }

        $getExt = explode('.', $fileName);
        $fileExt = array_pop($getExt);

        if ($fName === null) {
            $tempName = trim(preg_replace('#[^a-z0-9\.]#is', '_', implode('', $getExt)), '_');
            $renamedFile = $path . '/' . $tempName . date('H_i_s_u') . '.' . $fileExt;
        } else {
            $renamedFile = $path . '/' . $fName . '.' . $fileExt;
        }

        if (!file_exists($renamedFile)) {
            move_uploaded_file($_FILES[$file]["tmp_name"], $renamedFile);
            return $renamedFile;
        } else {
            $this->setError('File ' . $fileName . ' is esxits! Please select an onther file!');
        }
        return null;
    }

    public function resizeImageFromUrl($url, $thumbWidth, $thumbHeight = 0) {

        //Load image and size
        $url = str_replace(' ', '%20', urldecode($url));
        $ext = strtolower(preg_replace('#[\&\?].*$#is', '', end(explode('.', $url))));
        $headers = get_headers($url, 1);

        if (isset($headers['Content-Type'])) {
            if (preg_match('#image/[a-z\-]+#is', $headers['Content-Type'], $ext)) {
                $ext = preg_replace('#^[a-z]+[\-\/]#is', '', $ext[0]);
                unset($headers);
            }
        }



        switch (true) {
            case false !== stripos($ext, 'gif'):
                $ext = 'gif';
                $img = imagecreatefromgif($url);
                break;
            case false !== stripos($ext, 'png'):
                $ext = 'png';
                $img = imagecreatefrompng($url);
                break;
            case false !== stripos($ext, 'bmp'):
                $ext = 'bmp';
                $img = imagecreatefromwbmp($url);
                break;
            case false !== stripos($ext, 'jpg'):
            case false !== stripos($ext, 'jpeg'):
            default:
                $img = imagecreatefromjpeg($url);
                $ext = 'jpg';
                break;
        }

        if (isset($img) && is_resource($img)) {
            $width = imagesx($img);
            $height = imagesy($img);
            $new_width = $thumbWidth > $width ? $width : $thumbWidth;
            $new_height = $thumbHeight == 0 ? floor(($new_width / $width) * $height) : $thumbHeight;

            $tmp_img = imagecreatetruecolor($new_width, $new_height);
            imagecopyresized($tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            header("Content-type: Image/" . $ext);

            switch ($ext) {
                case 'gif':
                    $img = imagegif($tmp_img, null);
                    break;
                case 'png':
                    $img = imagepng($tmp_img, null, 9);
                    break;
                case 'bmp':
                    $img = image2wbmp($tmp_img, null);
                    break;
                case 'jpg':
                case 'jpeg':
                default:
                    $img = imagejpeg($tmp_img, null, 100);
                    break;
            }

            imagedestroy($tmp_img);
            unset($img, $tmp_img);
            return true;
        }

        return false;
    }

    public function resize($src, $savePath, $newWidth, $newHeight, $option = 'crop', $qualtity = 100) {
        if (!file_exists($src)) {
            return false;
        }

        preg_match('#\.(\w+)$#is', $src, $ext);
        $ext = isset($ext[1]) ? strtolower($ext[1]) : '';

        switch ($ext) {
            case 'gif':
                $img = imagecreatefromgif($src);
                break;
            case 'png':
                $img = imagecreatefrompng($src);
                break;
            case 'bmp':
                $img = imagecreatefromwbmp($src);
                break;
            case 'jpg':
            case 'jpeg':
                $img = imagecreatefromjpeg($src);
                break;
        }

        if (isset($img) && is_resource($img)) {
            $width = imagesx($img);
            $height = imagesy($img);

            switch ($option) {
                case 'exact':
                    $optimalWidth = $newWidth;
                    $optimalHeight = $newHeight;
                    break;
                case 'portrait':
                    $optimalHeight = $newHeight;
                    $optimalWidth = $newHeight * ($width / $height);
                    break;

                case 'landscape':
                    $optimalWidth = $newWidth;
                    $optimalHeight = $newWidth * ($height / $width);
                    break;
                case 'crop':
                    $heightRatio = $height / $newHeight;
                    $widthRatio = $width / $newWidth;
                    if ($heightRatio < $widthRatio) {
                        $optimalRatio = $height / $newHeight;
                    } else {
                        $optimalRatio = $width / $newWidth;
                    }
                    $optimalHeight = $height / $optimalRatio;
                    $optimalWidth = $width / $optimalRatio;
                    break;

                default: //auto
                    if ($height < $width) {
                        $optimalWidth = $newWidth;
                        $optimalHeight = $newWidth * ($height / $width);
                    } elseif ($height > $width) {
                        $optimalWidth = $newHeight * ($width / $height);
                        $optimalHeight = $newHeight;
                    } else {
                        if ($newHeight < $newWidth) {
                            $optimalWidth = $newWidth;
                            $optimalHeight = $newWidth * ($height / $width);
                        } else if ($newHeight > $newWidth) {
                            $optimalWidth = $newHeight * ($width / $height);
                            $optimalHeight = $newHeight;
                        } else {
                            $optimalWidth = $newWidth;
                            $optimalHeight = $newHeight;
                        }
                    }

                    break;
            }

            $resizeImage = imagecreatetruecolor($optimalWidth, $optimalHeight);
            imagecopyresampled($resizeImage, $img, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $width, $height);

            if ($option == 'crop') {
                $cropStartX = ( $optimalWidth / 2) - ( $newWidth / 2 );
                $cropStartY = ( $optimalHeight / 2) - ( $newHeight / 2 );
                $cropImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($cropImage, $resizeImage, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight);
                $resizeImage = $cropImage;
            }

            $path = preg_replace('#/[^/]+$#is', '', $savePath);
            if (false === file_exists($path)) {
                mkdir($path, 0777, true);
            }

            switch ($ext) {
                case 'gif':
                    $img = imagegif($resizeImage, $savePath);
                    break;
                case 'png':
                    $img = imagepng($resizeImage, $savePath, round(($qualtity / 100) * 9));
                    break;
                case 'bmp':
                    $img = image2wbmp($resizeImage, $savePath, round(($qualtity / 100) * 255));
                    break;
                case 'jpg':
                case 'jpeg':
                default:
                    $img = imagejpeg($resizeImage, $savePath, $qualtity);
                    break;
            }

            imagedestroy($resizeImage);
            unset($img, $resizeImage);
            return true;
        }
        return false;
    }

}

?>