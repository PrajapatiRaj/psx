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

namespace PSX;

use DateInterval;
use PSX\Cache\Handler\File;
use PSX\Cache\Item;
use PSX\Cache\HandlerInterface;

/**
 * Provides a general caching mechanism. This class abstracts how cached items
 * are saved (i.e. file, sql, ...) and handels expire times. Here an example how
 * you can use the cache class. As [key] you must provide a unique key.
 * <code>
 * $cache = new Cache('[key]');
 *
 * if(($content = $cache->load()) === false)
 * {
 * 	// here some complex stuff so that it is worth to cache the content
 * 	$content = 'test';
 *
 * 	$cache->write($content);
 * }
 *
 * echo $content;
 * </code>
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Cache
{
	/**
	 * The cache key
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * The expire time of the cache in seconds
	 *
	 * @var integer
	 */
	protected $expire;

	/**
	 * Whether to write the cache or not
	 *
	 * @var boolean
	 */
	protected $enabled;

	/**
	 * The handler for the class
	 *
	 * @var PSX\Cache\HandlerInterface
	 */
	protected $handler;

	/**
	 * The cache item
	 *
	 * @var PSX\Cache\Item
	 */
	protected $item;

	/**
	 * To create an cache object we need an $key wich identifies the cache. The
	 * handler is an object of type PSX\Cache\HandlerInterface wich is used to
	 * load and write the cache. Optional you can set the $expire time how long
	 * the cache remains if not set the default config cache expire time is
	 * used.
	 *
	 * @param string $key
	 * @param integer|DateInterval $expire
	 * @param PSX\Cache\HandlerInterface $handler
	 */
	public function __construct($key, $expire = 0, HandlerInterface $handler = null)
	{
		if(!is_numeric($expire))
		{
			$interval = $expire instanceof DateInterval ? $expire : new DateInterval($expire);
			$now      = new DateTime();
			$tstamp   = $now->getTimestamp();

			$now->add($interval);

			$this->expire = $now->getTimestamp() - $tstamp;
		}
		else
		{
			$this->expire = $expire;
		}

		$this->key     = md5($key);
		$this->handler = $handler !== null ? $handler : new File();
		$this->enabled = true;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getExpire()
	{
		return $this->expire;
	}

	public function isEnabled()
	{
		return $this->enabled;
	}

	public function setEnabled($enabled)
	{
		$this->enabled = (boolean) $enabled;
	}

	/**
	 * If caching is enabled and the item exists and is not expired we load the
	 * cache from the handler if not we return false
	 *
	 * @return false|string
	 */
	public function load()
	{
		if($this->enabled && $this->exists() && !$this->isExpired())
		{
			return $this->get()->getContent();
		}

		return false;
	}

	/**
	 * Returns whether the cache item is expired. Note if the underlying cache
	 * item does not exist it is also expired
	 *
	 * @return boolean
	 */
	public function isExpired()
	{
		if($this->exists())
		{
			if($this->expire > 0 && $this->get()->getTime() !== null && (time() - $this->get()->getTime()) > $this->expire)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns whether an cache exists
	 *
	 * @return boolean
	 */
	public function exists()
	{
		return $this->get() instanceof Item;
	}

	/**
	 * Returns the cache item or null if not available. The cache item contains
	 * the cache content and time and is independent from the underlying cache
	 * handler
	 *
	 * @return PSX\Cache\Item
	 */
	public function get()
	{
		if($this->item === null)
		{
			$item = $this->handler->load($this->key);

			if($item instanceof Item)
			{
				$this->item = $item;
			}
		}

		return $this->item;
	}

	/**
	 * Removes the internal cache wich is present if you call the load method so
	 * you can reload the cache item
	 */
	public function reset()
	{
		$this->item = null;
	}

	/**
	 * Write the string $content to the cache by using the handler.
	 *
	 * @param string $content
	 * @return void
	 */
	public function write($content)
	{
		if($this->enabled)
		{
			$this->handler->write($this->key, $content, $this->expire);

			$this->reset();
		}
	}

	/**
	 * Remove the key from the cache using the handler
	 *
	 * @return void
	 */
	public function remove()
	{
		if($this->enabled)
		{
			$this->handler->remove($this->key);

			$this->reset();
		}
	}
}

