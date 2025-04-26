<?php


namespace App\Modules\Player\Helper\configuration;


class GenerateBaseConfig
{
	protected array $configData = [];
	protected array $template = [];

	public function __construct(array $configData)
	{
		$this->configData = $configData;
	}

	public function replace(): static
	{
		$this->replaceBaseValues();
		$this->replaceNetworkingSections();
		return $this;
	}

	protected function replaceNetworkingSections(): static
	{
		if (!array_key_exists('connection_type', $this->configData) || !array_key_exists('lan_dhcp', $this->configData))
			return $this;

		if ($this->configData['connection_type'] == 'WLAN')
			$this->replaceWifiSection();
		elseif ($this->configData['connection_type'] == 'UMTS')
			$this->replaceMobileSection();

		if (array_key_exists('proxy_type', $this->configData) && $this->configData['proxy_type'] != 'no_proxy')
			$this->replaceProxySection();

		$this->replaceIP4NetworkSection();
		return $this;
	}

	protected function replaceStdSurroundBlock($block_name, $value): static
	{
		$this->template[strtolower($block_name)][] = [strtoupper($block_name) => $value];

		return $this;
	}

	protected function replaceBaseValues(): static
	{
		if (array_key_exists('name', $this->configData) && $this->configData['name'] != '')
			$this->replaceStdSurroundBlock('info_player_name', $this->configData['name']);

		if (array_key_exists('content_url', $this->configData) && $this->configData['content_url'] != '')
			$this->replaceStdSurroundBlock('content_server_url', $this->configData['content_url']);

		return $this;
	}

	protected function replaceWifiSection(): static
	{
		$authentication = 'OPEN';
		$encryption     = 'NONE';
		$password       = '';
		if (!array_key_exists('wlan_ssid', $this->configData))
			return $this;
		if (array_key_exists('wlan_authentification', $this->configData))
		{
			if ($this->configData['wlan_authentification'] == 'WEP')
			{
				$authentication = $this->getWepAuthentication();
				$encryption     = 'WEP';
				$password       = $this->configData['wlan_password'];
			}
			elseif ($this->configData['wlan_authentification'] == 'WPA1')
			{
				$authentication = 'WPAPSK';
				$encryption     = $this->configData['wlan_encryption'];
				$password       = $this->configData['wlan_password'];
			}
			elseif ($this->configData['wlan_authentification'] == 'WPA2')
			{
				$authentication = 'WPA2PSK';
				$encryption     = $this->configData['wlan_encryption'];
				$password       = $this->configData['wlan_password'];
			}
		}
		$this->template['net_wifi_section'][] = [
			'NET_WIFI_ENABLED' => 'true',
			'NET_WIFI_SSID' => $this->configData['wlan_ssid'],
			'NET_WIFI_AUTHENTICATION' => $authentication,
			'NET_WIFI_ENCRYPTION' => $encryption,
			'NET_WIFI_PASSWORD' => $password
		];
		return $this;
	}

	protected function getWepAuthentication(): string
	{
		return 'WEPAUTO';
	}

	protected function replaceMobileSection(): static
	{
		$this->template['net_umts_section'][] = [
			'NET_UMTS_APN' => $this->configData['umts_apn'],
			'NET_UMTS_PIN' => $this->configData['umts_pin'],
			'NET_UMTS_USER' => $this->configData['umts_user'],
			'NET_UMTS_PASSWORD' => $this->configData['umts_password']
		];

		return $this;
	}

	protected function replaceIP4NetworkSection(): static
	{
		$section = [];
		if ($this->configData['lan_dhcp'] == 0)
			$section['NET_DHCP_ENABLED'] = 'true';
		else
		{
			$this->template['net_ethernet_nodhcp'][] = [
				'NET_DHCP_ENABLED' => 'false',
				'NET_ETHERNET_IP' => $this->configData['lan_ip'],
				'NET_ETHERNET_NETMASK' => $this->configData['lan_netmask'],
				'NET_ETHERNET_GATEWAY' => $this->configData['lan_gateway'],
				'NET_ETHERNET_DOMAIN' => $this->configData['lan_domain'],
				'NET_ETHERNET_DNS' => $this->configData['lan_dns']
			];
		}
		$this->template['net_ethernet_section'][] = $section;

		return $this;
	}

	protected function replaceProxySection(): static
	{
		$this->template['net_proxy_section'][] = [
			'NET_PROXY_TYPE' => $this->configData['proxy_type'],
			'NET_PROXY_HOST' => $this->configData['proxy_host'],
			'NET_PROXY_PORT' => $this->configData['proxy_port'],
			'NET_PROXY_USER' => $this->configData['proxy_user'],
			'NET_PROXY_PASSWORD' => $this->configData['proxy_password']
		];

		return $this;
	}


}