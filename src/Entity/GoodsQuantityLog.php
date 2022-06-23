<?php

namespace App\Entity;

use App\Repository\GoodsQuantityLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoodsQuantityLogRepository::class),ORM\Table(name: "com_goods_quantity_log")]
#[ORM\Index(name: "idx_goods_id", columns: ["goods_id"])]
class GoodsQuantityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $goods_id;

    #[ORM\Column(type: 'smallint', options: ["comment" => "1add,2div"])]
    private $type;

    #[ORM\Column(type: 'integer')]
    private $quantity;

    #[ORM\Column(type: 'smallint', options: ["comment" => "1system2order"])]
    private $source;

    #[ORM\Column(type: 'string', length: 50)]
    private $create_by;

    #[ORM\Column(type: 'bigint')]
    private $create_at;

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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSource(): ?int
    {
        return $this->source;
    }

    public function setSource(int $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getCreateBy(): ?string
    {
        return $this->create_by;
    }

    public function setCreateBy(string $create_by): self
    {
        $this->create_by = $create_by;

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
