<?php
namespace App\Framework\Core\Acl;

use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\User\Enterprise\UserVipRepository;
use App\Framework\User\UserEntity;

/**
 * Class AbstractAclValidator
 *
 * Includes a cache mechanismen to prevent repeated
 * access to the database
 *
 */
abstract class AbstractAclValidator
{
	const int USER_STATUS_DEFAULT_USER 	= 1;
	const int USER_STATUS_ADMIN_USER	= 2;
	const string CONFIG_ACL_SECTION_GLOBAL     = 'GlobalACLs';
	const string CONFIG_ACL_SECTION_SUB_ADMIN  = 'LocalSubAdminACLs';
	const string CONFIG_ACL_SECTION_EDITOR     = 'LocalEditorACLs';
	const string CONFIG_ACL_SECTION_VIEWER     = 'LocalViewerACLs';
	protected string $moduleName;
	protected UserEntity $userEntity;
	protected UserVipRepository $userVipRepository;

	protected Config $config;

	private bool $is_subadmin_editable = false;
	private bool $is_subadmin_editable_cached = false;
	private bool $is_editor_editable = false;
	private bool $is_editor_editable_cached = false;
	private bool $is_editor_usable = false;
	private bool $is_editor_usable_cached = false;

	public function __construct(string $module, UserEntity $user, UserVipRepository $userVip, Config $config)
	{
		$this->moduleName        = $module;
		$this->userEntity        = $user;
		$this->userVipRepository = $userVip;
		$this->config            = $config;
	}

	abstract public function getAclNameModuleAdmin();
	abstract public function getAclNameSubAdmin();
	abstract public function getAclNameEditor();
	abstract public function getSubAdminUserModuleName();
	abstract public function getLocalSubAdminAccessName();
	abstract public function getEditorUserModuleName();
	abstract public function getLocalEditorUserUseName();
	abstract public function getLocalEditorUserEditName();

	public function isUserModuleAdmin(): bool
	{
		return $this->validateAcl($this->getConfig()->getConfigValue($this->getAclNameModuleAdmin(), $this->getModuleName(), self::CONFIG_ACL_SECTION_GLOBAL));
	}

	/**
	 * @throws CoreException
	 */
	public function isUserSubAdmin(): bool
	{
		return $this->validateAcl($this->getConfig()->getConfigValue($this->getAclNameSubAdmin(), $this->getModuleName(), self::CONFIG_ACL_SECTION_GLOBAL));
	}

	/**
	 * @throws CoreException
	 */
	public function isUserEditor(): bool
	{
		return $this->validateAcl($this->getConfig()->getConfigValue($this->getAclNameEditor(), $this->getModuleName(), self::CONFIG_ACL_SECTION_GLOBAL));
	}

	/**
	 * @throws CoreException
	 */
	public function isUserContentEditableForSubAdmin($owner_uid): bool
	{
		if (empty($owner_uid) || !$this->isUserSubAdmin())
			return false;

		if ($this->is_subadmin_editable_cached)
			return $this->is_subadmin_editable;

		$local_acl	= $this->getUserVipRepository()->findOneAclByUIDModuleAndDataNum(
			$this->userEntity->getMain()['UID'],
			$this->getSubAdminUserModuleName(),
			$this->getUserEntity()->getUserMainModel()->findCompanyIdByUID($owner_uid)
		);

		$edit_access = $this->getConfig()->getConfigValue(
			$this->getLocalSubAdminAccessName(),
			$this->getModuleName(),
			self::CONFIG_ACL_SECTION_SUB_ADMIN
		);

		$this->is_subadmin_editable_cached = true;
		$this->is_subadmin_editable = (($local_acl & $edit_access) > 0);

		return $this->is_subadmin_editable;
	}

	/**
	 * @throws CoreException
	 */
	public function isUnitEditableForEditor($unit_id): bool
	{
		if (empty($unit_id) || !$this->isUserEditor())
			return false;

		if ($this->is_editor_editable_cached)
			return $this->is_editor_editable;

		$local_acl	= $this->getUserVipRepository()->findOneAclByUIDModuleAndDataNum(
			$this->userEntity->getMain()['UID'],
			$this->getEditorUserModuleName(),
			$unit_id
		);

		$edit_access = $this->getConfig()->getConfigValue(
			$this->getLocalEditorUserEditName(),
			$this->getModuleName(),
			self::CONFIG_ACL_SECTION_EDITOR
		);

		$this->is_editor_editable_cached = true;
		$this->is_editor_editable = (($local_acl & $edit_access) > 0);

		return $this->is_editor_editable;
	}

	public function isUnitUsableForEditor($unit_id): bool
	{
		if (empty($unit_id))
			return false;

		if ($this->is_editor_usable_cached)
			return $this->is_editor_usable;

		$local_acl	= $this->getUserVipRepository()->findOneAclByUIDModuleAndDataNum(
			$this->userEntity->getMain()['UID'],
			$this->getEditorUserModuleName(),
			$unit_id
		);

		$edit_access = $this->getConfig()->getConfigValue(
			$this->getLocalEditorUserUseName(),
			$this->getModuleName(),
			self::CONFIG_ACL_SECTION_EDITOR
		);

		$this->is_editor_usable_cached = true;
		$this->is_editor_usable = (($local_acl & $edit_access) > 0);

		return $this->is_editor_usable;
	}

	public function determineCompaniesForSubAdmin(): array
	{
		$vips = $this->getUserVipRepository()->findAllActiveDataNumsByUIDModule(
			$this->userEntity->getMain()['UID'],
			$this->getSubAdminUserModuleName()
		);

		return array_column($vips, 'data_num');
	}

	public function filterLocalAcl(array $acls, int $access): array
	{
		return array_filter($acls, function($var) use ($access) { return ($var['acl'] & $access); });
	}

	public function getUserEntity(): UserEntity
	{
		return $this->userEntity;
	}

	public function getUserVipRepository(): UserVipRepository
	{
		return $this->userVipRepository;
	}

	protected function getModuleName(): string
	{
		return $this->moduleName;
	}

	protected function getConfig(): Config
	{
		return $this->config;
	}


	protected function validateAcl(string $acl_constant): bool
	{
		return ($this->userEntity->getAcl()[$this->moduleName] === $acl_constant);
	}
}