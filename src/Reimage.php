<?php
declare(strict_types=1);

namespace Reimage;

use Reimage\Exceptions\ReimageException;
use Reimage\FileSystemAdapters\FileSystemInterface;
use Reimage\FileSystemAdapters\Local;
use Reimage\ImageAdapters\ImageInterface;
use Reimage\ImageAdapters\Intervention;
use Reimage\PathMapperAdapters\BasicMapper;
use Reimage\PathMapperAdapters\PathMapperInterface;

class Reimage
{
    public const WIDTH = 'w';
    public const HEIGHT = 'h';
    public const SIGN = 's';

    private const ALL_PARAMS = [
        self::WIDTH,
        self::HEIGHT,
        self::SIGN,
    ];

    private const HASH_LENGHT = 6;
    private const CDN_IMAGE_SECRET = '1234';

    /**
     * @param string $sourcePath
     * @param array<string,string|int> $rawParams
     * @return string
     * @throws ReimageException
     */
    function createUrl(string $sourcePath, array $rawParams): string
    {
        $sourcePath = Utils::unifyPath($sourcePath);

        $fullParams = $this->computeAllParams($rawParams);

        $publicPath = $this->generatePublicPath($sourcePath, $fullParams);

        $query = $rawParams + [
                self::SIGN => $this->generateSignature($publicPath, $fullParams),
            ];

        return $publicPath . '?' . http_build_query($query);
    }

    /**
     * @param array<string,string|int> $params
     * @return array<string,string|int>
     */
    private function computeAllParams(array $params): array
    {
        $allParams = [];

        foreach ($params as $index => $param) {
            if (in_array($index, self::ALL_PARAMS)) {
                $allParams[$index] = $param;
            }
        }

        return $allParams;
    }

    /**
     * @param string $sourcePath
     * @param array<string,string|int> $params
     * @return string
     * @throws ReimageException
     */
    private function generatePublicPath(string $sourcePath, array $params): string
    {
        $mapper = $this->getMapperAdapter();
        $publicPath = $mapper->remapSourceToPublic($sourcePath);

        $hashedPart = $this->generateFileHash($sourcePath, $params);

        $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $newPublicPath = preg_replace('/(\.' . preg_quote($ext, '/') . ')$/', '_' . $hashedPart . '.' . $ext, $publicPath, 1);

        if ($newPublicPath === null) {
            throw new ReimageException('preg_replace error occurred');
        }

        return $newPublicPath;
    }

    private function generateSourcePath(string $publicPath): string
    {
        $ext = pathinfo($publicPath, PATHINFO_EXTENSION);
        $publicWithoutHash = preg_replace('/_[a-f0-9]{' . self::HASH_LENGHT . '}\.' . preg_quote($ext, '/') . '/', '.' . $ext, $publicPath);
        if ($publicWithoutHash === null) {
            throw new ReimageException('preg_replace error occurred');
        }

        $mapper = $this->getMapperAdapter();
        $sourcePath = $mapper->remapPublicToSource($publicWithoutHash);

        return $sourcePath;
    }

    private function generateCachePath(string $publicPath): string
    {
        $mapper = $this->getMapperAdapter();

        return $mapper->remapPublicToCache($publicPath);
    }

    /**
     * @param string $sourcePath
     * @param array<string,string|int> $params
     * @return string
     */
    private function generateSignature(string $sourcePath, array $params): string
    {
        unset($params[self::SIGN]);
        ksort($params);

        return md5(self::CDN_IMAGE_SECRET . '|' . basename($sourcePath) . '|' . http_build_query($params));
    }

    /**
     * @param string $path
     * @param array<string,string|int> $params
     * @return string
     */
    private function generateFileHash(string $path, array $params): string
    {
        unset($params[self::SIGN]);
        ksort($params);

        $stringToHash = basename($path) . http_build_query($params);
        return substr(md5($stringToHash), 0, self::HASH_LENGHT);
    }

    private function getMapperAdapter(): PathMapperInterface
    {
        $testDir = dirname(__FILE__, 2) . '/tests';

        return new BasicMapper([
            [
                'source' => $testDir . '/TestImages',
                'cache' => $testDir . '/Temp',
                'public' => '/cdn',
            ],
        ]);
    }

    /**
     * @param string $publicPath
     * @param array<string,string|int> $queryParams
     * @return bool
     * @throws ReimageException
     */
    public function createImage(string $publicPath, array $queryParams): bool
    {
        if (!$this->isValidSignature($publicPath, $queryParams)) {
            throw new ReimageException('Invalid signature');
        }

        $sourcePath = $this->generateSourcePath($publicPath);
        $cachePath = $this->generateCachePath($publicPath);

        $image = $this->doImageCommands($sourcePath, $queryParams);
        $this->getFileSystemAdapter()->saveContent($cachePath, $image->getImageString());

        return true;
    }

    /**
     * @param string $publicPath
     * @param array<string,string|int> $params
     * @return bool
     */
    private function isValidSignature(string $publicPath, array $params): bool
    {
        $sign = $params[self::SIGN] ?? null;
        if (empty($sign)) {
            return false;
        }

        unset($params[self::SIGN]);
        // sort?
        // clean?

        $expectedSign = $this->generateSignature($publicPath, $params);

        return $sign === $expectedSign;
    }

    /**
     * @param string $fullPath
     * @param array<string,string|int> $params
     * @return ImageInterface
     */
    private function doImageCommands(string $fullPath, array $params): ImageInterface
    {
        $imageClass = $this->getImageAdapter();
        $imageClass->loadImage($fullPath);

        $width = $params[self::WIDTH] ?? null;
        $height = $params[self::HEIGHT] ?? null;
        if ($width || $height) {
            $width = $width !== null ? (int)$width : null;
            $height = $height !== null ? (int)$height : null;

            $imageClass->resize($width, $height);
        }

        //todo more operations

        return $imageClass;
    }


    private function getImageAdapter(): ImageInterface
    {
        return new Intervention();
    }

    private function getFileSystemAdapter(): FileSystemInterface
    {
        return new Local();
    }
}
