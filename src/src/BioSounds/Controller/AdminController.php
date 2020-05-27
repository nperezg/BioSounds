<?php

namespace BioSounds\Controller;

use BioSounds\Controller\Administration\UserController;
use BioSounds\Exception\ForbiddenException;
use BioSounds\Utils\Auth;
use BioSounds\Controller\Administration\CollectionController as CollectionController;
use BioSounds\Controller\Administration\SettingController as SettingController;
use BioSounds\Controller\Administration\RecordingController as RecordingController;

class AdminController extends BaseController
{
    /**
     * AdminController constructor.
     * @throws \Exception
     */
    public function create()
    {
		if (!Auth::isUserAdmin()){
			throw new ForbiddenException();
		}

		return $this->settings();
    }

    /**
     * @param string|null $action
     * @return false|string
     * @throws \Exception
     */
	public function settings(?string $action = null)
    {
        if (!empty($action)) {
            return (new SettingController($this->twig))->$action();
        }
		return (new SettingController($this->twig))->show();
	}

    /**
     * @throws \Exception
     */
	public function collections()
    {
        return (new CollectionController($this->twig))->show();
	}

    /**
     * @param string|null $action
     * @return false|string
     * @throws \Exception
     */
	public function users(?string $action = null)
    {
        if (!empty($action)) {
            return (new UserController($this->twig))->$action();
        }
        return (new UserController($this->twig))->create();
	}

    /**
     * @param mixed ...$args
     * @return mixed
     * @throws \Exception
     */
    public function recordings(... $args)
    {
		return (new RecordingController($this->twig))->show(
            empty($args[0]) ? null : $args[0],
            empty($args[1]) ? 1 : $args[1]);
	}

    /**
     * @param string $action
     * @param int|null $id
     * @return mixed
     */
	public function recordingManager(string $action, int $id = null)
    {
        return (new RecordingController($this->twig))->$action($id);
    }
}
