<?php

namespace Mautic\EmailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Ramsey\Uuid\Uuid;

class EmailReply
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Stat
     */
    private $stat;

    /**
     * @var \DateTime
     */
    private $dateReplied;

    /**
     * @var string
     */
    private $messageId;

    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('email_stat_replies')
            ->setCustomRepositoryClass(EmailReplyRepository::class)
            ->addIndex(['stat_id', 'message_id'], 'email_replies')
            ->addIndex(['date_replied'], 'date_email_replied');

        $builder->addUuid();

        $builder->createManyToOne('stat', Stat::class)
            ->inversedBy('replies')
            ->addJoinColumn('stat_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->createField('dateReplied', 'datetime')
            ->columnName('date_replied')
            ->length(3)
            ->build();

        $builder->createField('messageId', 'string')
            ->columnName('message_id')
            ->build();
    }

    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('emailReply')
            ->addProperties(
                [
                    'uuid',
                    'dateReplied',
                    'messageId',
                ]
            )
            ->build();
    }

    /**
     * @param string $messageId
     */
    public function __construct(Stat $stat, $messageId, \DateTime $dateReplied = null)
    {
        $this->id          = Uuid::uuid4()->toString();
        $this->stat        = $stat;
        $this->messageId   = $messageId;
        $this->dateReplied = (null === $dateReplied) ? new \DateTime() : $dateReplied;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Stat
     */
    public function getStat()
    {
        return $this->stat;
    }

    /**
     * @return \DateTime
     */
    public function getDateReplied()
    {
        return $this->dateReplied;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }
}
