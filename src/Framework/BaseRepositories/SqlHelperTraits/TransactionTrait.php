<?php

namespace App\Framework\BaseRepositories\SqlHelperTraits;

use App\Framework\Database\DBHandler;

trait TransactionTrait
{
	/**
	 * @var DBHandler Database adapter
	 */
	protected DBHandler $dbh;

	/**
	 * Begins a database transaction.
	 *
	 * @return void
	 */
	public function beginTransaction(): void
	{
		$this->dbh->beginTransaction();
	}

	/**
	 * Commits a database transaction.
	 *
	 * @return void
	 */
	public function commitTransaction(): void
	{
		$this->dbh->commitTransaction();
	}

	/**
	 * Rolls back a database transaction.
	 *
	 * @return void
	 */
	public function rollbackTransaction(): void
	{
		$this->dbh->rollbackTransaction();
	}

	/**
	 * Checks if a transaction is active.
	 *
	 * @return bool
	 */
	public function isTransactionActive(): bool
	{
		return $this->dbh->hasActiveTransaction();
	}

}