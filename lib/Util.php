<?php

class Util {

    public static function toDate($date) {
        $days = array('Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy');
        return $days[date('w', $date)] . ', ' . date("d/m/Y H:i", $date) . ' GMT+7';
    }

    public static function removeTag($input, $tags = null) {
        if (null === $tags) {
            return strip_tags($input);
        }
        return preg_replace('#</?(?:' . (is_array($tags) ? implode('|', $tags) : $tags) . ')[^>]*>#i', '', $input);
    }

    public static function unescape($str) {
        return preg_replace_callback("#u([0-9a-f]{4})#uis", create_function('$matches', 'return html_entity_decode(\'&#x\'.$matches[1].\';\', ENT_QUOTES, \'UTF-8\');'), $str);
    }

    public static function validate($value, $type = 'string', $alert = '') {
        switch ($type) {
            case 'number':
                if (!preg_match('#^\d+$#', $value))
                    return $alert;
                break;
            case 'phone':
                if (!preg_match('#^\d{10,11}$#', $value))
                    return $alert;
                break;
                break;
            case 'email':
                if (!preg_match('#^[\w\d\-\.]+@[\w\d]+(?:\.[a-z]{2,4})+$#i', $value))
                    return $alert;
                break;
            case 'text':
                if (preg_match('#<[^>]+>#', $value) || (strlen($value) < 10 || strlen($value) > 1000))
                    return $alert;
                break;
            case 'string':
            default:
                if (!preg_match('#^[\P{Common}\w\d\-\s]+$#ius', $value) || (strlen($value) < 6 || strlen($value) > 140))
                    return $alert;
                break;
        }

        return true;
    }

    public static function getTimeFromString($str, $int = true) {
        if (preg_match('#\d+([^\d:]+)\d+\\1\d+#s', $str, $date)) {
            $date = $date [0];
            $reverse = false;
            if (!preg_match('#^\d{4}#s', $date) && preg_match('#[^\d]+\d{2}$#s', $date)) {
                $date = preg_replace('#[^\d]+(\d{2})$#s', '-' . (intval(date('Y') / 100)) . '$1', $date);
                $reverse = true;
            }
            $reverse = $reverse && preg_match('#\d{4}$#s', $date) ? true : false;
            $date = preg_split('#[^\d]#s', $date, null, PREG_SPLIT_NO_EMPTY);

            if ($reverse) {
                $date = array_reverse($date);
            }
            $date = implode('-', $date);
            if (preg_match('#\d+\s*:\s*\d+\s*:\s*\d+#s', $str, $time)) {
                $time = $time [0];
            } else if (preg_match('#\d+\s*:\s*\d+#s', $str, $time)) {
                $time = $time [0] . ':00';
            } else if (preg_match('#(\d+)\s*h\s*(\d+)?#is', $str, $time)) {
                $time = $time [1] . ':' . (isset($time [2]) ? $time [2] : '00') . ':00';
            } else {
                $time = null;
            }
            $date = $date . ' ' . $time;
            $datetime = strtotime($date);
        } else if (preg_match('#(?:ngày|tiếng|giờ|phút)\s*trước#uis', $str)) {
            preg_match('#(\d+)\s*ngày#uis', $str, $days);
            $days = isset($days [1]) ? $days [1] : 0;
            preg_match('#(\d+)\s*(?:giờ|tiếng)#uis', $str, $hours);
            $hours = isset($hours [1]) ? $hours [1] : 0;
            preg_match('#(\d+)\s*phút#uis', $str, $minutes);
            $minutes = isset($minutes [1]) ? $minutes [1] : 0;
            $datetime = time() - (($days * 86400) + ($hours * 3600) + ($minutes * 60));
        }

        return isset($datetime) ? ($int ? $datetime : date('d-m-Y H:i:s', $datetime)) : 0;
    }

    public static function getDiffTime($time) {
        $time = (is_numeric($time) ? $time : strtotime($time));
        $seconds = abs(time() - $time);
        if ($seconds < 60) {
            $response = $seconds . ' giây trước';
        } else if ($seconds >= 60 && $seconds < 3600) {
            $response = round($seconds / 60) . ' phút trước';
        } else if ($seconds >= 3600 && $seconds < 86400) {
            $minutes = round(($seconds % 3600) / 60);
            $response = round($seconds / 3600) . ' giờ' . ($minutes > 0 ? ', ' . $minutes . ' phút' : '') . ' trước';
        } else if ($seconds >= 86400 && $seconds < 604800) {
            $hours = round(($seconds % 86400) / 3600);
            $response = round($seconds / 86400) . ' ngày' . ($hours > 0 ? ', ' . $hours . ' giờ' : '') . ' trước';
        } else {
            $response = date('d.m.Y', $time);
        }
        return $response;
    }

    public static function stripWordTags($words) {
        $words = preg_replace('#\[(if|endif)[^\]]*\][^\[]*(\[endif[^\]]*\])?#uis', '', $words);
        $words = preg_replace('#\[(if|endif)[^\]]*\]#uis', '', $words);
        $words = preg_replace('#<\!.*?>#uis', '', $words);
        $words = preg_replace('#<(xml|style)[^>]*>.*?</\1>#is', '', $words);
        $words = preg_replace('#</?(a|[a-z]\:)[^>]*>#is', '', $words);
        return $words;
    }

    public static function getSomeWords($str, $count = 10) {
        $str = trim(strip_tags($str));
        $words = preg_split('#\s+#is', $str);
        $min = min($count, count($words));

        return implode(' ', array_slice($words, 0, $min)) . ($min == $count ? '...' : '');
    }

    public static function toUnsign($str, $splitChar = '-') {
        $str = str_replace(array('ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'Ấ', 'Ầ', 'Ẫ', 'Ậ', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'á', 'à', 'ả', 'ã', 'ạ', 'â', 'ă', 'Á', 'À', 'Ả', 'Ã', 'Ạ', 'Â', 'Ă', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'í', 'ì', 'ỉ', 'ĩ', 'ị', 'Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'Ố', 'Ồ', 'Ổ', 'Ô', 'Ộ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Đ', 'đ', 'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ơ', 'Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ơ', 'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'e', 'e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'E', 'e', 'e', 'e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'E', 'E', 'i', 'i', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'o', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'O', 'o', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'O', 'u', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'U', 'y', 'y', 'y', 'y', 'y', 'Y', 'Y', 'Y', 'Y', 'Y', 'D', 'd', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'u', 'u', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'U', 'U'), $str);

        return strtolower(preg_replace(array('/[^a-zA-Z0-9\s-]/', '/[\s-]+/', '/^[\s-]|[\s-]$/'), array('', $splitChar, ''), $str));
    }

    public static function paginate($numberPages, $currentPage, $url, $className = 'pagination', $page = 'page-', $ext = '', $unlimited = false) {
        $html = '';
        if (is_numeric($numberPages) && $numberPages > 1) {

            $html .= '<div class="' . $className . '">';

            if ($currentPage > 1) {
                $html .= '<a rel="nofollow" href="' . $url . ($currentPage < 2 ? '' : $page . ($currentPage - 1)) . $ext . '" >Previous</a>';
            }

            for ($i = $currentPage - 2; $i < $currentPage; $i ++) {
                if ($i > 0) {
                    $html .= '<a href="' . $url . $page . $i . $ext . '" >' . $i . '</a>';
                }
            }

            for ($i = $currentPage; $i < $currentPage + 3; $i ++) {
                if ($i <= $numberPages) {
                    if ($i != $currentPage) {
                        $html .= '<a rel="nofollow" href="' . $url . $page . $i . $ext . '" >' . $i . '</a>';
                    } else if ($i > 0 && $i == $currentPage) {
                        $html .= '<a rel="nofollow" class="active" href="' . $url . $page . $i . $ext . '" >' . $i . '</a>';
                    }
                }
            }

            if ($currentPage + 2 < $numberPages) {
                $html .= '<span>...</span>';
            }

            if ($unlimited) {
                $html .= '<a href="' . $url . $page . ($currentPage + 1) . $ext . '" >Next</a>';
            } else {
                if ($currentPage < $numberPages) {
                    $html .= '<a rel="nofollow" href="' . $url . $page . ($currentPage + 1) . $ext . '" >Next</a>';
                }
            }
            $html .= '</div>';
        }
        return $html;
    }

    public static function getThumbSrc($src, $w = 0, $h = 0) {
        $src = preg_replace('#/thumb/(.*?)_thumb_\d+x\d+#is', '/$1', $src);
        //return preg_replace('#(http://[^/]+)/(resize_[^/]+/)?#s', $w ? '$1/' : '$1/', $src);
        return preg_replace('#(http://[^/]+)/(resize_[^/]+/)?#s', $w ? '$1/resize_' . $w . 'x' . $h . '/' : '$1/', $src);
        return $src;
    }

    public static function getCropSrc($src, $w = 0, $h = 0) {
        return preg_replace('#(http://[^/]+)/(crop_[^/]+/)?#s', $w ? '$1/crop_' . $w . 'x' . $h . '/' : '$1/', $src);
        return $src;
    }

    public static function utf8ToBase64($str) {
        if ($str) {
            return base64_encode($str);
        } else {
            return null;
        }
    }

    public static function getCatRelate($categoryId) {
        switch ($categoryId) {
            case 1:
                return 9;
                break;
            case 9:
                return 1;
                break;
            case 3:
                return 4;
                break;
            case 4:
                return 3;
                break;
            case 2:
                return 40;
                break;
            case 40:
                return 2;
                break;
            case 7:
                return 8;
                break;
            case 8:
                return 7;
                break;
            case 43:
                return 51;
                break;
            case 51:
                return 43;
                break;
            case 45:
                return 46;
                break;
            case 46:
                return 45;
                break;
            case 10:
                return 50;
                break;
            case 50:
                return 10;
                break;
            case 41:
                return 42;
                break;
            case 42:
                return 1;
                break;
            default:
                return 9;
                break;
        }
    }

}

?>