<?php

class jqGrid_Utils
{
    /**
     * Supports any encoding
     * Not only utf-8, as official json_encode does
     *
     * Based on original 'php2js' function of Dmitry Koterov
     *
     * @static
     * @param  mixed $a
     * @return string
     */
    public static function jsonEncode($a = false)
    {
        static $jsonReplaces = array(
            array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
            array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"')
        );

        if(is_null($a)) return 'null';
        if($a === false) return 'false';
        if($a === true) return 'true';

        if(is_scalar($a))
        {
            if(is_float($a))
            {
                $a = str_replace(",", ".", strval($a));
            }

            return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
        }

        #Special internal data structure - no escaping
        if(is_object($a) and $a instanceof jqGrid_Data)
        {
            return strval($a);
        }

        $isList = true;

        for($i = 0, reset($a); $i < count($a); $i++, next($a))
        {
            if(key($a) !== $i)
            {
                $isList = false;
                break;
            }
        }

        $result = array();

        if($isList)
        {
            foreach($a as $v)
            {
                $result[] = self::jsonEncode($v);
            }

            return '[ ' . implode(', ', $result) . ' ]';
        }
        else
        {
            foreach($a as $k => $v)
            {
                $result[] = self::jsonEncode($k) . ': ' . self::jsonEncode($v);
            }

            return '{ ' . implode(', ', $result) . ' }';
        }
    }

    /**
     * Check input string to contain only english letters, numbers and unserscore
     * The list of allowed characters might be extended
     *
     * @static
     * @throws jqGrid_Exception
     * @param $val - input string
     * @param string $additional - additional allowed characters
     * @return string
     */
    public static function checkAlphanum($val, $additional = '')
    {
        static $mask = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_';

        if($val and strspn($val, $mask . $additional) != strlen($val))
        {
            throw new jqGrid_Exception('Alphanum check failed on value: ' . $val);
        }

        return $val;
    }

    /**
     * Convert undescore to camel-case
     * Used to build function names
     *
     * @static
     * @param $prefix
     * @param $name
     * @return string
     */
    public static function uscore2camel($prefix, $name)
    {
        //underscores to camel
        $parts = explode('_', $name);
        $parts = array_map('ucfirst', $parts);

        return self::checkAlphanum($prefix . implode('', $parts));
    }

    /**
     * Callback for array_walk
     * PHP 5.2 does not support closures, so..
     *
     * @static
     * @param $arr
     * @param $enc_from
     * @param $enc_to
     * @return array
     */
    public static function arrayIconv($arr, $enc_from, $enc_to)
    {
        foreach($arr as $k => &$v)
        {
            $v = iconv($enc_from, $enc_to, $v);
        }

        return $arr;
    }
}