<?php

/**
 * This file is a part of the FTP Wrapper package
 *
 * For the full informations, please read the README file
 * distributed with this source code
 *
 * @package FTP Wrapper
 * @version 1.0.0
 * @author  Touki <g.vincendon@vithemis.com>
 */

namespace Touki\FTP\Uploader\FTP;

use Touki\FTP\FTP;
use Touki\FTP\FTPWrapper;
use Touki\FTP\UploaderInterface;
use Touki\FTP\UploaderVotableInterface;
use Touki\FTP\Model\Filesystem;
use Touki\FTP\Model\File;

/**
 * FTP Resource uploader
 *
 * @author Touki <g.vincendon@vithemis.com>
 */
class ResourceUploader implements UploaderInterface, UploaderVotableInterface
{
    /**
     * FTP Wrapper
     * @var FTPWrapper
     */
    protected $wrapper;

    /**
     * Constructor
     *
     * @param FTPWrapper $wrapper An FTPWrapper instance
     */
    public function __construct(FTPWrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * {@inheritDoc}
     */
    public function vote(Filesystem $remote, $local, array $options = array())
    {
        return
            ($remote instanceof File)
            && true === is_resource($local)
            && isset($options[ FTP::NON_BLOCKING ])
            && false === $options[ FTP::NON_BLOCKING ]
        ;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException When argument(s) is(are) incorrect
     */
    public function upload(Filesystem $remote, $local, array $options = array())
    {
        if (!($remote instanceof File)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid remote file given, expected instance of File, got %s",
                get_class($remote)
            ));
        }

        if (true !== is_resource($local)) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid local file given. Expected resource, got %s",
                gettype($local)
            ));
        }

        if (!isset($options[ FTP::NON_BLOCKING ]) || false !== $options[ FTP::NON_BLOCKING ]) {
            throw new \InvalidArgumentException("Invalid option given. Expected false as FTP::NON_BLOCKING parameter");
        }

        $defaults = array(
            FTP::TRANSFER_MODE => FTPWrapper::BINARY,
            FTP::START_POS     => 0
        );
        $options = $options + $defaults;

        $this->wrapper->pasv(true);

        return $this->wrapper->fput($remote->getRealPath(), $local, $options[ FTP::TRANSFER_MODE ], $options[ FTP::START_POS ]);
    }
}
