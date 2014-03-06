<?php
/*
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2010-2014 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of psx. psx is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * psx is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with psx. If not, see <http://www.gnu.org/licenses/>.
 */

namespace PSX\Http;

use DateTime;

/**
 * Util class to handle Authentication header
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Authentication
{
	public static function decodeParameters($data)
	{
		$params = array();
		$parts  = explode(',', $data);

		foreach($parts as $value)
		{
			$value = trim($value);
			$pair  = explode('=', $value);

			$key   = isset($pair[0]) ? $pair[0] : null;
			$value = isset($pair[1]) ? $pair[1] : null;

			if(!empty($key))
			{
				$key   = strtolower($key);
				$value = trim($value, '"');

				$params[$key] = $value;
			}
		}

		return $params;
	}

	public static function encodeParameters(array $params)
	{
		$parts = array();

		foreach($params as $key => $value)
		{
			$parts[] = $key . '="' . $value . '"';
		}

		return implode(', ', $parts);
	}
}
