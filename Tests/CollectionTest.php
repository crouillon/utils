<?php
/*
 * Copyright (c) 2011-2014 Lp digital system
 *
 * This file is part of BackBee.
 *
 * BackBee is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * BackBee is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with BackBee. If not, see <http://www.gnu.org/licenses/>.
 */
namespace BackBee\Utils\Tests;

use BackBee\Utils\Collection\Collection;

/**
 * @author      c.rouillon <rouillon.charles@gmail.com>
 * @author      Mickaël Andrieu <mickael.andrieu@lp-digital.fr>
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $_mock;

    /**
     * Sets up the fixture
     */
    public function setUp()
    {
        $this->mock = [
            'key' => [
                'subkey' => [
                    'subsubkey' => 'value',
                ],
            ],
        ];
    }

    public function testHas()
    {
        $this->assertTrue(Collection::has($this->mock, 'key:subkey:subsubkey'));
        $this->assertFalse(Collection::has($this->mock, 'key:subkey:unknown'));
        $this->assertFalse(Collection::has($this->mock, 'key:subkey:subsubkey:unknown'));
        $this->assertTrue(Collection::has($this->mock, 'key::subkey::subsubkey', '::'));
        $this->assertFalse(Collection::has($this->mock, 'key:subkey:subsubkey', '::'));
    }

    /**
     * @expectedException \Exception
     */
    public function testHasWithInvalidKey()
    {
        $this->assertTrue(Collection::has($this->mock, new \stdClass()));
    }

    /**
     * @expectedException \Exception
     */
    public function testHasWithInvalidSeparator()
    {
        $this->assertTrue(Collection::has($this->mock, 'key', new \stdClass()));
    }

    public function testGet()
    {
        $this->assertEquals('value', Collection::get($this->mock, 'key:subkey:subsubkey'));
        $this->assertNull(Collection::get($this->mock, 'key:subkey:unknown'));
        $this->assertNull(Collection::get($this->mock, 'key:subkey:subsubkey:unknown'));
        $this->assertEquals('default', Collection::get($this->mock, 'key:subkey:subsubkey:unknown', 'default'));
        $this->assertEquals('value', Collection::get($this->mock, 'key::subkey::subsubkey', null, '::'));
        $this->assertNull(Collection::get($this->mock, 'key:subkey:subsubkey', null, '::'));

        $result = [
            'subkey' => [
                'subsubkey' => 'value',
            ],
        ];
        $this->assertEquals($result, Collection::get($this->mock, 'key'));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetWithInvalidKey()
    {
        $this->assertTrue(Collection::get($this->mock, new \stdClass()));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetWithInvalidSeparator()
    {
        $this->assertTrue(Collection::get($this->mock, 'key', null, new \stdClass()));
    }

    public function testArray_column()
    {
        $mock = [
            'unused',
            [
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ],
            [
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ],
            [
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            ],
        ];

        $this->assertEquals([
            [
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            [
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ],
            [
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ],
            [
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            ],
        ], Collection::array_column($mock));
        $this->assertEquals(['John', 'Sally', 'Jane', 'Peter'], Collection::array_column($mock, 'first_name'));
        $this->assertEquals([2135 => 'John', 3245 => 'Sally', 5342 => 'Jane', 5623 => 'Peter'], Collection::array_column($mock, 'first_name', 'id'));
        $this->assertEquals([
            2135 => [
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            3245 => [
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ],
            5342 => [
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ],
            5623 => [
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            ],
        ], Collection::array_column($mock, null, 'id'));
    }

    public function testToCsv()
    {
        $users = [0 => ['name' => 'Charles', 'role' => 'lead developper'],
            1 => ['name' => 'Eric', 'role' => 'developper'],
        ];

        $this->assertSame("Charles;lead developper\nEric;developper\n", Collection::toCsv($users));
    }

    /**
     * @expectedException \BackBee\Utils\Exception\InvalidArgumentException
     */
    public function testToCsvWithBadInput()
    {
        $users = [0 => ['name' => 'Charles', 'role' => 'lead developper'],
            1 => ['name' => 'Eric', 'role' => 'developper'],
        ];
        $separator = '//';

        Collection::toCsv($separator, $users);
    }

    public function testToBasicXml()
    {
        $users = ['users' => [
            0 => ['name' => 'Charles', 'role' => 'lead developper'],
            1 => ['name' => 'Eric', 'role' => 'developper'],
            ],
        ];

        $this->assertSame('<users><0><name>Charles</name><role>lead developper</role></0><1><name>Eric</name><role>developper</role></1></users>', Collection::toBasicXml($users));
    }

    public function testToXml()
    {
        $users = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper', 'drink' => 'milk & chocolate'],
            ],
        ];

        $xmlReturn = '<users><1><name>Charles</name><role>lead developper</role></1><2><name>Eric</name><role>developper</role><drink>milk &amp; chocolate</drink></2></users>';
        $this->assertSame($xmlReturn, Collection::toXml($users));
    }

    public function testArrayDiffAssocRecursive()
    {
        $users = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            ],
        ];

        $users2 = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $diff1 = [ ];
        $diff2 = ['users' => [
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $this->assertSame(Collection::array_diff_assoc_recursive($users, $users2), $diff1);
        $this->assertSame(Collection::array_diff_assoc_recursive($users2, $users), $diff2);
    }

    public function testArrayMergeAssocRecursive()
    {
        $users = [
            'users' => [
                1 => ['name' => 'Charles', 'role' => 'lead developper'],
                2 => ['name' => 'Eric', 'role' => 'developper'],
                3 => 'Florian',
                4 => 'Alex',

            ],
            'foo' => 'bar',
            'bar' => ['foo'],
            'back' => 'bee',
            'frameworks' => [
                1 => [1 => 'Rails', 2 => 'ruby'],
                2 => [1 => 'Symfony', 2 => 'php'],
            ],
        ];
        $users2 = [
            0 => 'Djoudi',
            'users' => [
                1 => 'Mickael',
                2 => ['name' => 'Harris', 'role' => 'developper'],
                3 => ['name' => 'Nicolas', 'role' => 'developper'],
                4 => 'Florian',
            ],
            'foo' => 'foo',
            'bar' => 'bar',
            'back' => ['bee'],
            'frameworks' => [
                1 => [1 => 'Django', 2 => 'python'],
                2 => [1 => 'Symfony', 2 => 'php'],
            ],
        ];
        $mergedUsers = [
            'users' => [
                1 => ['name' => 'Charles', 'role' => 'lead developper'],
                2 => ['name' => 'Eric', 'role' => 'developper'],
                3 => 'Florian',
                4 => 'Alex',
                5 => 'Mickael',
                6 => ['name' => 'Harris', 'role' => 'developper'],
                7 => ['name' => 'Nicolas', 'role' => 'developper'],
                
            ],
            'foo' => 'foo',
            'bar' => 'bar',
            'back' => ['bee'],
            'frameworks' => [
                1 => [1 => 'Rails', 2 => 'ruby'],
                2 => [1 => 'Symfony', 2 => 'php'],
                3 => [1 => 'Django', 2 => 'python'],
            ],
            0 => 'Djoudi',
        ];

        $this->assertSame(Collection::array_merge_assoc_recursive($users, $users2), $mergedUsers);
    }

    public function testArrayRemoveAssocRecursive()
    {
        $allUsers = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $nicolas = ['users' => [
            3 => ['name' => 'Nicolas', 'role' => 'developper'],
            ],
        ];

        $unknown = ['users' => [
            4 => ['name' => 'Unknown', 'role' => 'tester'],
            ],
        ];

        $expectedResult = ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            ],
        ];

        Collection::array_remove_assoc_recursive($allUsers, $nicolas);
        $this->assertSame($allUsers, $expectedResult);

        Collection::array_remove_assoc_recursive($expectedResult, $unknown);
        $this->assertSame($expectedResult, ['users' => [
            1 => ['name' => 'Charles', 'role' => 'lead developper'],
            2 => ['name' => 'Eric', 'role' => 'developper'],
            ],
        ]);
    }

    public function tearDown()
    {
        $this->mock = null;
    }
}
