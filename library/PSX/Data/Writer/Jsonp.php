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

namespace PSX\Data\Writer;

use PSX\Data\RecordInterface;
use PSX\Data\WriterInterface;

/**
 * Jsonp
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Jsonp extends Json
{
	public static $mime = 'application/javascript';

	protected $callbackName;

	public function write(RecordInterface $record)
	{
		$callbackName = $this->getCallbackName();

		if(!empty($callbackName))
		{
			return $callbackName . '(' . parent::write($record) . ')';
		}
		else
		{
			return parent::write($record);
		}
	}

	public function isContentTypeSupported($contentType)
	{
		return stripos($contentType, self::$mime) !== false;
	}

	public function getContentType()
	{
		return self::$mime;
	}

	public function getCallbackName()
	{
		if($this->callbackName === null)
		{
			$callbackName = isset($_GET['callback']) ? $_GET['callback'] : null;

			if(!empty($callbackName))
			{
				$this->setCallbackName($callbackName);
			}
		}

		return $this->callbackName;
	}

	public function setCallbackName($callbackName)
	{
		if(preg_match('/^([A-Za-z0-9._]{3,32})$/', $callbackName))
		{
			$this->callbackName = $callbackName;
		}
	}
}
