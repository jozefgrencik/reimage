<?php
declare(strict_types=1);

namespace Reimage;

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
        } else {
            /** @var ImageInterface[] $availableAdapters */
            $availableAdapters = [
                new Intervention(),
                new Imagine(),
            ];

            foreach ($availableAdapters as $adapter) {
                if ($adapter->isInstalled()) {
                    return $adapter;
                }
            }

            throw new ReimageException('No available image adapter was found');
        }
    }

    public function setImager(ImageInterface $imageAdapter): self
    {
        $this->imageAdapter = $imageAdapter;

        return $this;
    }
}
