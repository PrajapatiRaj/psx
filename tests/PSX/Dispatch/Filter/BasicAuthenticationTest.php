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

namespace PSX\Dispatch\Filter;

use Closure;
use PSX\Dispatch\Filter\Exception\FailureException;
use PSX\Dispatch\Filter\Exception\MissingException;
use PSX\Dispatch\Filter\Exception\SuccessException;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Url;

/**
 * BasicAuthenticationTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class BasicAuthenticationTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \PSX\Dispatch\Filter\Exception\SuccessException
	 */
	public function testSuccessful()
	{
		$handle = new BasicAuthentication(function($username, $password){
			return $username == 'test' && $password == 'test';
		});

		$handle->onSuccess(function(){
			throw new SuccessException();
		});

		$username = 'test';
		$password = 'test';

		$request  = new Request(new Url('http://localhost'), 'GET', array('Authorization' => 'Basic ' . base64_encode($username . ':' . $password)));
		$response = new Response();

		$handle->handle($request, $response);
	}

	/**
	 * @expectedException \PSX\Dispatch\Filter\Exception\FailureException
	 */
	public function testFailure()
	{
		$handle = new BasicAuthentication(function($username, $password){
			return $username == 'test' && $password == 'test';
		});

		$handle->onFailure(function(){
			throw new FailureException();
		});

		$username = 'foo';
		$password = 'bar';

		$request  = new Request(new Url('http://localhost'), 'GET', array('Authorization' => 'Basic ' . base64_encode($username . ':' . $password)));
		$response = new Response();

		$handle->handle($request, $response);
	}

	public function testMissing()
	{
		$handle = new BasicAuthentication(function($username, $password){
			return $username == 'test' && $password == 'test';
		});

		try
		{
			$request  = new Request(new Url('http://localhost'), 'GET');
			$response = new Response();

			$handle->handle($request, $response);

			$this->fail('Must throw an Exception');
		}
		catch(\Exception $e)
		{
			$this->assertEquals(401, $response->getStatusCode());
			$this->assertEquals('Basic realm="psx"', (string) $response->getHeader('WWW-Authenticate'));
		}
	}

	/**
	 * @expectedException \PSX\Dispatch\Filter\Exception\MissingException
	 */
	public function testMissingWrongType()
	{
		$handle = new BasicAuthentication(function($username, $password){
			return $username == 'test' && $password == 'test';
		});

		$handle->onMissing(function(){
			throw new MissingException();
		});

		$request  = new Request(new Url('http://localhost'), 'GET', array('Authorization' => 'Foo'));
		$response = new Response();

		$handle->handle($request, $response);
	}
}
