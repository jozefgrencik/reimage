<?php
declare(strict_types=1);

namespace Reimage;

use Imagine\Image\AbstractImagine;
use Intervention\Image\ImageManager;
use Reimage\Exceptions\ReimageException;
use Reimage\ImageAdapters\ImageInterface;
use Reimage\ImageAdapters\Imagine;
use Reimage\ImageAdapters\Intervention;
use Reimage\PathMapperAdapters\PathMapperInterface;

class Config
{
    /** @var ImageInterface|null */
    private $imageAdapter;

    /** @var PathMapperInterface|null */
    private $pathMapperAdapter;

    /**
     * Config constructor.
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = null)
    {
        if (is_array($config)) {
            if (array_key_exists('path_mapper', $config)) {
                $this->setPathMapper($config['path_mapper']);
            }
            if (array_key_exists('image_adapter', $config)) {
                $this->setImager($config['image_adapter']);
            }
        }
    }

    /**
     * @throws ReimageException
     */
    public function getPathMapper(): PathMapperInterface
    {
        if ($this->pathMapperAdapter) {
            return $this->pathMapperAdapter;
        } else {
            throw new ReimageException('No available Path mapper was found');
        }
    }

    public function setPathMapper(PathMapperInterface $pathMapperAdapter): self
    {
        $this->pathMapperAdapter = $pathMapperAdapter;

        return $this;
    }

    /**
     * @throws ReimageException
     */
    public function getImageAdapter(): ImageInterface
    {
        if ($this->imageAdapter) {
            return $this->imageAdapter;
        }

        // we will guess
        if (class_exists(ImageManager::class)) {
            if ($this->isImagickEnabled()) {
                $this->imageAdapter = new Intervention(new ImageManager(['driver' => 'imagick']));
            } elseif ($this->isGdEnabled()) {
                $this->imageAdapter = new Intervention(new ImageManager(['driver' => 'gd']));
            }
        }

        if ($this->imageAdapter === null && class_exists(AbstractImagine::class)) {
            if ($this->isImagickEnabled()) {
                $this->imageAdapter = new Imagine(new \Imagine\Imagick\Imagine());
            } elseif ($this->isGdEnabled()) {
                $this->imageAdapter = new Imagine(new \Imagine\Gd\Imagine());
            } elseif ($this->isGmagickEnabled()) {
                $this->imageAdapter = new Imagine(new \Imagine\Gmagick\Imagine());
            }
        }

        throw new ReimageException('No available image adapter was found');
    }

    /**
     * @param ImageInterface|ImageManager $imageAdapter
     * @return $this
     * @throws ReimageException
     */
    public function setImager($imageAdapter): self
    {
        if ($imageAdapter instanceof ImageInterface) {
            $this->imageAdapter = $imageAdapter;
        } elseif ($imageAdapter instanceof ImageManager) {
            $this->imageAdapter = new Intervention($imageAdapter);
        } else {
            //todo imagine
            throw new ReimageException('Unsupported Imager');
        }

        return $this;
    }

    private function isGdEnabled(): bool
    {
        return extension_loaded('gd');
    }

    private function isImagickEnabled(): bool
    {
        return extension_loaded('imagick');
    }

    private function isGmagickEnabled(): bool
    {
        return extension_loaded('gmagick');
    }

}
