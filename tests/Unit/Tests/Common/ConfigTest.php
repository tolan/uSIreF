<?php

namespace uSIref\Unit\Tests\Common;

use uSIref\Unit\Abstracts\TestCase;
use uSIreF\Common\{Config, Exception};

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class ConfigTest extends TestCase {

    /**
     * Simple test for create config and get default value root.
     *
     * @return void
     */
    public function testRootSuccess() {
        $config = new Config();
        $this->assertEquals(dirname(__DIR__, 4), $config->get('root'));
    }

    /**
     * Success test for load config from json.
     *
     * @return void
     */
    public function testLoadJsonSuccess() {
        $config = new Config();
        $result = $config->loadJson(__DIR__.'/mock-config.json');
        $this->assertInstanceOf(Config::class, $result);
    }

    /**
     * Fail test for load config from json.
     *
     * @return void
     */
    public function testLoadJsonFailNonExist() {
        $config = new Config();
        $this->expectException(Exception::class);
        $config->loadJson(__DIR__.'/non-exists-config.json');
    }

    /**
     * Success test for load config from array.
     *
     * @return void
     */
    public function testFromArraySuccess() {
        $config = new Config();
        $this->assertInstanceOf(Config::class, $config->fromArray([]));
        $this->assertInstanceOf(Config::class, $config->fromArray(['name' => 'value']));
    }

    /**
     * Fail test for load config from array.
     *
     * @return void
     */
    public function testFromArrayFailNonArrayType() {
        $config = new Config();
        $this->expectException(\TypeError::class);
        $config->fromArray('string');
    }

    /**
     * Success test for get all data in array.
     *
     * @return void
     */
    public function testToArraySuccess() {
        $config = new Config();
        $result = $config->toArray();
        $this->assertEquals(['root' => dirname(__DIR__, 4)], $result);
    }

    /**
     * Success test for set value.
     *
     * @return void
     */
    public function testSetSuccess() {
        $config = new Config();
        $result = $config->set('name', ['data', 'data2']);
        $this->assertInstanceOf(Config::class, $result);

        $config->set('name', ['data-new']);
    }

    /**
     * Success test for get value.
     *
     * @return void
     */
    public function testGetSuccess() {
        $config = new Config();

        $this->assertEquals(null, $config->get('undefined'));
        $this->assertEquals('value', $config->get('undefined', 'value'));
        $this->assertEquals(dirname(__DIR__, 4), $config->get('root'));
        $this->assertEquals(dirname(__DIR__, 4), $config->get('root', 'something else'));
    }

    /**
     * Success test for check existance of value.
     *
     * @return void
     */
    public function testHasSuccess() {
        $config = new Config();

        $this->assertTrue($config->has('root'));
        $this->assertFalse($config->has('undefined'));
    }

    /**
     * Success test for set and fet value.
     *
     * @return void
     */
    public function testSetAndGetSuccess() {
        $config = new Config();

        $this->assertEquals(null, $config->get('name'));

        $config->set('name', 'value');
        $this->assertEquals('value', $config->get('name'));

        $config->set('name', ['data', 'data2']);
        $this->assertEquals(['data', 'data2'], $config->get('name'));

        $config->set('name', ['data2', ['data3']]);
        $this->assertEquals([0 => 'data', 1 => 'data2', 3 => ['data3']], $config->get('name'));
    }

    /**
     * Success test for load config from json and get data.
     *
     * @return void
     */
    public function testLoadJsonAndGetSuccess() {
        $config = new Config();
        $config->loadJson(__DIR__.'/mock-config.json');
        $this->assertEquals('Test', $config->get('name'));
    }

    /**
     * Success test for load config from array and get data.
     *
     * @return void
     */
    public function testFromArrayAndGetSuccess() {
        $config = new Config();
        $this->assertInstanceOf(Config::class, $config->fromArray(['name' => 'value']));
        $this->assertEquals('value', $config->get('name'));
    }
}
