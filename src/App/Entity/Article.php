<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Intervention\Image\ImageManagerStatic as Image;
use App\Util\AppUtility;
/**
 *
 * @ORM\Entity
 * @ORM\Table(name="`Article`", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="slug", columns={"slug"})
 * })
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\ArticleRepository")
 */
class Article
{
    use \App\Entity\EntityTrait\ImageTrait;


    protected function getImageDir()
    {
        return 'uploads/articles';
    }

    function getVariations()
    {
        return ['big', 'medium', 'small'];
    }

    function handleImage($variationName, $tmpPath, $savedPath)
    {
        $image = Image::make($tmpPath);
        if ($variationName == "big") {
            $image->fit(1024, 768);
        } else if ($variationName == "medium") {
            $image->fit(640, 480);
        } else if ($variationName == "small") {
            $image->fit(320, 210);
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
     * @ORM\Column(type="string", length=250)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $slug;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=350, nullable=true)
     */
    private $extract;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $activatedAt;

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
     * Set title
     *
     * @param string $title
     *
     * @return Article
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Article
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Article
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Article
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Article
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set activatedAt
     *
     * @param \DateTime $activatedAt
     *
     * @return Article
     */
    public function setActivatedAt($activatedAt)
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * Get activatedAt
     *
     * @return \DateTime
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * Set extract
     *
     * @param string $extract
     *
     * @return Article
     */
    public function setExtract($extract)
    {
        $this->extract = $extract;

        return $this;
    }

    /**
     * Get extract
     *
     * @return string
     */
    public function getExtract()
    {
        return $this->extract;
    }
     /**
     * @ORM\PrePersist
     */
    public function updateUpdatedAt() {
        $this->updatedAt = new \DateTime('Now');
    }

     /**
     * @ORM\PrePersist
     */
    public function addCreatedAt() {
        if ($this->createdAt == null) {
            $this->createdAt = new \DateTime('Now');
        }
    }


     /**
     * @ORM\PrePersist
     */
    public function addActivatedAt() {
        if ($this->activatedAt == null) {
            $this->activatedAt = new \DateTime('Now');
        }
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function generateSlug()
    {
        if ($this->title !== null) {
            $this->slug = AppUtility::getSlug($this->title);
        }

        return $this;
    }
}
