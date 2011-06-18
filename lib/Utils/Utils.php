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

		if(is_object($a) and $a instanceof jqGridRawData)
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
			throw new jqGridException('Alphanum check failed on: ' . $val);
		}

		return $val;
	}

	public static function implodeColValue(array $hash)
	{
		$base = array();

		foreach($hash as $k => $v)
		{
			$base[] = $k . ':' . $v;
		}

		return implode(';', $base);
	}

	public static function getFunctionName($prefix, $name)
	{
		//underscores to camel
		$parts = explode('_', $name);
		$parts = array_map('ucfirst', $parts);

		return self::checkAlphanum($prefix . implode('', $parts));
	}
}