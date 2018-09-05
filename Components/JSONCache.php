<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.09.18
 * Time: 16:58
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;


use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

class JSONCache {
    const CACHE_PATH = __DIR__
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . '..'
    . DIRECTORY_SEPARATOR . 'files'
    . DIRECTORY_SEPARATOR . 'FatchipShopware2Afterbuy'
    . DIRECTORY_SEPARATOR . 'cache';

    /** @var Filesystem $fileSystem */
    protected $fileSystem;

    public function __construct($afterbuyPartnerId) {
        $adapter = new Local(
            self::CACHE_PATH . DIRECTORY_SEPARATOR . $afterbuyPartnerId
        );

        $this->fileSystem = new Filesystem($adapter);
    }

    /**
     * Caches the given array to .json file in MediaManager. The array must have
     * the following format:
     *
     * [
     *   AfterBuyID => dataArray
     * ]
     *
     * The dataArray will be converted to json and dumped to a file
     * /media/$path/AfterBuyID.json
     *
     * @param array  $data
     * @param string $directory
     */
    public function cacheData($data, $directory) {
        foreach ($data as $id => $value) {
            $fileName = $id . '.json';

            $this->fileSystem->put(
                trim($directory, DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR . $fileName,
                json_encode($value)
            );
        }
    }

    public function getLatestCacheDate($directory) {
        $list = $this->fileSystem->listContents(
            trim($directory, DIRECTORY_SEPARATOR)
        );

        $latestCacheDate = 0;

        foreach ($list as $file) {
            $latestCacheDate = max($latestCacheDate, $file['timestamp']);
        }

        // echo date('D, d M Y H:i:s', $latestCacheDate);

        return $latestCacheDate;
    }

    public function deleteCache($directory = '') {
        $files = $this->fileSystem->listContents(
            trim(DIRECTORY_SEPARATOR, $directory)
        );

        foreach ($files as $file) {
            try {
                $this->fileSystem->delete($file);
            } catch (FileNotFoundException $e) {
            }
        }
    }
}