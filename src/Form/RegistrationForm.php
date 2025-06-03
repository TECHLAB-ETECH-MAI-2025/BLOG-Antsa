<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
//use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
					'label' => 'First name',
					'required' => false,
					'attr' => [
						'class' => 'form-control',
						'placeholder' => 'Your first name'
					]
				])
				->add('lastName', TextType::class, [
					'label' => 'Lastname',
					'required' => false,
					'attr' => [
						'class' => 'form-control',
						'placeholder' => 'Your last name'
					]
				])
				->add('email', EmailType::class, [
					'label' => 'Email',
					'attr' => [
						'class' => 'form-control',
						'placeholder' => 'exemple@domaine.com'
					],
					'constraints' => [
						new NotBlank([
							'message' => 'Your mail',
						]),
						// new Email([
						// 	'message' => 'L\'adresse email n\'est pas valide',
						// ])
					]
				])
				->add('agreeTerms', CheckboxType::class, [
					'label' => 'I agree',
					'mapped' => false,
					'constraints' => [
						new IsTrue([
							'message' => 'You must accept our terms and conditions',
						]),
					],
					'attr' => [
						'class' => 'form-check-input'
					],
					'label_attr' => [
						'class' => 'form-check-label'
					]
				])
				->add('plainPassword', RepeatedType::class, [
					'type' => PasswordType::class,
					'mapped' => false,
					'first_options' => [
						'label' => 'Password',
						'attr' => [
							'class' => 'form-control',
							'autocomplete' => 'new-password',
							'placeholder' => 'Minimum 8 caractères'
						],
					],
					'second_options' => [
						'label' => 'Confirm password',
						'attr' => [
							'class' => 'form-control',
							'autocomplete' => 'new-password',
							'placeholder' => 'New password'
						],
					],
					'invalid_message' => 'Password do not match',
					'constraints' => [
						new NotBlank([
							'message' => 'Enter password',
						]),
						new Length([
							'min' => 8,
							'minMessage' => 'Your password must be at least {{ limit }} characters long.',
							'max' => 4096,
						]),
					],
				])
			;
		}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
