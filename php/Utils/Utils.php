<?php

class jqGrid_Utils 
{
	/**
	 * Supports any encoding
	 * Not only utf-8, as official json_encode does
	 *
	 * Based on original 'php2js' function of Dmitry Koterov
	 *
	 */
	public static function jsonEncode( $a = false, $newlines = false )
	{
		static $jsonReplaces = array(
			array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'),
			array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"')
		);

		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';

		if (is_scalar($a))
		{
			if (is_float($a))
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

		for ($i = 0, reset($a); $i < count($a); $i++, next($a))
		{
			if (key($a) !== $i)
			{
				$isList = false;
				break;
			}
		}
		
		$result = array();

		if ($isList)
		{
			foreach ($a as $v)
			{
				$result[] = self::jsonEncode($v);
			}

			return '[ ' . implode(', ', $result) . ' ]';
		}
		else
		{
			foreach ($a as $k => $v)
			{
				$result[] = self::jsonEncode($k).': '.self::jsonEncode($v);
			}
			
			return '{ ' . implode(', ', $result) . ' }';
		}
	}

	public static function checkAlphanum($val)
	{
		static $mask = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_';

		if( $val and strspn($val, $mask) != strlen($val) )
		{
			throw new jqGrid_Exception('Alphanum check failed on value: ' . $val);
		}

		return $val;
	}

	/**
	 * Convert 'under_score' to 'underScore'
	 */
	public static function score2camel($prefix, $name)
	{
		//underscores to camel
		$parts = explode('_', $name);
		$parts = array_map('ucfirst', $parts);

		return self::checkAlphanum($prefix . implode('', $parts));
	}

	/**
	 * Callback for array_walk
	 * PHP 5.2 does not support closures, so..
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