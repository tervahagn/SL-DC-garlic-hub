<?php

namespace App\Modules\Player\Helper\configuration;


class GenerateIAdeaAndroidConfig extends GenerateIAdeaBaseConfig
{

	protected function getWepAuthentication(): string
	{
		return 'WEP';
	}

}