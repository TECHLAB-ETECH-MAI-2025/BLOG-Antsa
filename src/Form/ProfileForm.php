<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('firstName', TextType::class, [
				'label' => 'Prénom',
				'required' => false,
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'Votre prénom'
				]
			])
			->add('lastName', TextType::class, [
				'label' => 'Nom',
				'required' => false,
				'attr' => [
					'class' => 'form-control',
					'placeholder' => 'Votre nom'
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
						'message' => 'Veuillez entrer une adresse email',
					]),
					new Email([
						'message' => 'L\'adresse email n\'est pas valide',
					])
				]
			])
			->add('plainPassword', RepeatedType::class, [
				'type' => PasswordType::class,
				'mapped' => false,
				'required' => false,
				'invalid_message' => 'Les mots de passe doivent correspondre.',
				'first_options'  => [
					'label' => 'Nouveau mot de passe',
					'attr' => ['class' => 'form-control']
				],
				'second_options' => [
					'label' => 'Confirmer le mot de passe',
					'attr' => ['class' => 'form-control']
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
