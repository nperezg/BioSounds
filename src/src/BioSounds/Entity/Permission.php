<?php

namespace BioSounds\Entity;

use BioSounds\Provider\BaseProvider;

class Permission extends BaseProvider
{
	const VIEW = 'View';
	const REVIEW = 'Review';

    /**
     * @param int $permissionId
     * @return bool
     * @throws \Exception
     */
	public function isReviewPermission(int $permissionId): bool
    {
		$this->database->prepareQuery('SELECT name FROM permission WHERE permission_id = :permissionId');
		if (empty($result = $this->database->executeSelect([':permissionId' => $permissionId]))) {
            return false;
        }
		
		return $result[0]['name'] == self::REVIEW ? true : false;
	}

    /**
     * @param int $permissionId
     * @return bool
     * @throws \Exception
     */
	public function isViewPermission(int $permissionId): bool
    {
		$this->database->prepareQuery('SELECT name FROM permission WHERE permission_id = :permissionId');
		if (empty($result = $this->database->executeSelect([':permissionId' => $permissionId]))) {
            return false;
        }
		
		return $result[0]['name'] == self::VIEW ? true : false;
	}

    /**
     * @return int|null
     * @throws \Exception
     */
	public function getViewId(): ?int
    {
		$this->database->prepareQuery('SELECT permission_id FROM permission WHERE name = :name');
		if (empty($result = $this->database->executeSelect([':name' => self::VIEW]))) {
            return null;
        }
		
		return $result[0]['permission_id'];
	}

    /**
     * @return int|null
     * @throws \Exception
     */
	public function getReviewId(): ?int
    {
		$this->database->prepareQuery('SELECT permission_id FROM permission WHERE name = :name');
		if (empty($result = $this->database->executeSelect([':name' => self::REVIEW]))) {
		    return null;
        }

		return $result[0]['permission_id'];
	}	
}
