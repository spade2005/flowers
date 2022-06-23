<?php

namespace App\Entity;

use App\Repository\MemberCartRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberCartRepository::class),ORM\Table(name: "com_member_cart")]
#[ORM\Index(name: "idx_member_id", columns: ["member_id"])]
class MemberCart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $member_id;

    #[ORM\Column(type: 'integer')]
    private $goods_id;

    #[ORM\Column(type: 'integer',options: ["comment" => "数量"])]
    private $goods_number;

    #[ORM\Column(type: 'smallint',options: ["comment" => "0加入1已取消2已下单"])]
    private $status;

    #[ORM\Column(type: 'integer',options: ["comment" => "订单id"])]
    private $order_id;

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

    public function getMemberId(): ?int
    {
        return $this->member_id;
    }

    public function setMemberId(int $member_id): self
    {
        $this->member_id = $member_id;

        return $this;
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

    public function getGoodsNumber(): ?int
    {
        return $this->goods_number;
    }

    public function setGoodsNumber(int $goods_number): self
    {
        $this->goods_number = $goods_number;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getOrderId(): ?int
    {
        return $this->order_id;
    }

    public function setOrderId(int $order_id): self
    {
        $this->order_id = $order_id;

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

    public function getUpdateAt(): ?string
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
