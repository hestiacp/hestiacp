<?php

use League\Flysystem\Config;
use League\Flysystem\ZipArchive\ZipArchiveAdapter as Zip;

class ZipArchiveTests extends PHPUnit_Framework_TestCase
{
    /**
     * @return Zip[]
     */
    public function zipProvider()
    {
        if (! is_dir($folder = __DIR__.'/files')) {
            mkdir($folder, 0777);
        }

        if (is_file($location = __DIR__.'/files/tester.zip')) {
            unlink($location);
        }

        return [
            [new Zip($location, new ZipArchive())],
        ];
    }

    public function testInstance()
    {
        $adapter = new Zip(__DIR__.'/files/tester.zip', new ZipArchive());
        $this->assertInstanceOf('League\Flysystem\AdapterInterface', $adapter);
    }

    /**
     * @dataProvider zipProvider
     */
    public function testGetArchive(Zip $zip)
    {
        $this->assertInstanceOf('ZipArchive', $zip->getArchive());
    }

    /**
     * @dataProvider zipProvider
     */
    public function testZip(Zip $zip)
    {
        $this->assertCount(0, $zip->listContents());
        $this->assertInternalType('array', $zip->write('file.txt', 'contents', new Config()));
        $this->assertCount(1, $zip->listContents());
        $this->assertInternalType('array', $zip->write('nested/file.txt', 'contents', new Config()));
        $this->assertCount(3, $zip->listContents());
        $zip->setPathPrefix('nested/');
        $this->assertCount(1, $zip->listContents());
        $zip->setPathPrefix('');
        $result = $zip->read('nested/file.txt');
        $this->assertEquals('contents', $result['contents']);
        $zip->update('nested/file.txt', 'new contents', new Config());
        $result = $zip->read('nested/file.txt');
        $this->assertEquals('new contents', $result['contents']);
        $result = $zip->readStream('nested/file.txt');
        $this->assertArrayHasKey('stream', $result);
        $result = $zip->getSize('nested/file.txt');
        $this->assertEquals(12, $result['size']);
        $result = $zip->getTimestamp('nested/file.txt');
        $this->assertInternalType('integer', $result['timestamp']);
        $result = $zip->getMimetype('nested/file.txt');
        $this->assertEquals('text/plain', $result['mimetype']);
        $zip->deleteDir('nested');
        $this->assertCount(1, $zip->listContents());
        $zip->rename('file.txt', 'renamed.txt');
        $this->assertFalse($zip->has('file.txt'));

        $zip->createDir('empty_dir', new Config());
        $this->assertInternalType('array', $zip->getMetadata('empty_dir'));

        $stream = tmpfile();
        fwrite($stream, 'something');
        rewind($stream);
        $zip->writeStream('streamed.txt', $stream, new Config());
        fclose($stream);
        $this->assertInternalType('array', $zip->has('streamed.txt'));

        $stream = tmpfile();
        fwrite($stream, 'something');
        rewind($stream);
        $zip->updateStream('streamed-other.txt', $stream, new Config());
        fclose($stream);
        $this->assertInternalType('array', $zip->has('streamed-other.txt'));
    }

    /**
     * @dataProvider zipProvider
     * @expectedException  LogicException
     */
    public function testWriteStreamFail(Zip $zip)
    {
        $zip->writeStream('file.txt', tmpfile(), new Config(['visibility' => 'private']));
    }

    /**
     * @expectedException LogicException
     * @dataProvider zipProvider
     */
    public function testGetVisibility($zip)
    {
        $zip->getVisibility('path');
    }

    /**
     * @expectedException LogicException
     * @dataProvider zipProvider
     */
    public function testSetVisibility($zip)
    {
        $zip->setVisibility('path', 'public');
    }

    /**
     * @expectedException LogicException
     * @dataProvider zipProvider
     */
    public function testSetVisibilityWrite($zip)
    {
        $zip->write('path', 'contents', new Config(['visibility' => 'private']));
    }

    /**
     * @expectedException  LogicException
     */
    public function testZipOpenFails()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('This test results in a fatal error on HHVM');
        }

        $mock = Mockery::mock('ZipArchive');
        $mock->shouldReceive('open')->andReturn(false);
        $zip = new Zip('location', $mock);
    }

    public function testZipReadWriteFails()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM does not support mocking of ZipArchive');
        }

        $mock = Mockery::mock('ZipArchive');
        $mock->shouldReceive('open')->andReturn(true);
        $mock->shouldReceive('close')->andReturn(true);
        $mock->shouldReceive('addFromString')->andReturn(false);
        $mock->shouldReceive('getFromName')->andReturn(false);
        $mock->shouldReceive('getStream')->andReturn(false);
        $zip = new Zip('location', $mock);

        $this->assertFalse($zip->write('file', 'contents', new Config()));
        $this->assertFalse($zip->read('file'));
        $this->assertFalse($zip->getMimetype('file'));
        $this->assertFalse($zip->readStream('file'));
    }

    public function testCopy()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('This test results in a fatal error on HHVM');
        }

        $resource = fopen(__DIR__.'/../readme.md', 'r+');
        $mock = Mockery::mock('ZipArchive');
        $mock->shouldReceive('open')->andReturn(true);
        $mock->shouldReceive('close')->andReturn(true);
        $mock->shouldReceive('addFromString')->andReturn(true);
        $mock->shouldReceive('getStream')->andReturn($resource);
        $zip = new Zip('location', $mock);
        $this->assertTrue($zip->copy('old', 'new'));

        // Ensure the resource is closed internally
        $this->assertFalse(is_resource($resource));
    }

    public function testCopyFailed()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('This test results in a fatal error on HHVM');
        }

        $mock = Mockery::mock('ZipArchive');
        $mock->shouldReceive('open')->andReturn(true);
        $mock->shouldReceive('close')->andReturn(true);
        $mock->shouldReceive('getStream')->andReturn(false);
        $zip = new Zip('location', $mock);
        $this->assertFalse($zip->copy('old', 'new'));
    }
}
