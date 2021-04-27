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

        $cacheFile = $this->generateFileName($sourcePath, $fullParams);

        $query = $rawParams + [
                self::SIGN => $this->generateSignature($cacheFile, $fullParams),
            ];

        return $cacheFile . '?' . http_build_query($query);
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
     * @param string $relativePath
     * @param array<string,string|int> $params
     * @return string
     * @throws \Exception
     */
    private function generateFileName(string $relativePath, array $params): string
    {
        unset($params[self::SIGN]);
        ksort($params);

        $mapper = $this->getMapper();
        $publicPath = $mapper->remapSourceToPublic($relativePath);

        $hashedPart = $this->generateFileHash($relativePath, $params);

        $ext = pathinfo($relativePath, PATHINFO_EXTENSION);
        $newPath = preg_replace('/(\.' . preg_quote($ext, '/') . ')$/', '_' . $hashedPart . '.' . $ext, $publicPath, 1);

        if ($newPath === null) {
            throw new \Exception('preg_replace error occurred');
        }

        return $newPath;
    }

    /**
     * @param string $path
     * @param array<string,string|int> $params
     * @return string
     */
    private function generateSignature(string $path, array $params): string
    {
        unset($params[self::SIGN]);
        ksort($params);

        return md5(self::CDN_IMAGE_SECRET . '|' . ltrim($path, '/') . '|' . http_build_query($params));
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
}
