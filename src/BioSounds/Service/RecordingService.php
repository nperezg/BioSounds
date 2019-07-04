<?php

namespace BioSounds\Service;

use BioSounds\Presenter\RecordingListPresenter;
use BioSounds\Presenter\RecordingPresenter;
use BioSounds\Provider\RecordingProvider;

class RecordingService
{
    const RECORDING_PATH = 'sounds/sounds/%s/%s/%s';
    const IMAGE_PATH = 'sounds/images/%s/%s/%s';

    private $imageService;

    /**
     * RecordingService constructor.
     */
    public function __construct()
    {
        $this->imageService = new ImageService();
    }

    /**
     * @param int $colId
     * @param int $limit
     * @param int $offSet
     * @param array $filter
     * @return RecordingListPresenter[]
     * @throws \Exception
     */
    public function getListWithImages(int $colId, int $limit, int $offSet, array $filter): array
    {
        $result = [];
        $list = (new RecordingProvider())->getListByCollection($colId, $limit, $offSet, $filter);

        $spectrogramService = new SpectrogramService();

        foreach($list as $item) {
            $recordingListPresenter = new RecordingListPresenter();
            $recordingListPresenter->setRecording($item);

            $recordingListPresenter->setPlayerImage('assets/images/notready-small.png');
            if (!empty($playerImage = $spectrogramService->getPlayerImage($item->getId()))
                && is_file(  sprintf(
                    self::IMAGE_PATH,
                    $item->getCollection(),
                    $item->getDirectory(),
                    $playerImage->getFilename()
                ))
            ) {
                $recordingListPresenter->setPlayerImage(
                    sprintf(
                        self::IMAGE_PATH,
                        $item->getCollection(),
                        $item->getDirectory(),
                        $playerImage->getFilename()
                    )
                );
            }

            $recordingListPresenter->setSmallImage('assets/images/notready-small.png');
            if (!empty($smallImage = $spectrogramService->getSmallImage($item->getId()))
                && is_file(  sprintf(
                    self::IMAGE_PATH,
                    $item->getCollection(),
                    $item->getDirectory(),
                    $smallImage->getFilename()
                ))
            ) {
                $recordingListPresenter->setSmallImage(
                    sprintf(
                        self::IMAGE_PATH,
                        $item->getCollection(),
                        $item->getDirectory(),
                        $smallImage->getFilename()
                    )
                );
            }

            $playerRecording = sprintf(
                self::RECORDING_PATH,
                $item->getCollection(),
                $item->getDirectory(),
                substr($item->getFileName(), 0, strripos($item->getFileName(), '.')) . '.wav'
            );

            $recordingListPresenter->setPlayerRecording($playerRecording);

            if (!is_file($playerRecording)) {
                $recordingListPresenter->setPlayerRecording(
                    sprintf(
                        self::RECORDING_PATH,
                        $item->getCollection(),
                        $item->getDirectory(),
                        $item->getFileName()
                    )
                );
            }

            $result[] = $recordingListPresenter;
        }
        return $result;
    }

    /**
     * @param string $imagePath
     * @param string $wavFilePath
     * @param int $maxFrequency
     * @param int $channel
     * @param int $minFrequency
     * @throws \Exception
     */
    public function generateSpectrogramImage(
        string $imagePath,
        string $wavFilePath,
        int $maxFrequency,
        int $channel,
        int $minFrequency
    ){
        if (!file_exists($imagePath)) {
            try {
                $this->imageService->generatePlayerImage(
                    $imagePath,
                    $wavFilePath,
                    $maxFrequency,
                    $channel,
                    $minFrequency
                );
            } catch(\Exception $exception) {
                error_log($exception->getMessage());
                throw new \Exception('There was a problem generating the recording spectrogram image.');
            }
        }
    }

    /**
     * @param RecordingPresenter $recordingPresenter
     * @param int $samplingRate
     * @param int $channel
     * @param string $fileName
     */
    public function setViewPort(
        RecordingPresenter $recordingPresenter,
        int $samplingRate,
        int $channel,
        string $fileName
    ){
        $recordingPresenter->setViewPortFilePath($this->imageService->generateViewPort(
            $samplingRate,
            $recordingPresenter->getMinFrequency(),
            $recordingPresenter->getMaxFrequency(),
            $recordingPresenter->getMinTime(),
            $recordingPresenter->getMaxTime(),
            $fileName,
            $channel,
            $recordingPresenter->getDuration(),
            'tmp/'.$_SESSION['random_id'] . '/'
        ));
    }
}
