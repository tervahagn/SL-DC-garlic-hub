<?php
namespace App\Modules\Playlists\Helper\export;


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
abstract class Base extends BaseItemController
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

	/**
	 * @param string    $module_name
	 * @param Config    $Config
	 * @param Model     $PlaylistModel
	 * @param ItemsModel $itemsModel
	 */
	public function  __construct($module_name, Config $Config, Model $PlaylistModel, ItemsModel $itemsModel)
	{
		$this->setModuleName($module_name)
			 ->setPlaylistModel($PlaylistModel)
			 ->setItemsModel($itemsModel)
			 ->setConfig($Config);
	}

	abstract public function createMediaSymlinks(Content $Content);
	abstract public function createTemplatesSymlinks(Content $Content);

	/**
	 * @param   string  $path
	 * @return  $this
	 * @throws  ModuleException
	 */
	public function setPlaylistBasePath($path)
	{
		$real_path = realpath(_BasePath . $path);
		if ($real_path === false)
		{
			throw new ModuleException($this->getModuleName(), 'Playlist path of ' . $real_path . ' does not exists');
		}

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