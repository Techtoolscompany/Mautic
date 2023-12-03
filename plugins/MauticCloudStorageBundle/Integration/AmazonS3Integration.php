<?php

namespace MauticPlugin\MauticCloudStorageBundle\Integration;

use Aws\S3\S3Client;
use Gaufrette\Adapter\AwsS3;
use Gaufrette\Extras\Resolvable\ResolvableFilesystem;
use Gaufrette\Extras\Resolvable\Resolver\AwsS3PublicUrlResolver;
use Gaufrette\Filesystem;
use MauticPlugin\MauticCloudStorageBundle\Exception\NoFormNeededException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

class AmazonS3Integration extends CloudStorageIntegration
{
    /**
     * @var ResolvableFilesystem|null
     */
    private $fileSystem;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'AmazonS3';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return 'Amazon S3';
    }

    /**
     * Get the array key for clientId.
     *
     * @return string
     */
    public function getClientIdKey()
    {
        return 'client_id';
    }

    /**
     * Get the array key for client secret.
     *
     * @return string
     */
    public function getClientSecretKey()
    {
        return 'client_secret';
    }

    /**
     * @return array<string, string>
     */
    public function getRequiredKeyFields(): array
    {
        return [
            'client_id'     => 'mautic.integration.keyfield.clientid',
            'client_secret' => 'mautic.integration.keyfield.clientsecret',
            'bucket'        => 'mautic.integration.keyfield.amazons3.bucket',
        ];
    }

    /**
     * @param Form|FormBuilder $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea)
    {
        if ('keys' === $formArea) {
            $builder->add(
                'region',
                TextType::class,
                [
                    'label'    => 'mautic.integration.Amazon.region',
                    'attr'     => ['class'   => 'form-control'],
                    'data'     => empty($data['region']) ? 'us-east-1' : $data['region'],
                    'required' => false,
                ]
            );

            $builder->add(
                'endpoint',
                TextType::class,
                [
                    'label'    => 'mautic.integration.Amazon.endpoint',
                    'attr'     => ['class'   => 'form-control'],
                    'data'     => empty($data['endpoint']) ? null : $data['endpoint'],
                    'required' => false,
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return AwsS3
     */
    public function getAdapter()
    {
        if (!$this->adapter instanceof \Gaufrette\Adapter || !$this->fileSystem instanceof \Gaufrette\Extras\Resolvable\ResolvableFilesystem) {
            $keys = $this->getDecryptedApiKeys();

            $s3Args = [
                'version'     => 'latest',
                'region'      => (empty($keys['region'])) ? 'us-east-1' : $keys['region'],
                'credentials' => [
                    'key'    => $keys['client_id'],
                    'secret' => $keys['client_secret'],
                ],
            ];
            if (!empty($keys['endpoint'])) {
                $s3Args['endpoint'] = $keys['endpoint'];
            }

            $service = new S3Client($s3Args);

            $this->adapter    = new AwsS3($service, $keys['bucket']);
            $decorated        = new Filesystem($this->adapter);
            $this->fileSystem = new ResolvableFilesystem(
                $decorated,
                new AwsS3PublicUrlResolver($service, $keys['bucket'])
            );
        }

        return $this->adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new NoFormNeededException();
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicUrl($key)
    {
        $this->getAdapter();

        return $this->fileSystem->resolve($key);
    }
}
