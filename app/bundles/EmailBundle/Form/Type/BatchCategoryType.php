<?php

namespace Mautic\EmailBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class BatchCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'add',
            ChoiceType::class,
            [
                'label'             => 'mautic.email.category.batch.add_to',
                'multiple'          => true,
                'choices'           => $options['items'],
                'required'          => false,
                'label_attr'        => ['class' => 'control-label'],
                'attr'              => ['class' => 'form-control'],
            ]
        );

        //        $builder->add(
        //            'remove',
        //            ChoiceType::class,
        //            [
        //                'label'             => 'mautic.lead.batch.remove_from',
        //                'multiple'          => true,
        //                'choices'           => $options['items'],
        //                'required'          => false,
        //                'label_attr'        => ['class' => 'control-label'],
        //                'attr'              => ['class' => 'form-control'],
        //            ]
        //        );

        $builder->add('ids', HiddenType::class);

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text'     => false,
                'save_text'      => 'mautic.core.form.save',
                'cancel_onclick' => 'javascript:void(0);',
                'cancel_attr'    => [
                    'data-dismiss' => 'modal',
                ],
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'items',
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'email_batch';
    }
}
