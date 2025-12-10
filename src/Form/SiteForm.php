<?php 

namespace App\Form;

use App\Entity\Main\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;
class SiteForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sitRaisonsociale', TextType::class, [
                'label' => 'Raison Sociale',
                'required' => true,
                'attr' => [
                    'class' => 'form-control custom-form-control',
                    'disabled' => $options['disabled'],
                ],
                'constraints' => [
                    new NotBlank(['message' => 'La raison sociale est requise.']),
                   
                ],
            ])
            ->add('sitAdresse', TextType::class, [
                'label' => 'Adresse',
                'required' => true,
                'attr' => [
                    'class' => 'form-control custom-form-control',
                    'disabled' => $options['disabled'],
                ],
                'constraints' => [
                    new NotBlank(['message' => "L'adresse est requise."]),
                    
                ],
            ])
            ->add('sitTel', TextType::class, [
                'label' => 'Téléphone',
                'required' => true,
                'attr' => [
                    'class' => 'form-control custom-form-control',
                    'disabled' => $options['disabled'],
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est requis.']),
                    
                ],
            ])
            ->add('sitMail', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'attr' => [
                    'class' => 'form-control custom-form-control',
                    'disabled' => $options['disabled'],
                ],
                'constraints' => [
                    new NotBlank(['message' => "L'adresse email est requise."]),
                    new Email(['message' => "L'adresse email n'est pas valide."]),
                ],
            ])
            ->add('sitCode', TextType::class, [
                'label' => 'Code',
                'required' => true,
                'attr' => [
                    'class' => 'form-control custom-form-control',
                    'disabled' => $options['disabled'],
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le code est requis.']),
                    new Length([
                        'max' => 10,
                        'maxMessage' => 'Le code ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('sitBddNom', TextType::class, [
                'label' => 'Nom de la base de données',
                'required' => true,
                'attr' => [
                    'class' => 'form-control custom-form-control',
                    'disabled' => $options['disabled'],
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la base de données est requis.']),
                    
                ],
            ])
            ->add('sitBddUser', TextType::class, [
                'label' => "Utilisateur de la base de données",
                'required' => true,
                'attr' => [
                    'class' => 'form-control custom-form-control',
                    'disabled' => $options['disabled'],
                ],
                'constraints' => [
                    new NotBlank(['message' => "L'utilisateur de la base de données est requis."]),
                    
                ],
            ])
            ->add('sitBddMdp', TextType::class, [
                'label' => 'Mot de passe de la base de données',
                'required' => true,
                'attr' => [
                    'class' => 'form-control custom-form-control',
                    'disabled' => $options['disabled'],
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe de la base de données est requis.']),
                    
                ],
            ])
            
            ->add('estActif', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input bg-primary border border-primary',
                    'role' => 'switch',
                    'disabled' => $options['disabled'],
                ],
            ])
           ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
            'disabled' => false,
        ]);

        $resolver->setAllowedTypes('disabled', 'bool');
    }
}