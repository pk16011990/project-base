<?php

namespace SS6\ShopBundle\Model\Customer;

use DateTime;
use Doctrine\ORM\EntityRepository;
use SS6\ShopBundle\Model\Customer\User;
use SS6\ShopBundle\Model\Security\UniqueLoginInterface;
use SS6\ShopBundle\Model\Security\TimelimitLoginInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SecurityUserRepository extends EntityRepository implements UserProviderInterface {

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	protected function getUserRepository() {
		return $this->em->getRepository(User::class);
	}
	

	/**
	 * @param string $email
	 * @return \SS6\ShopBundle\Model\Customer\User
	 * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException if the user is not found
	 */
	public function loadUserByUsername($email) {
		$user = $this->findOneBy(array('email' => $email));

		if ($user === null) {
			$message = sprintf(
				'Unable to find an active SS6\ShopBundle\Model\Customer\User object identified by email "%s".', $email
			);
			throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException($message, 0);
		}

		return $user;
	}

	/**
	 * @param UserInterface $user
	 * @return \SS6\ShopBundle\Model\Customer\User
	 * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
	 * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
	 */
	public function refreshUser(UserInterface $user) {
		$class = get_class($user);
		if (!$this->supportsClass($class)) {
			$message = sprintf('Instances of "%s" are not supported.', $class);
			throw new \Symfony\Component\Security\Core\Exception\UnsupportedUserException($message);
		}
		
		if ($user instanceof TimelimitLoginInterface) {
			if (time() - $user->getLastActivity()->getTimestamp() > 3600 * 24) {
				throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException('User was too long unactive');
			}
			$user->setLastActivity(new DateTime());
		}
		
		$findParams = array(
			'id' => $user->getId(),
		);
		if ($user instanceof UniqueLoginInterface) {
			$findParams['loginToken'] = $user->getLoginToken();
		}
		$freshUser = $this->findOneBy($findParams);

		if ($freshUser === null) {
			throw new \Symfony\Component\Security\Core\Exception\UsernameNotFoundException('Unable to find an active admin');
		}
		
		return $freshUser;
	}

	/**
	 * @param string $class
	 * @return bool
	 */
	public function supportsClass($class) {
		return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
	}

}