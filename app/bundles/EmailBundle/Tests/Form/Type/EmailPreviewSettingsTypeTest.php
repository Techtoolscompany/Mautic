<?php

declare(strict_types=1);

namespace Mautic\EmailBundle\Tests\Form\Type;

use Mautic\CoreBundle\Form\Type\SelectType;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Form\Type\EmailPreviewSettingsType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailPreviewSettingsTypeTest extends TestCase
{
    /**
     * @var EmailPreviewSettingsType|MockObject
     */
    private $form;

    protected function setUp()
    {
        $this->form = new EmailPreviewSettingsType();

        parent::setUp();
    }

    public function testConfigureOptions(): void
    {
        $resolver = $this->createMock(OptionsResolver::class);
        $resolver->expects(self::once())
            ->method('setDefaults')
            ->with(
                [
                    'emailId'      => null,
                    'translations' => null,
                    'variants'     => null,
                ]
            );

        $this->form->configureOptions($resolver);
    }

    public function testGetBlockPrefix(): void
    {
        self::assertSame('email_preview_settings', $this->form->getBlockPrefix());
    }

    public function testBuildFormWithTranslationAndVariantFieldNotAvailable(): void
    {
        $emailId = 1;
        $options = [
            'emailId'      => $emailId,
            'translations' => [
                'children' => [],
            ],
            'variants'     => [
                'children' => [],
            ],
        ];

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('add')
            ->withConsecutive(
                [
                    'contact',
                    SelectType::class,
                    [
                        'attr' => [
                            'onChange'         => "Mautic.emailPreview.regenerateUrl({$emailId})",
                            'data-placeholder' => 'Choose ...',
                        ],
                    ],
                ]
            );

        $this->form->buildForm($builder, $options);
    }

    public function testBuildFormWithTranslationAndVariantFieldAvailable(): void
    {
        $emailId = 1;
        $email   = new Email();
        $email->setId($emailId);

        $expectedTranslations = [
            1  => 'First (en)',
            2  => 'Second (cs)',
            3  => 'Third (it)',
        ];

        $expectedVariants = [
            1  => 'First (1)',
            2  => 'Second (2)',
            3  => 'Third (3)',
        ];

        $options = [
            'emailId'      => $emailId,
            'translations' => [
                'parent'   => $this->createEmailWithNameAndId($expectedTranslations[1], 1),
                'children' => [
                    $this->createEmailWithNameAndId($expectedTranslations[2], 2),
                    $this->createEmailWithNameAndId($expectedTranslations[3], 3),
                ],
            ],
            'variants'     => [
                'parent'   => $this->createEmailWithNameAndId($expectedVariants[1], 1),
                'children' => [
                    $this->createEmailWithNameAndId($expectedVariants[2], 2),
                    $this->createEmailWithNameAndId($expectedVariants[3], 3),
                ],
            ],
        ];

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::exactly(3))
            ->method('add')
            ->withConsecutive([
                    'translation',
                    ChoiceType::class,
                    [
                        'choices' => array_flip($expectedTranslations),
                        'attr'    => [
                            'onChange' => "Mautic.emailPreview.regenerateUrl({$emailId})",
                        ],
                    ],
                ],
                [
                    'variant',
                    ChoiceType::class,
                    [
                        'choices' => array_flip($expectedVariants),
                        'attr'    => [
                            'onChange' => "Mautic.emailPreview.regenerateUrl({$emailId})",
                        ],
                    ],
                ],
                [
                    'contact',
                    SelectType::class,
                    [
                        'attr' => [
                            'onChange' => "Mautic.emailPreview.regenerateUrl({$emailId})",
                        ],
                    ],
                ]
            );

        $this->form->buildForm($builder, $options);
    }

    private function createEmailWithNameAndId(string $name, int $id): Email
    {
        $name = substr($name, 0, strpos($name, ' '));

        $email = new Email();
        $email->setName($name);
        $email->setId($id);

        return $email;
    }
}
