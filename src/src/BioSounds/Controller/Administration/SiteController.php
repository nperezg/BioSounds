<?php

namespace BioSounds\Controller\Administration;

use BioSounds\Controller\BaseController;
use BioSounds\Entity\Site;
use BioSounds\Entity\User;
use BioSounds\Exception\ForbiddenException;
use BioSounds\Provider\SiteProvider;
use BioSounds\Utils\Auth;


class SiteController extends BaseController
{
    const SECTION_TITLE = 'Site';

    /**
     * @return false|string
     * @throws \Exception
     */
    public function show()
    {
        if (!Auth::isUserAdmin()) {
            throw new ForbiddenException();
        }
        // echo Utils::getSetting('license');

        return $this->twig->render('administration/sites.html.twig', [
            'siteList' => (new SiteProvider())->getListOrderById(),
            'userList' => (new User())->getAllUsers(),
        ]);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        $siteEnt = new Site();

        if (!Auth::isUserAdmin()) {
            throw new ForbiddenException();
        }

        $data = [];
        $sitePdoValue = '';
        foreach ($_POST as $key => $value) {
            if (strrpos($key, '_')) {
                $type = substr($key, strrpos($key, '_') + 1, strlen($key));
                $key = substr($key, 0, strrpos($key, '_'));
            }
            $sitePdoValue = $value;
            if (empty($value)) {
                $sitePdoValue = '';
            }

            switch ($key) {
                case 'user':
                    $data['user_id'] =  filter_var($sitePdoValue, FILTER_SANITIZE_NUMBER_INT);
                    break;
                case 'longitude':
                    $data['longitude_WGS84_dd_dddd'] =  filter_var($sitePdoValue, FILTER_SANITIZE_NUMBER_INT);
                    break;
                case 'latitude':
                    $data['latitude_WGS84_dd_dddd'] =  filter_var($sitePdoValue, FILTER_SANITIZE_NUMBER_INT);
                    break;
                default:
                    $data[$key] = filter_var($sitePdoValue, FILTER_SANITIZE_STRING);
            }
        }

        if (isset($data['steId'])) {
            $siteEnt->update($data);
            return json_encode([
                'errorCode' => 0,
                'message' => 'Site updated successfully.',
            ]);
        } else {
            $data['creation_date_time'] = date('Y-m-d H:i:s', time());

            if ($siteEnt->insert($data) > 0) {
                return json_encode([
                    'errorCode' => 0,
                    'message' => 'Site created successfully.',
                ]);
            }
        }
    }


    /**
     * @param int $id
     * @return false|string
     * @throws \Exception
     */
    public function delete(int $id)
    {
        if (!Auth::isUserAdmin()) {
            throw new ForbiddenException();
        }

        if (empty($id)) {
            throw new \Exception(ERROR_EMPTY_ID);
        }

        $siteProvider = new SiteProvider();
        $siteProvider->delete($id);

        return json_encode([
            'errorCode' => 0,
            'message' => 'Site deleted successfully.',
        ]);
    }
}
