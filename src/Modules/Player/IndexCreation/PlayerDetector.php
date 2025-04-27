<?php

namespace App\Modules\Player\IndexCreation;

use App\Framework\Core\Config\Config;
use App\Modules\Player\Helper\PlayerModel;

class PlayerDetector
{
	protected PlayerModel $modelId;

	protected Config $config;

	public function __construct(Config $Config)
	{
		$this->config = $Config;
	}

	public function getModelId(): PlayerModel
	{
		return $this->modelId;
	}

	public function isAllowedPlayer(): bool
	{
		return !($this->modelId === PlayerModel::UNKNOWN);
	}

	public function detectModelId($modelName): static
	{
		$this->modelId = match ($modelName)
		{
			'XMP-120', 'XMP-130', 'XDS-101', 'XDS-104', 'XDS-151' => PlayerModel::IADEA_XMP1X0,
			'XMP-320', 'XMP-330', 'XMP-340', 'XDS-195', 'XDS-245', 'GDATA-1100' => PlayerModel::IADEA_XMP3X0,
			'XMP-3250', 'XMP-3350', 'XMP-3450', 'XDS-1950', 'XDS-2450' => PlayerModel::IADEA_XMP3X50,
			'fs5-player', 'fs5-playerSTLinux', 'NTnextPlayer', 'Kathrein', 'NT111', 'NTwin' => PlayerModel::COMPATIBLE,
			'XMP-2200', 'MBR-1100', 'XMP-6200', 'XMP-6250', 'XMP-6400', 'XMP-7300', 'XDS-1060', 'XDS-1062', 'XDS-1068', 'XDS-1078', 'XDS-1071', 'XDS-1078-A9', 'XDS-1588', 'XDS-1588-A' => PlayerModel::IADEA_XMP2X00,
			'Garlic' => PlayerModel::GARLIC,
			'IDS-App' => PlayerModel::IDS,
			'BXP-202', 'BXP-301', 'TD-1050' => PlayerModel::QBIC,
			default => PlayerModel::UNKNOWN,
		};
		return $this;
	}

	public function selectIndexTemplate($firmwareVersion): string
	{
		switch ($this->modelId)
		{
			case PlayerModel::IADEA_XMP2X00:
			case PlayerModel::QBIC:
				$index = 'index_XMP2x00.smil.tpl';
				break;
			case PlayerModel::GARLIC->value:
				$garlic_build = $this->determineGarlicBuild($firmwareVersion);
				if ($garlic_build >= 566)
					$index = 'index_garlic.smil.tpl';
				else
					$index = 'index.smil.tpl';
				break;
			case PlayerModel::IADEA_XMP1X0->value:
			case PlayerModel::IADEA_XMP3X0->value:
			case PlayerModel::IADEA_XMP3X50->value:
			case PlayerModel::IDS->value:
			case PlayerModel::COMPATIBLE->value:
				$index = 'index_old.smil.tpl';
			break;
			default:
				$index = 'index.smil.tpl';
				break;
		}
		return $index;
	}

	private function determineGarlicBuild(string $firmwareVersion): int
	{
		$ar = explode('L', $firmwareVersion);
		$ar = explode('.', $ar[0]);
		return (int) end($ar);
	}

}