<?php
declare(strict_types=1);

namespace Reimage;

use Reimage\Exceptions\ReimageException;
use Reimage\FileSystemAdapters\FileSystemInterface;
use Reimage\FileSystemAdapters\Local;
use Reimage\ImageAdapters\ImageInterface;

class Reimage
{
    public const WIDTH = 'w';
    public const HEIGHT = 'h';
    public const SIGN = 's';
    public const QUALITY = 'q';
    public const BACKGROUND_COLOR = 'bc';
    public const FOCUS_X = 'fx';
    public const FOCUS_Y = 'fy';
    public const FOCUS_X_PERCENT = 'fxp';
    public const FOCUS_Y_PERCENT = 'fyp';
    public const ROTATE = 'r';
    public const PROFILE = 'p';
    public const GREYSCALE = 'grey';
    public const BLUR = 'blur';
    public const NEGATIVE = 'neg';
    public const BRIGHTNESS = 'b';
    public const FLIP = 'flip';
    public const GAMMA = 'gam';

    private const ALL_PARAMS = [
        self::WIDTH,
        self::HEIGHT,
        self::SIGN,
        self::QUALITY,
        self::BACKGROUND_COLOR,
        self::FOCUS_X,
        self::FOCUS_Y,
        self::FOCUS_X_PERCENT,
        self::FOCUS_Y_PERCENT,
        self::ROTATE,
        self::PROFILE,
        self::GREYSCALE,
        self::BLUR,
        self::NEGATIVE,
        self::BRIGHTNESS,
        self::FLIP,
        self::GAMMA,
    ];

    private const HASH_LENGHT = 6;
    private const CDN_IMAGE_SECRET = '1234';

    /** @var Config */
    private $config;

    /**
     * Reimage constructor.
     * @param Config|array<string,mixed>|null $config
     */
    public function __construct($config = null)
    {
        if ($config instanceof Config) {
            $this->config = $config;
        } elseif (is_array($config)) {
            $this->config = new Config($config);
        } else {
            $this->config = new Config();
        }
    }


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
        $mapper = $this->config->getPathMapper();
        $publicPath = $mapper->remapSourceToPublic($sourcePath);

        $hashedPart = $this->generateFileHash($sourcePath, $params);

        $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $newPublicPath = preg_replace('/(\.' . preg_quote($ext, '/') . ')$/', '_' . $hashedPart . '.' . $ext, $publicPath, 1);

        if ($newPublicPath === null) {
            throw new ReimageException('preg_replace error occurred');
        }

        return $newPublicPath;
    }

    /**
     * @throws ReimageException
     */
    private function generateSourcePath(string $publicPath): string
    {
        $ext = pathinfo($publicPath, PATHINFO_EXTENSION);
        $publicWithoutHash = preg_replace('/_[a-f0-9]{' . self::HASH_LENGHT . '}\.' . preg_quote($ext, '/') . '/', '.' . $ext, $publicPath);
        if ($publicWithoutHash === null) {
            throw new ReimageException('preg_replace error occurred');
        }

        $mapper = $this->config->getPathMapper();
        $sourcePath = $mapper->remapPublicToSource($publicWithoutHash);

        return $sourcePath;
    }

    /**
     * @throws ReimageException
     */
    private function generateCachePath(string $publicPath): string
    {
        $mapper = $this->config->getPathMapper();

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

    /**
     * @param string $publicPath
     * @param array<string,string> $queryParams
     * @return string
     * @throws ReimageException
     */
    public function createImage(string $publicPath, array $queryParams): string
    {
        if (!$this->isValidSignature($publicPath, $queryParams)) {
            throw new ReimageException('Invalid signature');
        }

        $sourcePath = $this->generateSourcePath($publicPath);
        $cachePath = $this->generateCachePath($publicPath);

        $image = $this->doImageCommands($sourcePath, $queryParams);
        $this->getFileSystemAdapter()->saveContent($cachePath, $image->getImageString());

        return $cachePath;
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
     * @param array<string,string> $params
     * @return ImageInterface
     * @throws ReimageException
     */
    private function doImageCommands(string $fullPath, array $params): ImageInterface
    {
        $imageClass = $this->config->getImageAdapter();
        $imageClass->loadImage($fullPath);

        $width = $params[self::WIDTH] ?? null;
        $height = $params[self::HEIGHT] ?? null;
        if ($width || $height) {
            $width = $width !== null ? (int)$width : null;
            $height = $height !== null ? (int)$height : null;

            $imageClass->resize($width, $height);
        }

        $rotationAngle = $params[self::ROTATE] ?? null;
        if ($rotationAngle !== null) {
            $imageClass->rotate((float)$rotationAngle);
        }

        if (array_key_exists(self::GREYSCALE, $params)) {
            $imageClass->greyscale();
        }

        $blur = $params[self::BLUR] ?? null;
        if ($blur !== null) {
            $imageClass->blur((int)$blur);
        }

        if (array_key_exists(self::NEGATIVE, $params)) {
            $imageClass->negative();
        }

        $brightness = $params[self::BRIGHTNESS] ?? null;
        if ($brightness !== null) {
            $imageClass->brightness((int)$brightness);
        }

        $flip = $params[self::FLIP] ?? null;
        if ($flip !== null) {
            $imageClass->flip((string)$flip);
        }

        $gamma = $params[self::GAMMA] ?? null;
        if ($gamma !== null) {
            $imageClass->gamma((float)$gamma);
        }

        return $imageClass;
    }

    private function getFileSystemAdapter(): FileSystemInterface
    {
        return new Local();
    }
}
