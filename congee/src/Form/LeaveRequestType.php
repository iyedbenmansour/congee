<?php

// src/Form/LeaveRequestType.php
namespace App\Form;

use App\Entity\LeaveRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class LeaveRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('employeeId', TextType::class, [
                'label' => 'Employee ID',
            ])
            ->add('companyId', TextType::class, [
                'label' => 'Company ID',
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Start Date',
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'End Date',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('leaveType', ChoiceType::class, [
                'label' => 'Leave Type',
                'choices' => [
                    'Normal' => 'normal',
                    'Maladie' => 'maladie',
                    'Paternité' => 'paternite',
                    'Maternité' => 'maternite'
                ],
                'placeholder' => 'Select a leave type',
            ])
            ->add('pdfFile', FileType::class, [
                'label' => 'Supporting Document (PDF)',
                'mapped' => false, // This field is not mapped to the entity
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LeaveRequest::class,
        ]);
    }
}