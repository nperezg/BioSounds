<?php

namespace BioSounds\Controller\Administration;

use BioSounds\Controller\BaseController;
use BioSounds\Entity\Collection;
use BioSounds\Exception\ForbiddenException;
use BioSounds\Provider\CollectionProvider;
use BioSounds\Utils\Auth;

class CollectionController extends BaseController
{
    const SECTION_TITLE = 'Collections';

    /**
     * @return string
     * @throws \Exception
     */
    public function show(int $page = 1)
    {
        if (!Auth::isUserAdmin()) {
            throw new ForbiddenException();
        }

        $collProvider = new CollectionProvider();

        $collNum = $collProvider->countCollections();
        $pages = $collNum > 0 ? ceil($collNum / self::ITEMS_PAGE) : 1;

        return $this->twig->render('administration/collections.html.twig', [
            'collections' => $collProvider->getCollectionPages(
                $this::ITEMS_PAGE,
                $this::ITEMS_PAGE * ($page - 1)
            ),
            // 'collections' => (new (CollectionProvider))->getList("name"),
            'currentPage' => ($page > $pages) ?: $page,
            'pages' => $pages
        ]);
    }


    /**
     * @return false|string
     * @throws \Exception
     */
    public function save()
    {
        $collProvider = new Collection();

        if (!Auth::isUserAdmin()) {
            throw new ForbiddenException();
        }

        $data = [];

        foreach ($_POST as $key => $value) {
            if (strrpos($key, '_')) {
                $type = substr($key, strrpos($key, '_') + 1, strlen($key));
                $key = substr($key, 0, strrpos($key, '_'));
            }
            $data[$key] = filter_var($value, FILTER_SANITIZE_STRING);
        }
        $data['user_id']=$_SESSION['user_id'];
        if (isset($data['collId'])) {
            $collProvider->updateColl($data);
            return json_encode([
                'errorCode' => 0,
                'message' => 'Collection updated successfully.'
            ]);
        } else if ($collProvider->insertColl($data) > 0) {
            return json_encode([
                'errorCode' => 0,
                'message' => 'Collection created successfully.',
            ]);
        }
    }

    /**
     * @return false|string
     * @throws \Exception
     */
    public function editCollection()
    {
        if (!Auth::isUserAdmin()) {
            throw new ForbiddenException();
        }

        $collId = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
        return json_encode([
            'errorCode' => 0,
            'data' => $this->twig->render('administration/collEdit.html.twig', [
                'collId' => $collId,
            ]),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function export()
    {
        if (!Auth::isUserAdmin()) {
            throw new ForbiddenException();
        }

        $file_name = "collections.csv";
        $fp = fopen('php://output', 'w');
        header('Content-Type: application/octet-stream;charset=utf-8');
        header('Accept-Ranges:bytes');
        header('Content-Disposition: attachment; filename=' . $file_name);

        $collList = (new CollectionProvider())->getList();

        foreach ($collList as $collItem) {
            $colArray = array($collItem->getId(), $collItem->getName(), $collItem->getAuthor(), $collItem->getDoi(), $collItem->getNote(), $collItem->getProject());
            $colAls[] = $colArray;
        }

        foreach ($colAls as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);
        exit();
    }
}
