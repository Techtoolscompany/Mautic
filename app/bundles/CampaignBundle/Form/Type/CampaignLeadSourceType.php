<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class CampaignLeadSourceType.
 */
class CampaignLeadSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sourceType = $options['data']['sourceType'];

        switch ($sourceType) {
            case 'lists':
                $builder->add(
                    'lists',
                    'choice',
                    [
                        'choices'    => $options['source_choices'],
                        'multiple'   => true,
                        'label'      => 'mautic.campaign.leadsource.lists',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class' => 'form-control',
                        ],
                        'constraints' => [
                            new NotBlank(
                                [
                                    'message' => 'mautic.core.value.required',
                                ]
                            ),
                        ],
                    ]
                );
                break;
            case 'forms':
                $builder->add(
                    'forms',
                    'choice',
                    [
                        'choices'    => $options['source_choices'],
                        'multiple'   => true,
                        'label'      => 'mautic.campaign.leadsource.forms',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class' => 'form-control',
                        ],
                        'constraints' => [
                            new NotBlank(
                                [
                                    'message' => 'mautic.core.value.required',
                                ]
                            ),
                        ],
                    ]
                );
                break;
            default:
                break;
        }

        $builder->add('sourceType', 'hidden');

        $builder->add('droppedX', 'hidden');

        $builder->add('droppedY', 'hidden');

        $update = !empty($options['data'][$sourceType]);
        if (!empty($update)) {
            $btnValue = 'mautic.core.form.update';
            $btnIcon  = 'fa fa-pencil';
        } else {
            $btnValue = 'mautic.core.form.add';
            $btnIcon  = 'fa fa-plus';
        }

        $builder->add('buttons', 'form_buttons', [
            'save_text'       => $btnValue,
            'save_icon'       => $btnIcon,
            'save_onclick'    => 'Mautic.submitCampaignSource(event)',
            'apply_text'      => false,
            'container_class' => 'bottom-form-buttons',
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['source_choices']);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'campaign_leadsource';
    }
}
