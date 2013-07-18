<?php

namespace Touki\FTP\Tests\Uploader\FTP;

use Touki\FTP\Tests\ConnectionAwareTestCase;
use Touki\FTP\Uploader\FTP\NbFileUploader;
use Touki\FTP\Model\File;
use Touki\FTP\Model\Directory;
use Touki\FTP\FTP;

/**
 * Non Blocking File uploader test
 *
 * @author Touki <g.vincendon@vithemis.com>
 */
class NbFileUploaderTest extends ConnectionAwareTestCase
{
    public function setUp()
    {
        parent::setUp();

        $self             = $this;
        $this->called     = false;
        $this->wrapper    = self::$wrapper;
        $this->uploader   = new NbFileUploader($this->wrapper);
        $this->local      = __FILE__;
        $this->remote     = new File(basename(__FILE__));
        $this->options    = array(
            FTP::NON_BLOCKING => true,
            FTP::NON_BLOCKING_CALLBACK => function() use ($self) {
                $self->called = true;
            }
        );
    }

    public function testVote()
    {
        $this->assertTrue($this->uploader->vote($this->remote, $this->local, $this->options));
    }

    public function testUpload()
    {
        $this->assertTrue($this->uploader->upload($this->remote, $this->local, $this->options));
        $this->assertNotEquals(-1, $this->wrapper->size($this->remote->getRealpath()));
        $this->assertTrue($this->called, 'Callback has not been called');

        $this->wrapper->delete($this->remote->getRealpath());
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Invalid remote file given, expected instance of File, got Touki\FTP\Model\Directory
     */
    public function testUploadWrongFilesystemInstance()
    {
        $remote = new Directory('/');

        $this->uploader->upload($remote, $this->local, $this->options);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Invalid local file given. Expected filename, got resource
     */
    public function testUploadResourceGiven()
    {
        $local = fopen($this->local, 'r');

        $this->uploader->upload($this->remote, $local, $this->options);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Invalid local file given. Expected filename, got directory
     */
    public function testUploadDirectoryGiven()
    {
        $local = __DIR__;

        $this->uploader->upload($this->remote, $local, $this->options);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Invalid option given. Expected true as FTP::NON_BLOCKING parameter
     */
    public function testUploadNoOptionNonBlocking()
    {
        $this->uploader->upload($this->remote, $this->local);
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Invalid option given. Expected true as FTP::NON_BLOCKING parameter
     */
    public function testUploadWrongOptionNonBlocking()
    {
        $this->uploader->upload($this->remote, $this->local, array(
            FTP::NON_BLOCKING => false
        ));
    }
}
