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

namespace PSX\Data\Transformer;

use PSX\Rss;
use PSX\Rss\Item;
use PSX\Http\Message;

/**
 * XmlArrayTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class XmlArrayTest extends \PHPUnit_Framework_TestCase
{
	public function testTransform()
	{
		$body = <<<INPUT
<test>
	<empty />
	<empty_2></empty_2>
	<foo>bar</foo>
	<bar>blub</bar>
	<bar>bla</bar>
	<test>
		<foo>bar</foo>
	</test>
	<foooo>
		<test>
			<title>blub</title>
		</test>
		<bar>
			<title>foo</title>
		</bar>
	</foooo>
	<item>
		<title>foo</title>
		<text>bar</text>
	</item>
	<item>
		<title>foo</title>
		<text>bar</text>
	</item>
	<item>
		<title>foo</title>
		<text>bar</text>
	</item>
	<item>
		<title>foo</title>
		<text>bar</text>
	</item>
</test>
INPUT;

		$dom = new \DOMDocument();
		$dom->loadXML($body);

		$transformer = new XmlArray();

		$expect = array(
			'empty' => '', 
			'empty_2' => '', 
			'foo' => 'bar', 
			'bar' => array('blub', 'bla'), 
			'test' => array('foo' => 'bar'),
			'foooo' => array('test' => array('title' => 'blub'), 'bar' => array('title' => 'foo')),
			'item' => array(
				array('title' => 'foo', 'text' => 'bar'), 
				array('title' => 'foo', 'text' => 'bar'), 
				array('title' => 'foo', 'text' => 'bar'), 
				array('title' => 'foo', 'text' => 'bar')
			),
		);

		$data = $transformer->transform($dom);

		$this->assertTrue(is_array($data));
		$this->assertEquals($expect, $data);
	}
}