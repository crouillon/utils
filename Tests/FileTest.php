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

use BackBee\Utils\File\File;

class FileTest extends UtilsTestCase
{
    private $folderPath;
    private $privatePath;
    private $zipPath;

    public function setUp()
    {
        $this->folderPath = $this->getFixturesFolder().'foo';
        $this->privatePath = $this->getFixturesFolder().'bad-rights';
        @chmod($this->privatePath, 0000);
        $this->zipPath = $this->getFixturesFolder().'archive';
    }

    public function testRealpath()
    {
        $this->assertEquals(__DIR__.DIRECTORY_SEPARATOR.'FileTest.php', File::realpath(__DIR__.DIRECTORY_SEPARATOR."FileTest.php"));
        $this->assertFalse(File::realpath(DIRECTORY_SEPARATOR."FileTest.php"));

        $this->assertEquals($this->folderPath, File::realpath($this->folderPath));
    }

    public function testRealPathMalformedUrl()
    {
        $this->assertFalse(File::realpath('http://www..com/'));
    }

    /**
     * @link http://php.net/manual/en/function.parse-url.php
     */
    public function testRealPathWithoutScheme()
    {
        $this->assertFalse(File::realpath('//www.example.com/path?googleguy=googley'));
    }

    public function testNormalizePath()
    {
        $this->assertEquals('/fake/file/path', File::normalizePath('/fake\file/path\\//', '/'));
        $this->assertEquals('/fake/file/path/', File::normalizePath('/fake\file/path\\//', '/', false));
        $this->assertEquals('C:\fake\file\path', File::normalizePath('C:\fake\file/path\\//', '\\'));
        $this->assertEquals('C:\fake\file\path\\', File::normalizePath('C:/fake\file/path\\//', '\\', false));
        $this->assertEquals('http://host/fake/path', File::normalizePath('http://host/fake\path\\'));
        $this->assertEquals('http://host:80/fake/path', File::normalizePath('http://host:80/fake\path\\'));
        $this->assertEquals('http://user@host:80/fake/path', File::normalizePath('http://user@host:80/fake\path\\'));
        $this->assertEquals('http://user:pass@host:80/fake/path', File::normalizePath('http://user:pass@host:80/fake\path\\'));
        $this->assertEquals(realpath($this->folderPath), File::normalizePath($this->folderPath));
    }

    public function testReadableFilesize()
    {
        $this->assertEquals('1.953 kB', File::readableFilesize(2000, 3));
        $this->assertEquals('553.71094 kB', File::readableFilesize(567000, 5));
        $this->assertEquals('553.71 kB', File::readableFilesize(567000));
        $this->assertEquals('5.28 GB', File::readableFilesize(5670008902));
        $this->assertEquals('0.00 B', File::readableFilesize(0));
    }

    public function testGetExtension()
    {
        $this->assertEquals('.txt', File::getExtension('test.txt', true));
        $this->assertEquals('jpg', File::getExtension('test.jpg', false));
        $this->assertEquals('', File::getExtension('test', false));
        $this->assertEquals('', File::getExtension('test', true));
        $this->assertEquals('', File::getExtension('', true));
    }

    public function testRemoveExtension()
    {
        $this->assertEquals('test', File::removeExtension('test.txt'));
        $this->assertEquals('', File::removeExtension('.txt'));
        $this->assertEquals('', File::removeExtension(''));
        $this->assertEquals('test', File::removeExtension('test'));
    }

    public function testExistingDirMkdir()
    {
        $this->assertTrue(File::mkdir($this->folderPath));
    }

    /**
     * @expectedException \BackBee\Utils\Exception\InvalidArgumentException
     */
    public function testExistingDirMkdirWithBadRights()
    {
        if (is_writable($this->privatePath)) {
            $this->markTestSkipped('Unsupported feature on '.PHP_OS);
        }

        File::mkdir($this->privatePath);
    }

    /**
     * @expectedException \BackBee\Utils\Exception\InvalidArgumentException
     */
    public function testUnknownDirMkdir()
    {
        File::mkdir('');
        File::mkdir(null);
    }

    /**
     * @expectedException \BackBee\Utils\Exception\InvalidArgumentException
     */
    public function testUnreadableCopy()
    {
        File::copy($this->privatePath, 'bar.txt');
    }

    /**
     * @expectedException \BackBee\Utils\Exception\InvalidArgumentException
     */
    public function testUnreadableGetFilesRecursivelyByExtension()
    {
        File::getFilesRecursivelyByExtension($this->privatePath, '.txt');
        File::getFilesRecursivelyByExtension('', '');
    }

    public function testGetFilesRecursivelyByExtension()
    {
        $this->assertEquals(
            [
                $this->folderPath.DIRECTORY_SEPARATOR.'bar.txt',
                $this->folderPath.DIRECTORY_SEPARATOR.'foo.txt',
            ], File::getFilesRecursivelyByExtension($this->folderPath, 'txt')
        );

        $this->assertEquals([$this->folderPath.DIRECTORY_SEPARATOR.'baz.php'], File::getFilesRecursivelyByExtension($this->folderPath, 'php'));
        $this->assertEquals([$this->folderPath.DIRECTORY_SEPARATOR.'backbee.yml'], File::getFilesRecursivelyByExtension($this->folderPath, 'yml'));
        $this->assertEquals([$this->folderPath.DIRECTORY_SEPARATOR.'noextension'], File::getFilesRecursivelyByExtension($this->folderPath, ''));
        $this->assertEquals([], File::getFilesRecursivelyByExtension($this->folderPath, 'aaa'));
    }

    /**
     * @expectedException \BackBee\Utils\Exception\InvalidArgumentException
     */
    public function testUnredableGetFilesByExtension()
    {
        File::getFilesByExtension($this->privatePath, '.txt');
        File::getFilesByExtension('', '');
    }

    public function testGetFilesByExtension()
    {
        $this->assertEquals(
            [
                $this->folderPath.DIRECTORY_SEPARATOR.'bar.txt',
                $this->folderPath.DIRECTORY_SEPARATOR.'foo.txt',
            ], File::getFilesByExtension($this->folderPath, 'txt')
        );

        $this->assertEquals([$this->folderPath.DIRECTORY_SEPARATOR.'baz.php'], File::getFilesByExtension($this->folderPath, 'php'));
        $this->assertEquals([$this->folderPath.DIRECTORY_SEPARATOR.'backbee.yml'], File::getFilesByExtension($this->folderPath, 'yml'));
        $this->assertEquals([$this->folderPath.DIRECTORY_SEPARATOR.'noextension'], File::getFilesByExtension($this->folderPath, ''));
        $this->assertEquals([], File::getFilesByExtension($this->folderPath, 'aaa'));
    }

    /**
     * @expectedException \BackBee\Utils\Exception\ApplicationException
     */
    public function testExtractZipArchiveNonexistentDir()
    {
        File::extractZipArchive('test', 'test');
    }

    /**
     * @expectedException \BackBee\Utils\Exception\ApplicationException
     */
    public function testExtractZipArchiveUnreadableDir()
    {
        File::extractZipArchive('test', $this->privatePath);
    }

    /**
     * @expectedException \BackBee\Utils\Exception\ApplicationException
     */
    public function testExtractZipArchiveExistingDir()
    {
        $zipFile = $this->getFixturesFolder().'archive.zip';
        File::extractZipArchive('test', $this->zipPath, true);
    }

    public function testResolveFilepath()
    {
        $twigFilePath = $this->getFixturesFolder().'file.twig';
        File::resolveFilepath($twigFilePath);
        $this->assertEquals($this->getFixturesFolder().'file.twig', $twigFilePath);
    }

    public function tearDown()
    {
        $this->folderPath = null;
        @chmod($this->privatePath, 0755);
        $this->privatePath = null;
        $this->zipPath = null;
    }
}
