<?php

namespace App\Modules\Player\Helper\configuration;


class GenerateIDSConfig extends GenerateBaseConfig
{
	public function replace(): static
	{
		$this->replaceBaseValues();
		$this->replaceNetWorkingSections();
		if (array_key_exists('password_main', $this->configData))
			$this->replaceStdSurroundBlock('password_main', $this->configData['password_main']);

		if (array_key_exists('password_menu', $this->configData))
			$this->replaceStdSurroundBlock('password_menu', $this->configData['password_menu']);

		if (array_key_exists('startup_autostart', $this->configData))
			$this->replaceStdSurroundBlock('startup_autostart', $this->configData['startup_autostart']);

		if($this->configData['startup_delay_seconds'])
			$this->replaceStdSurroundBlock('startup_delay_seconds', $this->configData['startup_delay_seconds']);

		if($this->configData['reboot_on_wake_on'])
			$this->replaceStdSurroundBlock('reboot_on_wake_on', $this->configData['reboot_on_wake_on']);

		if($this->configData['content_loop'])
			$this->replaceStdSurroundBlock('content_loop', $this->configData['content_loop']);

		if($this->configData['content_jump_to_first'])
			$this->replaceStdSurroundBlock('content_jump_to_first', $this->configData['content_jump_to_first']);

		if($this->configData['content_touch_overlay'])
			$this->replaceStdSurroundBlock('content_touch_overlay', $this->configData['content_touch_overlay']);

		if($this->configData['system_back_button'])
			$this->replaceStdSurroundBlock('system_back_button', $this->configData['system_back_button']);

		if($this->configData['system_watchdog'])
			$this->replaceStdSurroundBlock('system_watchdog', $this->configData['system_watchdog']);

		if($this->configData['system_bar_hide'])
			$this->replaceStdSurroundBlock('system_bar_hide', $this->configData['system_bar_hide']);

		return $this;
	}
}