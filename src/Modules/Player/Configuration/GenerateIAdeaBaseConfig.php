<?php

namespace App\Modules\Player\Configuration;


class GenerateIAdeaBaseConfig extends GenerateBaseConfig
{

	public function replace(): static
	{
		$this->replaceBaseValues();
		$this->replaceNetworkingSections();
		if (array_key_exists('brightness', $this->configData))
			$this->replaceStdSurroundBlock('brightness', $this->configData['brightness']);

		if (array_key_exists('volume', $this->configData))
			$this->replaceStdSurroundBlock('volume', $this->configData['volume']);

		if (array_key_exists('reboot_days', $this->configData) && array_key_exists('reboot_time', $this->configData))
			$this->replaceScheduledReboot();

		return $this;
	}

	protected function replaceScheduledReboot(): static
	{
		$this->template['scheduled_reboot'] [] = [
			'REBOOT_TIME' => $this->configData['reboot_time'],
			'REBOOT_DAYS' => implode(' ', $this->configData['reboot_days'])
		];

		return $this;
	}

}