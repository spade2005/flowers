<?php

namespace App\Entity;

use App\Repository\GoodsContentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoodsContentRepository::class),ORM\Table(name: "com_goods_content")]
#[ORM\Index(name: "idx_goods_id", columns: ["goods_id"])]
class GoodsContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $goods_id;

    #[ORM\Column(type: 'smallint', options: ["comment" => "0详情1售后2物流"])]
    private $type;

    #[ORM\Column(type: 'text')]
    private $content;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
}
