<?php

namespace App\Entity;

use App\Repository\GoodsImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoodsImageRepository::class),ORM\Table(name: "com_goods_image")]
#[ORM\Index(name: "idx_goods_id", columns: ["goods_id"])]
class GoodsImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $goods_id;

    #[ORM\Column(type: 'smallint', options: ["comment" => "0主图"])]
    private $type;

    #[ORM\Column(type: 'string', length: 255)]
    private $image_src;

    #[ORM\Column(type: 'boolean',options: ["comment" => "1为默认"])]
    private $is_default;

    #[ORM\Column(type: 'bigint')]
    private $create_at;

    #[ORM\Column(type: 'bigint')]
    private $update_at;

    #[ORM\Column(type: 'smallint')]
    private $deleted;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoodsId(): ?int
    {
        return $this->goods_id;
    }

    public function setGoodsId(int $goods_id): self
    {
        $this->goods_id = $goods_id;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getImageSrc(): ?string
    {
        return $this->image_src;
    }

    public function setImageSrc(string $image_src): self
    {
        $this->image_src = $image_src;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->is_default;
    }

    public function setIsDefault(bool $is_default): self
    {
        $this->is_default = $is_default;

        return $this;
    }

    public function getCreateAt(): ?string
    {
        return $this->create_at;
    }

    public function setCreateAt(string $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getUpdateAt()
    {
        return $this->update_at;
    }

    public function setUpdateAt(string $update_at): self
    {
        $this->update_at = $update_at;

        return $this;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(int $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
}
