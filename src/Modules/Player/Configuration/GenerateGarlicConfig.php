<?php

namespace App\Modules\Player\Configuration;


class GenerateGarlicConfig extends GenerateIAdeaBaseConfig
{
	public function replace(): static
	{
		parent::replace();

		if (array_key_exists('standby_mode', $this->configData))
			$this->replaceStdSurroundBlock('standby_mode', $this->configData['standby_mode']);

		return $this;
	}
}