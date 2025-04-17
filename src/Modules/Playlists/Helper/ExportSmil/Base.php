<?php
namespace App\Modules\Playlists\Helper\ExportSmil;


use App\Framework\Exceptions\ModuleException;

/**
 * Export playlist items from db and write the SMIL-file body to disc
 * Channels and internal playlists are asigned as placeholders like {XYZ_ITEM_2}
 * It also creates a SMIL-file for the Javascript SMIL preview-player
 *
 * Class player_playlist creates at least complete SMIL head and body from the
 * files created by this class
 *
 * Real playlist duration and filesize needs be calculated after export and set to database
 */
abstract class Base
{
	/**
	 * @var string
	 */
	protected $playlist_base_path;

	/**
	 * @var string
	 */
	protected $media_pool_path;

	/**
	 * @var string
	 */
	protected $templates_path;

	public function  __construct(Config $Config, ItemsRepositoryFactory $repositoryFactory)
	{
		$this->setPlaylistModel($PlaylistModel)
			 ->setItemsModel($itemsModel)
			 ->setConfig($Config);
	}

	abstract public function createMediaSymlinks(Content $Content);
	abstract public function createTemplatesSymlinks(Content $Content);


	public function setPlaylistBasePath($path): static
	{
		$real_path = realpath(_BasePath . $path);
		if ($real_path === false)
			throw new ModuleException($this->moduleName, 'Playlist path of ' . $real_path . ' does not exists');

		$this->playlist_base_path =  $real_path . DIRECTORY_SEPARATOR;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPlaylistBasePath()
	{
		return $this->playlist_base_path;
	}

	/**
	 * @param   string  $path
	 * @return  $this
	 * @throws  ModuleException
	 */
	public function setMediaPoolPath($path)
	{
		$this->media_pool_path = Base . phprealpath($path) . DIRECTORY_SEPARATOR;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMediaPoolPath()
	{
		return $this->media_pool_path;
	}

	/**
	 * @param   string  $path
	 * @return  $this
	 * @throws  ModuleException
	 */
	public function setTemplatesPath($path)
	{
		$this->templates_path = Base . phprealpath($path) . DIRECTORY_SEPARATOR;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTemplatesPath()
	{
		return $this->templates_path;
	}

// ======================  protected Functions ============================================

// ==================== Service methods ======================================

}