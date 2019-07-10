<?php

namespace BioSounds\Controller;

use BioSounds\Entity\UserPermission;
use BioSounds\Entity\User;
use BioSounds\Entity\Permission;
use BioSounds\Utils\Auth;

class UserPermissionController extends BaseController
{
    /**
     * @param int $userId
     * @return false|string
     * @throws \Exception
     */
	public function show(int $userId)
    {
        if (!Auth::isUserAdmin()){
            throw new \Exception('User has no access to the administration.');
        }

        $listCollections = (new UserPermission())->getColPermissionsByUser($userId);
        $permission = new Permission();

        return json_encode([
            'errorCode' => 0,
            'data' => $this->twig->render('administration/userPermission.html.twig', [
                'collections' => $listCollections,
                'username' => (new User())->getUserName($userId),
                'userId' => $userId,
                'viewId' => $permission->getViewId(),
                'reviewId' => $permission->getReviewId(),
            ]),
        ]);
	}

    /**
     * @return string
     * @throws \Exception
     */
	public function save(): string
    {
        if (!Auth::isUserAdmin()) {
            throw new \Exception('User has no access to the administration.');
        }

        $userProvider = new UserPermission();
        foreach (json_decode($_POST['rows']) as $row) {
            $row = filter_var_array((array) $row);

            $userProvider->delete($row[UserPermission::USER], $row[UserPermission::COLLECTION]);

            if ($row[UserPermission::PERMISSION] > 0) {
                $userProvider->insert($row);
            }
        }
        return json_encode([
            'errorCode' => 0,
        ]);
	}
}
