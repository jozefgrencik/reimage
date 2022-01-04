<?php
declare(strict_types=1);

namespace Reimage;

use Imagine\Image\AbstractImagine;
use Imagine\Image\ImagineInterface;
use Intervention\Image\ImageManager;
use Reimage\Exception\ReimageException;
use Reimage\ImageProcessorAdapter\ImageProcessorInterface;
use Reimage\ImageProcessorAdapter\Imagine;
use Reimage\ImageProcessorAdapter\Intervention;
use Reimage\PathMapperAdapter\PathMapperInterface;

class Config
{
    /** @var ImageProcessorInterface|null Image processor adapter */
    private $imageProcessor;

    /** @var PathMapperInterface|null */
    private $pathMapperAdapter;

    /**
     * Config constructor.
     * @param array<string, mixed> $config
     * @throws ReimageException
     */
    public function __construct(array $config = null)
    {
        if (is_array($config)) {
            if (array_key_exists('path_mapper', $config)) {
                $this->setPathMapper($config['path_mapper']);
            }
            if (array_key_exists('image_adapter', $config)) {
                $this->setImageProcessor($config['image_adapter']);
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
     * Get Image processor. If it is not defined, we will guess.
     * @throws ReimageException
     */
    public function getImageProcessor(): ImageProcessorInterface
    {
        if ($this->imageProcessor) {
            return $this->imageProcessor;
        }

        // we will guess
        if (class_exists(ImageManager::class)) {
            if ($this->isImagickEnabled()) {
                $this->imageProcessor = new Intervention(new ImageManager(['driver' => 'imagick']));
            } elseif ($this->isGdEnabled()) {
                $this->imageProcessor = new Intervention(new ImageManager(['driver' => 'gd']));
            }
        }

        if ($this->imageProcessor === null && class_exists(AbstractImagine::class)) {
            if ($this->isImagickEnabled()) {
                $this->imageProcessor = new Imagine(new \Imagine\Imagick\Imagine());
            } elseif ($this->isGdEnabled()) {
                $this->imageProcessor = new Imagine(new \Imagine\Gd\Imagine());
            } elseif ($this->isGmagickEnabled()) {
                $this->imageProcessor = new Imagine(new \Imagine\Gmagick\Imagine());
            }
        }

        throw new ReimageException('No available image adapter was found');
    }

    /**
     * Set Image procesor instance.
     * @param ImageProcessorInterface|ImageManager|ImagineInterface $imageAdapter
     * @return $this
     * @throws ReimageException
     */
    public function setImageProcessor($imageAdapter): self
    {
        if ($imageAdapter instanceof ImageProcessorInterface) {
            $this->imageProcessor = $imageAdapter;
        } elseif ($imageAdapter instanceof ImageManager) {
            $this->imageProcessor = new Intervention($imageAdapter);
        } elseif ($imageAdapter instanceof ImagineInterface) {
            $this->imageProcessor = new Imagine($imageAdapter);
        } else {
            throw new ReimageException('Unsupported Image processor library');
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
