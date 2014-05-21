<?php

namespace SS6\ShopBundle\Form\Front\Customer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class CustomerFormType extends AbstractType {

	/**
	 * @return string
	 */
	public function getName() {
		return 'customer';
	}

	/**
	 * @param \Symfony\Component\Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('firstName', 'text', array(
				'constraints' => array(
					new Constraints\NotBlank(array('message' => 'Vyplňte prosím jméno')),
				),
			))
			->add('lastName', 'text', array(
				'constraints' => array(
					new Constraints\NotBlank(array('message' => 'Vyplňte prosím příjmení')),
				),
			))
			->add('telephone', 'text', array('required' => false))
			->add('email', 'email', array('read_only' => true, 'required' => false))
			->add('password', 'repeated', array(
				'type' => 'password',
				'required' => (isset($options['validation_groups']) && in_array('create', $options['validation_groups'])),
				'first_options' => array(
					'constraints' => array(
						new Constraints\NotBlank(array(
							'message' => 'Vyplňte prosím heslo',
							'groups' => array('create'),
						)),
						new Constraints\Length(array('min' => 5, 'minMessage' => 'Heslo musí mít minimálně {{ limit }} znaků')),
					),
					'attr' => array('autocomplete' => 'off'),
				),
				'invalid_message' => 'Hesla se neshodují',
			))
			->add('companyName', 'text', array('required' => false))
			->add('companyNumber', 'text', array('required' => false))
			->add('companyTaxNumber', 'text', array('required' => false))
			->add('street', 'text', array('required' => false))
			->add('city', 'text', array('required' => false))
			->add('zip', 'text', array('required' => false))
			->add('country', 'text', array('required' => false))
			->add('deliveryCompanyName', 'text', array('required' => false))
			->add('deliveryContactPerson', 'text', array('required' => false))
			->add('deliveryTelephone', 'text', array('required' => false))
			->add('deliveryStreet', 'text', array('required' => false))
			->add('deliveryCity', 'text', array('required' => false))
			->add('deliveryZip', 'text', array('required' => false))
			->add('deliveryCountry', 'text', array('required' => false))
			->add('save', 'submit');
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'attr' => array('novalidate' => 'novalidate'),
		));
	}

}