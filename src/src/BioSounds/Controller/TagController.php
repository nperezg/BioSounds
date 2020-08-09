<?php

namespace BioSounds\Controller;

use BioSounds\Entity\Tag;
use BioSounds\Entity\Permission;
use BioSounds\Exception\EmptyIdException;
use BioSounds\Exception\ForbiddenException;
use BioSounds\Exception\NotAuthenticatedException;
use BioSounds\Provider\TagProvider;
use BioSounds\Utils\Auth;

class TagController extends BaseController
{
    /**
     * @param int $tagId
     * @return string
     * @throws \Exception
     */
    public function showCallDistance(int $tagId)
    {
        if (!Auth::isUserLogged()) {
            throw new NotAuthenticatedException();
        }

	    return json_encode([
	        'errorCode' => 0,
            'data' => $this->twig->render('tag/callEstimation.html.twig', [
                'tagId' => $tagId,
            ]),
        ]);
	}

    /**
     * @return false|string
     * @throws \Exception
     */
	public function create()
    {
        if (!Auth::isUserLogged()) {
            throw new NotAuthenticatedException();
        }

        if (!isset($_POST["t_min"]) || !isset($_POST["t_max"]) || !isset($_POST["f_min"]) || !isset($_POST["f_max"])) {
            throw new \Exception('Data not set.');
        }

        $tag = (new Tag())
            ->setRecording(filter_var($_POST["recording_id"], FILTER_SANITIZE_NUMBER_INT))
            ->setMinTime(filter_var($_POST["t_min"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION))
            ->setMaxTime(filter_var($_POST["t_max"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION))
            ->setMinFrequency(filter_var($_POST["f_min"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION))
            ->setMaxFrequency(filter_var($_POST["f_max"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION))
            ->setUserName(Auth::getUserName())
            ->setUser(Auth::getUserLoggedID());

        return json_encode([
            'errorCode' => 0,
            'data' => $this->twig->render('tag/tag.html.twig', [
                'tag' => $tag,
                'displayDeleteButton' => 'hidden',
                'recordingName' => isset($_POST['recording_name']) ? $_POST['recording_name'] : null,
            ]),
        ]);
	}

    /**
     * @param int $tagId
     * @return false|string
     * @throws \Exception
     */
	public function edit(int $tagId)
    {
        if (!Auth::isUserLogged()) {
            throw new NotAuthenticatedException();
        }

        if (empty($tagId)) {
            throw new EmptyIdException();
        }

        if (!Auth::isUserAdmin()
            && (!isset($_SESSION["user_col_permission"])
                || empty($_SESSION["user_col_permission"]))
        ){
            throw new ForbiddenException();
        }

        $tag = (new TagProvider())->get($tagId);

        /* TAG USER CONTROL */

        $isUserTagOwner = $tag->getUser() == Auth::getUserLoggedID();
        $isReviewGranted = Auth::isUserAdmin();
        $displaySaveButton = Auth::isUserAdmin() || $isUserTagOwner ? '' : 'hidden';

        if (!Auth::isUserAdmin() && !$isUserTagOwner) {
            $permissionProvider = new Permission();
            $isReviewGranted = $permissionProvider->isReviewPermission($_SESSION["user_col_permission"]);
            $isViewGranted = $permissionProvider->isViewPermission($_SESSION["user_col_permission"]);

            if (!$isReviewGranted && !$isViewGranted) {
                throw new ForbiddenException();
            }

            $displaySaveButton = $isReviewGranted ? '' : 'hidden';
         }

        /**********************/

        return json_encode([
            'errorCode' => 0,
            'data' => $this->twig->render('tag/tag.html.twig', [
                'tag' => $tag,
                'recordingName' => isset($_POST['recording_name']) ? $_POST['recording_name'] : null,
                'displayDeleteButton' => Auth::isUserAdmin() || $isUserTagOwner ? '' : 'hidden',
                'displaySaveButton' => $displaySaveButton,
                'disableTagForm' => !Auth::isUserAdmin() && !$isUserTagOwner,
                'reviewPanel' => $isReviewGranted ? (new TagReviewController($this->twig))->show($tagId) : '',
            ]),
        ]);
    }

    /**
     * @return false|string
     * @throws \Exception
     */
	public function save()
    {
        if (!Auth::isUserLogged()) {
            throw new NotAuthenticatedException();
        }

        $data[Tag::UNCERTAIN] = 0;
        $data[Tag::REFERENCE_CALL] = 0;
        $data[Tag::DISTANCE_NOT_ESTIMABLE] = 0;

        foreach($_POST as $key => $value) {
            $data[$key] = htmlentities(strip_tags($value), ENT_QUOTES);

            if ($key === Tag::CALL_DISTANCE && empty($value)) {
                $data[$key] = null;
            }
        }

        if (isset($data[Tag::ID]) && !empty($data[Tag::ID])) {
            (new TagProvider())->update($data);
            return json_encode([
                'errorCode' => 0,
                'message' => 'Tag updated successfully.',
            ]);
        }

        $data[Tag::USER_ID] = Auth::getUserLoggedID();
        if ($data[Tag::DISTANCE_NOT_ESTIMABLE] != 1) {
            $data[Tag::DISTANCE_NOT_ESTIMABLE] = null;
        }

        unset($data[Tag::ID]);

        return json_encode([
            'errorCode' => 0,
            'message' => 'Tag created successfully.',
            'tagId' => (new TagProvider())->insert($data),
        ]);
	}

    /**
     * @param int $tagId
     * @return array|int
     * @throws \Exception
     */
	public function delete(int $tagId)
    {
        if (!Auth::isUserAdmin() && (new TagProvider())->get($tagId)->getUser() != Auth::getUserLoggedID()) {
            throw new \Exception('The user doesn\'t have permissions to delete this tag.');
        }

        (new TagProvider())->delete($tagId);

        return json_encode([
            'errorCode' => 0,
            'message' => 'Tag deleted successfully.',
        ]);
	}
}
