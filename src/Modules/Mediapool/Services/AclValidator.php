<?php
namespace App\Modules\Mediapool\Services;


use App\Framework\Core\Acl\AbstractAclValidator;
use App\Framework\Core\Config\Config;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\FrameworkException;
use App\Framework\Exceptions\ModuleException;
use App\Framework\User\Enterprise\UserVipRepository;
use App\Framework\User\UserEntity;

class AclValidator extends AbstractAclValidator
{
	/**
	 * @throws ModuleException
	 * @throws CoreException
	 */
	public function checkDirectoryPermissions(array $directory): array
	{
		if (!isset($directory['UID']))
			throw new ModuleException($this->moduleName, 'Missing UID in media directory data struct.');

		$permissions = ['create' => false, 'read' => false, 'edit' => false];

		// Check for module admin or directory owner
		if ($this->isModuleAdmin() || $directory['UID'] == $this->userEntity->getMain()['UID'])
			return ['create' => true, 'read' => true, 'edit' => true];

		if ($this->isSubAdmin() && $this->hasSubAdminAccess((int)$directory['company_id']))
			$permissions = ['create' => true, 'read' => true, 'edit' => true];

		if ($this->isEditor() && $this->hasEditorAccess((int)$directory['node_id']))
			$permissions = ['create' => true, 'read' => true, 'edit' => false];

		if ($this->isViewer() && $this->hasViewerAccess((int)$directory['node_id']))
			$permissions['read'] = true;

		if ($directory['is_public'] === 1)
			$permissions['read'] = true;

		// Only moduleadmin can edit root directories
		if ($directory['parent_id'] == 0 && !$this->isModuleAdmin())
			$permissions['edit'] = false;


		return $permissions;
	}

}