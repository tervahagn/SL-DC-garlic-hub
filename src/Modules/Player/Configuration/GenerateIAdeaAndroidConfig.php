<?php

namespace App\Modules\Player\Configuration;


class GenerateIAdeaAndroidConfig extends GenerateIAdeaBaseConfig
{

	protected function getWepAuthentication(): string
	{
		return 'WEP';
	}

}