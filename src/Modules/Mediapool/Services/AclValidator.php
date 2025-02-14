<?php
namespace App\Modules\Mediapool\Services;


use App\Framework\Core\Acl\AbstractAclValidator;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use League\Flysystem\Visibility;

class AclValidator extends AbstractAclValidator
{

	const VISIBILITY_PUBLIC = 1;
	/**
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws \Doctrine\DBAL\Exception
	 * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
	 */
	public function checkDirectoryPermissions(int $UID, array $directory): array
	{
		if (!array_key_exists('UID', $directory))
			throw new ModuleException($this->moduleName, 'Missing UID in media directory data struct.');

		$permissions = ['create' => false, 'read' => false, 'edit' => false, 'share' => ''];

		// Check for module admin or directory owner
		if ($this->isModuleAdmin($UID) || $directory['UID'] == $UID)
			return ['create' => true, 'read' => true, 'edit' => true, 'share' => 'global'];

		// Edge Edition will not move further as there is not subadmin
		if ($this->isSubAdmin($UID) && $this->hasSubAdminAccessOnCompany($UID, $directory['company_id']))
			$permissions = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'company'];

		if ($this->isEditor($UID) && $this->hasEditorAccessOnUnit($UID, $directory['node_id']))
			$permissions = ['create' => true, 'read' => true, 'edit' => false, 'share' => ''];

		if ($this->isViewer($UID) && $this->hasViewerAccessOnUnit($UID, $directory['node_id']))
			$permissions['read'] = true;

		if ($directory['visibility'] === self::VISIBILITY_PUBLIC)
			$permissions['read'] = true;

		// Only moduleadmin can edit root directories
		if ($directory['parent_id'] == 0 && !$this->isModuleAdmin($UID))
			$permissions['edit'] = false;

		return $permissions;
	}

}