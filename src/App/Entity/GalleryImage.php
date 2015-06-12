<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Intervention\Image\ImageManagerStatic as Image;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="`GalleryImage`")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\GalleryImageRepository")
 */
class GalleryImage
{
    use \App\Entity\EntityTrait\ImageTrait;

    public function getImageDir()
    {
        return 'uploads/gallery';
    }

    public function getVariations()
    {
        return ['big', 'small', 'xx-small'];
    }

    public function handleImage($variationName, $tmpPath, $savedPath)
    {
        $image = Image::make($tmpPath);
        if ($variationName == "big") {
            $image->fit(1024, 768);
        } else if ($variationName == "small") {
            $image->fit(350, 317);
        } else if ($variationName == "xx-small") {
            $image->fit(150, 100);
        }

        $image->save($savedPath);
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="`order`", type="integer")
     */
    private $order = 0;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set order
     *
     * @param integer $order
     *
     * @return GaleryImage
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set imageVersion
     *
     * @param integer $imageVersion
     *
     * @return GaleryImage
     */
    public function setImageVersion($imageVersion)
    {
        $this->imageVersion = $imageVersion;

        return $this;
    }

    /**
     * Get imageVersion
     *
     * @return integer
     */
    public function getImageVersion()
    {
        return $this->imageVersion;
    }
}
