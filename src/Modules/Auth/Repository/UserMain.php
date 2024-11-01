<?php

namespace App\Modules\Auth\Repository;

use App\Framework\BaseRepositories\Sql;
use App\Framework\Database\DBHandler;
use App\Framework\Database\QueryBuilder;
use App\Modules\Auth\Entity\User;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Provides user data handling for authentication.
 */
class UserMain extends Sql implements UserProviderInterface
{
	/**
	 * @param DBHandler $dbh Database handler
	 * @param QueryBuilder $queryBuilder Query builder
	 * @param UserMainDataPreparer $dataPreparer Data preparer
	 * @param string $table Database table name
	 * @param string $id_field ID field name
	 */
	public function __construct(DBHandler $dbh, QueryBuilder $queryBuilder, UserMainDataPreparer $dataPreparer, string $table, string $id_field)
	{
		parent::__construct($dbh, $queryBuilder, $dataPreparer, 'user_main', 'UID');
	}

	/**
	 * Reloads user data by identifier.
	 *
	 * @param UserInterface $user The user to refresh
	 * @return UserInterface
	 */
	public function refreshUser(UserInterface $user): UserInterface
	{
		return $this->loadUserByIdentifier($user->getUserIdentifier());
	}

	/**
	 * Checks if this provider supports a given user class.
	 *
	 * @param string $class Class name to check
	 * @return bool
	 */
	public function supportsClass(string $class): bool
	{
		return $class === User::class;
	}

	/**
	 * Loads a user by their identifier.
	 *
	 * @param string $identifier Username identifier
	 * @return UserInterface
	 * @throws UserNotFoundException If user is not found
	 */
	public function loadUserByIdentifier(string $identifier): UserInterface
	{
		$where = "username = '$identifier'";
		$result = $this->getFirstDataSet($this->findAllBy($where));
		if (empty($result)) {
			$exception = new UserNotFoundException();
			$exception->setUserIdentifier($identifier);
			throw $exception;
		}

		return new User($result['username'], $result['password']); // Adjust to match User class
	}
}
