<?php
/**
 * Rakshit Patel Copyright (c) 2018.
 */

namespace App\Facades;

use DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Mail;

class LiveServices extends Facade
{

    /**
     * @return string
     */
    public static function uniqueValue()
    {
        return md5(date("Y/m/d") . date('m/d/Y h:i:s a', time()) . uniqid());
    }

    /**
     * @return \SimpleXMLElement
     */
    public static function svnLog()
    {
        return simplexml_load_file(env('SVN_LOG_URL_PATH'));
    }

    /**
     * @param $string
     * @return null|string|string[]
     */
    public static function seoUrl($string)
    {
        //Lower case everything
        $string = strtolower(trim($string));
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/i", " ", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

    /**
     * @param $str
     * @param array $noStrip
     * @return mixed|null|string|string[]
     */
    public static function camelCase($str, array $noStrip = [])
    {
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $str);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        $str = lcfirst($str);

        return $str;
    }

    /**
     * @param $process_arr
     * @return bool
     */
    public static function has_dupes($process_arr)
    {
        $process_arr_unique = array_unique($process_arr);
        if (count($process_arr_unique) < count($process_arr)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $arr
     * @return false|int|string
     */
    public static function arrayEmptyElementExists($arr)
    {
        return array_search("", $arr);
    }

    /**
     * @param $obj
     * @param $prop
     * @return mixed
     */
    public static function accessProtected($obj, $prop)
    {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    /**
     * @param $viewString
     * @param $data
     * @param $fromEmailString
     * @param $fromNameString
     * @param $subjectString
     * @param $toEmailArray
     */
    public static function sendMail($viewString, $data, $fromEmailString, $fromNameString, $subjectString, $toEmailArray)
    {
        define('fromEmailString', $fromEmailString);
        define('fromNameString', $fromNameString);
        define('subjectString', $subjectString);
        define('toEmailArray', $toEmailArray);
        Mail::send($viewString, $data, function ($message) {
            $message->from(fromEmailString, fromNameString);
            $message->subject(subjectString);
            $message->to(toEmailArray);
        });
    }

    public static function toSqlWithBinding($object,$return=false)
    {
        $query = $object->toSql();
        $bindings = $object->getBindings();
        foreach ($bindings as $key => $binding) {
            if (!is_numeric($binding)) {
                $binding = "'" . $binding . "'";
            }
            $regex = is_numeric($key) ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/" : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";
            $query = preg_replace($regex, $binding, $query, 1);
        }
        if ($return) return $query;
        echo $query;
        exit;
    }

    /**
     * @return array
     */
    public static function getAllEmailAddress()
    {
        $emails = array();
        /*        foreach (\App\Admin::pluck('email', 'name') as $name => $email) {
                    $emails += [$name => $email];
                }*/
        return $emails;
    }

    public static function tableStructure($table)
    {
        $object = DB::select("SELECT *
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE table_name = '$table'"
            . "");
        $info = array();

        foreach ($object as $objects) {
            $info[$objects->COLUMN_NAME]['name'] = $objects->COLUMN_NAME;
            $info[$objects->COLUMN_NAME]['id'] = $objects->COLUMN_NAME;
            $info[$objects->COLUMN_NAME]['type'] = $objects->DATA_TYPE;
        }
        return $info;
    }

    /**
     * @param $array
     * @return string
     */
    public static function htmlOptionFromDB($array)
    {
        $optionString = '';
        $selected_value = '';
        if (isset($array['selected_value'])) {
            $selected_value = $array['selected_value'];
        }
        if (isset($array['data_in_array'])) {
            if (isset($array['value'])) {
                foreach ($array['data_in_array'] as $key => $data) {
                    $optionString .= "<option value='" . $key . "'"
                        . ($key == $selected_value ? 'selected ' . $key : '')
                        . ">" . $data[$array['value']] . "</option>";
                }
            } else {
                foreach ($array['data_in_array'] as $key => $data) {
                    $optionString .= "<option value='" . $key . "'"
                        . ($key == $selected_value ? 'selected' : '')
                        . ">" . $data . "</option>";
                }
            }
            return $optionString;
        }
        if (isset($array['table']) && isset($array['value'])) {
            $dataSql = "SELECT
                               id," . $array['value'] . "
                               FROM
                               " . $array['table'] . " ";
            if (isset($array['raw_sql'])) {
                $dataSql .= $array['raw_sql'];
            }
            $object = DB::select($dataSql);
            if (isset($array['table_query'])) {
                $object->$array['table_query'];
            }
            $finalObject = $object;
            if ($finalObject) {
                $selectKey = 'id';
                if (isset($array['key'])) {
                    $selectKey = $array['key'];
                }
                $selectValue = $array['value'];
                foreach ($finalObject as $finalObjects) {
                    $optionString .= "<option value='" . $finalObjects->$selectKey . "'"
                        . ($finalObjects->$selectKey == $selected_value ? 'selected' : '')
                        . ">" . $finalObjects->$selectValue . "</option>";
                }
            }
            return $optionString;
        }
    }

    /**
     * @param $arr
     * @param string $from
     * @return array
     */
    public static function fixArrayKey($arr, $from = '_')
    {
        $arr = array_combine(array_map(function ($str) use ($from) {
            return str_replace(" ", $from, $str);
        }, array_keys($arr)), array_values($arr));
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                self::fixArrayKey($arr[$key]);
            }
        }
        return $arr;
    }

    /**
     * @param $str
     * @param array $arr
     * @param string $checkType
     * @return bool
     */
    public static function findArrayWordsInString($str, array $arr, $checkType = 'any_word')
    {
        switch ($checkType) {
            case 'any_word':
                foreach ($arr as $a) {
                    if (stripos($str, $a) !== false) return true;
                }
                break;
            case 'all_words':
                foreach ($arr as $a) {
                    if (stripos($str, $a) === false) {
                        return false;
                    }
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * @param $string
     * @param string $separator
     * @return string
     */
    public static function returnUrlWithDynamicBaseUrl($string, $separator = 'public')
    {
        $string = explode($separator, $string);
        return asset($string[1]);
    }

    /**
     * @param $string
     * @return mixed
     */
    public static function json_validate_or_json_decode($string, $jsonkey = null)
    {
        $key = 0;
        if ($jsonkey) {
            $key = $jsonkey;
        }

        // decode the JSON data
        $result = json_decode($string);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }
        if ($error !== '') {
            return $string;
        }

        $stringArray = json_decode($string, true);


        if (isset($stringArray[$key]) and ($stringArray[$key] != '""' or $stringArray[$key] != '"' or $stringArray[$key] != '' or $stringArray[$key] != null)) {
            return $stringArray[$key];
        } else {
            if (isset($stringArray[$key])) {
                return $stringArray[$key];
            } else {
                if (is_array(json_decode($string, true))) {
                    return "";
                } else {
                    return $string;
                }
            }
        }
    }

    public static function CheckJsonORString($string)
    {
        $key = 0;


        // decode the JSON data
        $result = json_decode($string);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            return $string;
        }else{
            return $result;
        }

    }

    public static function toSqlWithBindingV2($object,$return=false)
    {
        if(isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] == "203.88.147.186" || $_SERVER['REMOTE_ADDR'] == "123.201.21.122"))
        {
            $query = $object->toSql();
            $bindings = $object->getBindings();
            foreach ($bindings as $key => $binding) {
                if (!is_numeric($binding)) {
                    $binding = "'" . $binding . "'";
                }
                $regex = is_numeric($key) ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/" : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";
                $query = preg_replace($regex, $binding, $query, 1);
            }
            if ($return) return $query;
            echo $query;
            exit;
        }
    }
}
