<?php
namespace App\Modules\Mediapool\Services;


use App\Framework\Core\Acl\AbstractAclValidator;
use App\Framework\Core\Acl\AclHelper;
use App\Framework\Exceptions\CoreException;
use App\Framework\Exceptions\ModuleException;
use Doctrine\DBAL\Exception;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;

class AclValidator extends AbstractAclValidator
{
	const int VISIBILITY_PUBLIC = 1;

	public function __construct(AclHelper $aclHelper)
	{
		parent::__construct('mediapool', $aclHelper);
	}

	/**
	 * @param array<string,mixed> $directory
	 * @return array<string,bool|string>
	 * @throws CoreException
	 * @throws ModuleException
	 * @throws Exception
	 * @throws PhpfastcacheSimpleCacheException
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
		if ($this->isSubAdminWithAccessOnCompany($UID, $directory['company_id']))
			$permissions = ['create' => true, 'read' => true, 'edit' => true, 'share' => 'company'];

		if ($this->isEditorWithAccessOnUnit($UID, $directory['node_id']))
			$permissions = ['create' => true, 'read' => true, 'edit' => false, 'share' => ''];

		if ($this->isViewerWithAccessOnUnit($UID, $directory['node_id']))
			$permissions['read'] = true;

		if ($directory['visibility'] === self::VISIBILITY_PUBLIC)
			$permissions['read'] = true;

		// Only moduleadmin can edit root directories
		// nno need to check on Moduleadmin, as this happens above with:
		// if ($this->isModuleAdmin($UID) || $directory['UID'] == $UID)
		// module admin will never reach this line
		if ($directory['parent_id'] === 0)
			$permissions['edit'] = false;

		return $permissions;
	}

}