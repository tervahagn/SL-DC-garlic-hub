<?php

namespace App\Modules\Auth\Controller;

use App\Framework\Database\DBHandler;
use App\Framework\Database\QueryBuilder;
use App\Modules\Auth\Repositories\UserMain;
use App\Modules\Auth\Repositories\UserMainDataPreparer;
use SlimSession\Helper;

class LoginControllerFactory
{
	public static function create($container): LoginController
	{
		$dbh = $container->get(DBHandler::class);
		$queryBuilder = $container->get(QueryBuilder::class);

		$userMainDataPreparer = new UserMainDataPreparer($dbh);
		$userMain = new UserMain($dbh, $queryBuilder, $userMainDataPreparer);

		return new LoginController($userMain);
	}
}