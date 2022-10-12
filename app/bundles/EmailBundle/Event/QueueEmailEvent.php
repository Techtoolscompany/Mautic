<?php

namespace Mautic\EmailBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Mime\Email;

/**
 * Class QueueEmailEvent.
 */
class QueueEmailEvent extends Event
{
    /**
     * @var Email
     */
    private $message;

    /**
     * @var bool
     */
    private $retry = false;

    public function __construct(Email $message)
    {
        $this->message = $message;
    }

    /**
     * @return Email
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets whether the sending of the message should be tried again.
     */
    public function tryAgain()
    {
        $this->retry = true;
    }

    /**
     * @return bool
     */
    public function shouldTryAgain()
    {
        return $this->retry;
    }
}
