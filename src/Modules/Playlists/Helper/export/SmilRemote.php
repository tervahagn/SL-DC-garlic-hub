<?php
namespace Thymian\modules\playlists\export;

use Thymian\framework\core\Config;
use Thymian\framework\Curl;
use Thymian\framework\exceptions\ModuleException;
use Thymian\modules\playlists\Content;

class SmilRemote extends Base
{
	/**
	 * @var Curl
	 */
	protected $Curl;

	/**
	 * @param Curl $Curl
	 * @return $this
	 */
	public function setCurl(Curl $Curl)
	{
		$this->Curl = $Curl;
		return $this;
	}

	/**
	 * @return Curl
	 */
	public function getCurl()
	{
		return $this->Curl;
	}

	/**
	 * @return $this
	 * @throws ModuleException
	 * @throws \Thymian\framework\exceptions\CoreException
	 */
	public function createTemplatesSymlinks(Content $Content)
	{
		$payload = http_build_query(
			array(
				'json'          => json_encode($Content->getTemplatesSymlinks()),
				'playlist_id'   => $Content->getPlaylistId()));

		$url = $this->getConfig()->getConfigValue('_template_content_server_url', 'templates');

		$this->getCurl()
			 ->setUrl($url . 'create_symlink.php')
			 ->setPostFields($payload)
			 ->curlExec();

		if ($this->getCurl()->getHttpCode() != 200)
		{
			$message    = $this->getCurl()->getErrorMessage() . ', Headers:' .  $this->getCurl()->getResponseHeaders() . ', Body: ' . $this->getCurl()->getResponseBody() . ', HTTP: ' . $this->getCurl()->getHttpCode();
			$code       = $this->getCurl()->getErrorNumber();
			throw new ModuleException($this->getModuleName(), $message, $code);
		}

		return $this;
	}

	/**
	 * @param Content $Content
	 * @return $this
	 * @throws ModuleException
	 * @throws \Thymian\framework\exceptions\CoreException
	 */
	public function createMediaSymlinks(Content $Content)
	{
		$payload = http_build_query(
			array(
				'json'          => json_encode($Content->getMediaSymlinks()),
				'playlist_id'   => $Content->getPlaylistId()
			));

		$url = $this->getConfig()->getConfigValue('_content_server_url', 'mediapool');

		$this->getCurl()
			 ->setUrl($url . 'setcontent.php')
			 ->setPostFields($payload)
			 ->curlExec();

		if ($this->getCurl()->getHttpCode() != 200)
		{
			$message    = $this->getCurl()->getErrorMessage() . ', Headers:' .  $this->getCurl()->getResponseHeaders() . ', Body: ' . $this->getCurl()->getResponseBody() . ', HTTP: ' . $this->getCurl()->getHttpCode();
			$code       = $this->getCurl()->getErrorNumber();
			throw new ModuleException($this->getModuleName(), $message, $code);
		}

		return $this;
	}

	/**
	 * @param   int $playlist_id
	 * @return  $this
	 * @throws ModuleException
	 * @throws \Thymian\framework\exceptions\CoreException
	 */
	public function export($playlist_id)
	{
		$payload = http_build_query(array('smil_playlist_id' => $playlist_id));

		$url = $this->getConfig()->getConfigValue('_playlists_server_url', 'smil_playlists');

		$this->getCurl()
			 ->setUrl($url . 'create_playlist.php')
			 ->setPostFields($payload)
			 ->curlExec(false);

		if ($this->getCurl()->getHttpCode() != 200)
		{
			$message    = $this->getCurl()->getErrorMessage() . ', Headers:' .  $this->getCurl()->getResponseHeaders() . ', Body: ' . $this->getCurl()->getResponseBody() . ', HTTP: ' . $this->getCurl()->getHttpCode();
			$code       = $this->getCurl()->getErrorNumber();
			throw new ModuleException($this->getModuleName(), $message, $code);
		}
		return $this;
	}
}