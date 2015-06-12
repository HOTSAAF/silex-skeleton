<?php

namespace App\Entity\EntityTrait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Util\AppUtility;

trait ImageTrait
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $imageVersion = 0;

    private $tmp_id;

    private $image;

    private $variations = null;

    private $extension = 'jpg';

    public function processUploadedImage()
    {
        $this->variations = $this->getVariations();

        foreach ($this->variations as $variationName) {
            $this->handleImage(
                $variationName,
                $this->getImage()->getPathname(),
                $this->getImageAbsolutePath($variationName)
            );
        }
    }

    public function incrementImageVersion()
    {
        if (null === $this->getImage()) {
            return;
        }

        $this->imageVersion++;
    }

    /**
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function uploadImage()
    {
        if (null === $this->getImage()) {
            return;
        }

        // Create the upload folder
        if (!is_dir($this->getImageRootDir())) {
            mkdir($this->getImageRootDir(), 0777, true);
        }

        // Handle image
        $this->processUploadedImage();

        $this->image = null;
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemoveImage()
    {
        $this->tmp_id = $this->id;
    }

    /**
     * @ORM\PostRemove
     */
    public function postRemoveImage()
    {
        if ($this->tmp_id) {
            $this->id = $this->tmp_id;

            $this->variations = $this->getVariations();

            foreach ($this->variations as $variationName) {
                $file = $this->getImageAbsolutePath($variationName);
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setImage(UploadedFile $image = null)
    {
        $this->image = $image;

        $this->incrementImageVersion();

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImageByPath($path)
    {
        $relPath = __DIR__.'/../../../../'.$path;

        $this->image = AppUtility::getUploadedFileByPath($path);

        $this->incrementImageVersion();

        return $this;
    }

    public function getImageName($variationName = null, $withVersion = true)
    {
        $this->variations = $this->getVariations();

        if ($variationName === null) {
            reset($this->variations);
            $variationName = key($this->variations);
        }

        if (!in_array($variationName, $this->variations)) {
            throw new \Exception('No "' . $variationName . '" size exists for the ArticleImage Entity.');
        }

        return $this->id . '_' . $variationName . '.' . $this->extension . ($withVersion ? '?v' . $this->imageVersion : '');
    }

    public function getImageAbsolutePath($size = null, $withVersion = false)
    {
        return $this->imageVersion === 0
            ? null
            : $this->getImageRootDir() . '/' . $this->getImageName($size, $withVersion);
    }

    public function getImageWebPath($size = null, $withVersion = true)
    {
        return $this->imageVersion === 0
            ? null
            : $this->getImageDir() . '/' . $this->getImageName($size, $withVersion);
    }

    protected function getImageRootDir()
    {
        return __DIR__.'/../../../../web/' . $this->getImageDir();
    }
}
