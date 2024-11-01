<?php

namespace App\Modules\Auth\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Basic user model with roles and password for authentication.
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
	private string $password;
	private string $username;
	private array $roles;

	/**
	 * @param string $username User's unique identifier
	 * @param string $password Hashed password
	 * @param array $roles User roles
	 */
	public function __construct(string $username, string $password, array $roles = ['ROLE_USER'])
	{
		$this->username = $username;
		$this->password = $password;
		$this->roles = $roles;
	}

	/**
	 * Gets the hashed password.
	 *
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * Gets user roles.
	 *
	 * @return array
	 */
	public function getRoles(): array
	{
		return $this->roles;
	}

	/**
	 * Clears sensitive data.
	 */
	public function eraseCredentials(): void
	{
		// Clear temporary sensitive data if needed
	}

	/**
	 * Gets the user identifier (username).
	 *
	 * @return string
	 */
	public function getUserIdentifier(): string
	{
		return $this->username;
	}
}
