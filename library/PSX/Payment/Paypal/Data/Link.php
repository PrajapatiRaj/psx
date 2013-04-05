<?php
/*
 *  $Id: Message.php 488 2012-05-28 12:44:38Z k42b3.x@googlemail.com $
 *
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2010-2012 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace PSX\Payment\Paypal\Data;

use PSX\Data\RecordAbstract;
use PSX\Url;

/**
 * Link
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://phpsx.org
 * @category   PSX
 * @package    PSX_Payment
 * @version    $Revision: 488 $
 */
class Link extends RecordAbstract
{
	protected $href;
	protected $rel;
	protected $method;

	public function getName()
	{
		return 'link';
	}

	public function getFields()
	{
		return array(
			'href'   => $this->href,
			'rel'    => $this->rel,
			'method' => $this->method,
		);
	}

	public function setHref($href)
	{
		$this->href = $href;
	}

	public function getHref()
	{
		return $this->href;
	}

	public function setRel($rel)
	{
		$this->rel = $rel;
	}

	public function getRel()
	{
		return $this->rel;
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function getMethod()
	{
		return $this->method;
	}
}
