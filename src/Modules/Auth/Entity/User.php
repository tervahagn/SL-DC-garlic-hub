<?php

namespace App\Modules\Auth\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	private string $password;
	private string $username;
	private array $roles = ['ROLE_USER'];

	public function __construct(string $username, string $password, array $roles = ['ROLE_USER'])
	{
		$this->username = $username;
		$this->password = $password;
		$this->roles = $roles;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	public function getRoles(): array
	{
		return $this->roles;
	}

	public function eraseCredentials(): void
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	public function getUserIdentifier(): string
	{
		return $this->username;
	}
}
