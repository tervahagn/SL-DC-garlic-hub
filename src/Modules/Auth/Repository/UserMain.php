<?php

namespace App\Modules\Auth\Repository;

use App\Framework\BaseRepositories\Sql;
use App\Framework\Database\DBHandler;
use App\Framework\Database\QueryBuilder;
use App\Modules\Auth\Entity\User;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserMain extends Sql implements UserProviderInterface
{

	public function __construct(DBHandler $dbh, QueryBuilder $queryBuilder, UserMainDataPreparer $dataPreparer, string $table, string $id_field)
	{
		parent::__construct($dbh, $queryBuilder, $dataPreparer, 'user_main', 'UID');
	}

	public function refreshUser(UserInterface $user): UserInterface
	{
		return $this->loadUserByIdentifier($user->getUserIdentifier());
	}

	public function supportsClass(string $class): bool
	{
		return $class === User::class;
	}

	public function loadUserByIdentifier(string $identifier): UserInterface
	{
		$where  = "username = '$identifier'"; //$this->DataPreparer->quoteString($identifier);
		$result = $this->getFirstDataSet($this->findAllBy($where));
		if (empty($result))
		{
			$exception = new UserNotFoundException();
			$exception->setUserIdentifier($identifier);
			throw $exception;
		}

		return new User($result['username'], $result['password']); // Passe dies an deine User-Klasse an
	}
}
