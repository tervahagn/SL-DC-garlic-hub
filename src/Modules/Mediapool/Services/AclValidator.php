<?php
namespace App\Modules\Mediapool\Services;


use App\Framework\Core\Acl\AbstractAclValidator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;

class AclValidator extends AbstractAclValidator
{
	/**
	 * @throws ModuleException
	 * @throws CoreException
	 */
	public function checkDirectoryPermissions(int $UID, array $directory): array
	{
		if (!array_key_exists('UID', $directory))
			throw new ModuleException($this->moduleName, 'Missing UID in media directory data struct.');

		$permissions = ['create' => false, 'read' => false, 'edit' => false];

		// Check for module admin or directory owner
		if ($this->isModuleAdmin($UID) || $directory['UID'] == $UID)
			return ['create' => true, 'read' => true, 'edit' => true];

		// Edge Edition will not move further as there is not subadmin
		if ($this->isSubAdmin($UID) && $this->hasSubAdminAccessOnCompany($UID, $directory['company_id']))
			$permissions = ['create' => true, 'read' => true, 'edit' => true];

		if ($this->isEditor($UID) && $this->hasEditorAccessOnUnit($UID, $directory['node_id']))
			$permissions = ['create' => true, 'read' => true, 'edit' => false];

		if ($this->isViewer($UID) && $this->hasViewerAccessOnUnit($UID, $directory['node_id']))
			$permissions['read'] = true;

		if ($directory['is_public'] === 1)
			$permissions['read'] = true;

		// Only moduleadmin can edit root directories
		if ($directory['parent_id'] == 0 && !$this->isModuleAdmin($UID))
			$permissions['edit'] = false;

		return $permissions;
	}

}