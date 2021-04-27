<?php
declare(strict_types=1);

namespace Reimage;

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
     */
    function createUrl(string $sourcePath, array $rawParams): string
    {
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
     * @throws \Exception
     */
    private function generatePublicPath(string $sourcePath, array $params): string
    {
        unset($params[self::SIGN]);
        ksort($params);

        $mapper = $this->getMapper();
        $publicPath = $mapper->remapSourceToPublic($sourcePath);

        $hashedPart = $this->generateFileHash($sourcePath, $params);

        $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $newPublicPath = preg_replace('/(\.' . preg_quote($ext, '/') . ')$/', '_' . $hashedPart . '.' . $ext, $publicPath, 1);

        if ($newPublicPath === null) {
            throw new \Exception('preg_replace error occurred');
        }

        return $newPublicPath;
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

        return md5(self::CDN_IMAGE_SECRET . '|' . ltrim($sourcePath, '/') . '|' . http_build_query($params));
    }

    /**
     * @param string $path
     * @param array<string,string|int> $params
     * @return string
     */
    private function generateFileHash(string $path, array $params): string
    {
        $stringToHash = ltrim($path, '/') . http_build_query($params);
        return substr(md5($stringToHash), 0, self::HASH_LENGHT);
    }

    private function getMapper(): PathMapperInterface
    {
        return new BasicMapper([
            [
                'source' => '/files',
                'cache' => '/webroot/cdn',
                'public' => '/cdn',
            ],
        ]);
    }

    /**
     * @param string $publicPath
     * @param array<string,string|int> $queryParams
     * @return bool
     */
    public function createImage(string $publicPath, array $queryParams): bool
    {
        if ($this->isValidSignature($publicPath, $queryParams)) {
            //create image ..

            return true;
        }

        return false;
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
}
