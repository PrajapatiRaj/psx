<?php
/*
 *  $Id: TableTest.php 639 2012-09-30 22:43:50Z k42b3.x@googlemail.com $
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

namespace PSX\Sql;

use PSX\Config;
use PSX\Data\Record;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Table\Select;

/**
 * PSX_Sql_TableTest
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://phpsx.org
 * @category   tests
 * @version    $Revision: 639 $
 */
class TableTest extends \PHPUnit_Framework_TestCase
{
	protected $sql;
	protected $table;

	protected function setUp()
	{
		try
		{
			$config = getConfig();

			$this->table = 'TableTestTable';
			$this->sql   = new Sql($config['psx_sql_host'],
				$config['psx_sql_user'],
				$config['psx_sql_pw'],
				$config['psx_sql_db']);

		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->table}` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

			$this->sql->exec($sql);
			$this->sql->exec('TRUNCATE TABLE ' . $this->table);

			$data = array('foo', 'bar', 'test', 'fooooo');

			foreach($data as $d)
			{
				$this->sql->insert($this->table, array(

					'title' => $d,
					'date'  => date(DateTime::SQL),

				));
			}
		}
		catch(\Exception $e)
		{
			$this->markTestSkipped($e->getMessage());
		}
	}

	protected function tearDown()
	{
		if($this->sql instanceof Sql)
		{
			$this->sql->exec('TRUNCATE TABLE ' . $this->table);
		}

		unset($this->table);
		unset($this->sql);
	}

	public function testGetSql()
	{
		$table = new TableTestTable($this->sql);

		$this->assertEquals(true, $table->getSql() instanceof Sql);
	}

	public function testGetDisplayName()
	{
		$table = new TableTestTable($this->sql);

		$this->assertEquals('TableTestTable', $table->getDisplayName());
	}

	public function testGetPrimaryKey()
	{
		$table = new TableTestTable($this->sql);

		$this->assertEquals('id', $table->getPrimaryKey());
	}

	public function testGetFirstColumnWithAttr()
	{
		$table = new TableTestTable($this->sql);

		$this->assertEquals('id', $table->getFirstColumnWithAttr(TableTestTable::PRIMARY_KEY));
		$this->assertEquals('id', $table->getFirstColumnWithAttr(TableTestTable::AUTO_INCREMENT));
	}

	public function testGetFirstColumnWithType()
	{
		$table = new TableTestTable($this->sql);

		$this->assertEquals('id', $table->getFirstColumnWithType(TableTestTable::TYPE_INT));
		$this->assertEquals('title', $table->getFirstColumnWithType(TableTestTable::TYPE_VARCHAR));
		$this->assertEquals('date', $table->getFirstColumnWithType(TableTestTable::TYPE_DATETIME));
	}

	public function testGetValidColumns()
	{
		$table = new TableTestTable($this->sql);

		$r = $table->getValidColumns(array('test', 'date', 'id', 'foo', 'bar', 'title', 'blub'));
		$e = array(1 => 'date', 2 => 'id', 5 => 'title');

		$this->assertEquals($e, $r);
	}

	public function testSelect()
	{
		$table  = new TableTestTable($this->sql);
		$select = $table->select(array('id', 'title'));

		$this->assertEquals(array('id' => 2, 'title' => 'bar'), $select->where('id', '=', 2)->getRow());
	}

	public function testGetLastSelect()
	{
		$table  = new TableTestTable($this->sql);
		$select = $table->select(array('id', 'title'));

		$this->assertEquals(true, $table->getLastSelect() instanceof Select);
	}

	public function testGetRecord()
	{
		// update
		$table  = new TableTestTable($this->sql);
		$record = $table->getRecord(1);

		$this->assertEquals(true, $record instanceof TableTestRecord);
		$this->assertEquals(1, $record->id);
		$this->assertEquals('foo', $record->title);

		// create
		$table  = new TableTestTable($this->sql);
		$record = $table->getRecord();

		$this->assertEquals(true, $record instanceof TableTestRecord);
	}

	public function testGetAll()
	{
		$table = new TableTestTable($this->sql);

		$r = $table->getAll(array('title'), new Condition(array('id', '=', 2)));
		$e = array(array('title' => 'bar'));

		$this->assertEquals($e, $r);


		$r = $table->getAll(array('title'), new Condition(array('title', 'LIKE', 'fo%')), 'id', Sql::SORT_DESC);
		$e = array(array('title' => 'fooooo'), array('title' => 'foo'));

		$this->assertEquals($e, $r);


		$r = $table->getAll(array('title'), new Condition(array('title', 'LIKE', 'fo%')), 'id', Sql::SORT_ASC, 0, 1);
		$e = array(array('title' => 'foo'));

		$this->assertEquals($e, $r);
	}

	public function testGetRow()
	{
		$table = new TableTestTable($this->sql);

		$r = $table->getRow(array('title'), new Condition(array('id', '=', 2)));
		$e = array('title' => 'bar');

		$this->assertEquals($e, $r);
	}

	public function testGetCol()
	{
		$table = new TableTestTable($this->sql);

		$r = $table->getCol('title', new Condition(array('title', 'LIKE', 'fo%')), 'id', Sql::SORT_DESC);
		$e = array('fooooo', 'foo');

		$this->assertEquals($e, $r);
	}

	public function testGetField()
	{
		$table = new TableTestTable($this->sql);

		$r = $table->getField('title', new Condition(array('id', '=', 2)));
		$e = 'bar';

		$this->assertEquals($e, $r);
	}

	public function testCount()
	{
		$table = new TableTestTable($this->sql);

		$this->assertEquals(4, $table->count());
		$this->assertEquals(2, $table->count(new Condition(array('title', 'LIKE', 'fo%'))));
	}

	public function testInsert()
	{
		$value = 'foobar_' . rand(0, 999);
		$table = new TableTestTable($this->sql);

		$table->insert(array(

			'title' => $value,
			'date'  => date(DateTime::SQL)

		));

		$this->assertEquals($value, $table->getField('title', new Condition(array('title', '=', $value))));
	}

	public function testInsertRecord()
	{
		$value  = 'foobar_' . rand(0, 999);
		$table  = new TableTestTable($this->sql);
		$record = new Record('test', array(

			'title' => $value,
			'date'  => date(DateTime::SQL)

		));

		$table->insert($record);

		$this->assertEquals($value, $table->getField('title', new Condition(array('title', '=', $value))));
	}

	public function testUpdate()
	{
		$value = 'foobar_' . rand(0, 999);
		$table = new TableTestTable($this->sql);
		$con   = new Condition(array('id', '=', 3));
		$resp  = $table->getField('title', $con);

		$table->update(array(

			'title' => $value,
			'date'  => date(DateTime::SQL)

		), $con);

		$this->assertEquals(false, $resp === $table->getField('title', $con));
		$this->assertEquals(true, $value === $table->getField('title', $con));
	}

	public function testUpdateWithoutCondition()
	{
		$value = 'foobar_' . rand(0, 999);
		$table = new TableTestTable($this->sql);
		$con   = new Condition(array('id', '=', 3));
		$resp  = $table->getField('title', $con);

		$table->update(array(

			'id'    => 3,
			'title' => $value,
			'date'  => date(DateTime::SQL)

		));

		$this->assertEquals(false, $resp === $table->getField('title', $con));
		$this->assertEquals(true, $value === $table->getField('title', $con));
	}

	public function testUpdateRecord()
	{
		$value  = 'foobar_' . rand(0, 999);
		$table  = new TableTestTable($this->sql);
		$con    = new Condition(array('id', '=', 3));
		$resp   = $table->getField('title', $con);
		$record = new Record('test', array(

			'title' => $value,
			'date'  => date(DateTime::SQL)

		));

		$table->update($record, $con);

		$this->assertEquals(false, $resp === $table->getField('title', $con));
		$this->assertEquals(true, $value === $table->getField('title', $con));
	}

	public function testUpdateRecordWithoutCondition()
	{
		$value  = 'foobar_' . rand(0, 999);
		$table  = new TableTestTable($this->sql);
		$con    = new Condition(array('id', '=', 3));
		$resp   = $table->getField('title', $con);
		$record = new Record('test', array(

			'id'    => 3,
			'title' => $value,
			'date'  => date(DateTime::SQL)

		));

		$table->update($record);

		$this->assertEquals(false, $resp === $table->getField('title', $con));
		$this->assertEquals(true, $value === $table->getField('title', $con));
	}

	public function testReplace()
	{
		$value = 'foobar_' . rand(0, 999);
		$table = new TableTestTable($this->sql);
		$con   = new Condition(array('id', '=', 3));
		$resp  = $table->getField('title', $con);

		$table->replace(array(

			'id'    => 3,
			'title' => $value,
			'date'  => date(DateTime::SQL)

		));

		$this->assertEquals(false, $resp === $table->getField('title', $con));
		$this->assertEquals(true, $value === $table->getField('title', $con));
	}

	public function testDelete()
	{
		$table = new TableTestTable($this->sql);
		$con   = new Condition(array('id', '=', 3));

		$table->delete($con);

		$this->assertEquals(false, $table->getField('title', $con));
	}

	public function testDeleteWithoutCondition()
	{
		$table = new TableTestTable($this->sql);
		$con   = new Condition(array('id', '=', 3));

		$table->delete(array(

			'id' => 3

		));

		$this->assertEquals(false, $table->getField('title', $con));
	}

	public function testDeleteRecord()
	{
		$table  = new TableTestTable($this->sql);
		$con    = new Condition(array('id', '=', 3));
		$record = new Record('test', array(

			'id' => 3,

		));

		$table->delete($record);

		$this->assertEquals(false, $table->getField('title', $con));
	}
}

class TableTestTable extends TableAbstract
{
	public function getConnections()
	{
		return array();
	}

	public function getName()
	{
		return 'TableTestTable';
	}

	public function getColumns()
	{
		return array(

			'id'    => self::TYPE_INT | 10 | self::PRIMARY_KEY | self::AUTO_INCREMENT,
			'title' => self::TYPE_VARCHAR | 32,
			'date'  => self::TYPE_DATETIME,

		);
	}

	public function getDefaultRecordClass()
	{
		return '\PSX\Sql\TableTestRecord';
	}


	public function getDefaultRecordArgs()
	{
		return array();
	}
}

class TableTestRecord
{
	public function __construct()
	{
	}
}

