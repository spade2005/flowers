<?php

namespace App\Entity;

use App\Repository\OrderGoodsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderGoodsRepository::class),ORM\Table(name: "com_order_goods")]
#[ORM\Index(name: "idx_order_id", columns: ["order_id"])]
class OrderGoods
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $order_id;

    #[ORM\Column(type: 'integer')]
    private $goods_id;

    #[ORM\Column(type: 'string', length: 255)]
    private $goods_name;

    #[ORM\Column(type: 'string', length: 255)]
    private $goods_logo;

    #[ORM\Column(type: 'string', length: 20)]
    private $goods_sn;

    #[ORM\Column(type: 'integer', options: ["comment" => "商品数量"])]
    private $goods_num;

    #[ORM\Column(type: 'smallint', options: ["comment" => "类型"])]
    private $goods_type;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2, options: ["comment" => "商品金额"])]
    private $goods_amount;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2, options: ["comment" => "原始金额"])]
    private $origin_amount;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2)]
    private $discount_amount;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2, options: ["comment" => "实际"])]
    private $real_aount;

    #[ORM\Column(type: 'smallint', options: ["comment" => "1赠品"])]
    private $is_gift;

    #[ORM\Column(type: 'integer')]
    private $gift_id;

    #[ORM\Column(type: 'smallint', options: ["comment" => "1已发"])]
    private $shipping_status;

    #[ORM\Column(type: 'string', length: 50)]
    private $shipping_no;

    #[ORM\Column(type: 'integer', options: ["comment" => "退货单id"])]
    private $refund_id;

    #[ORM\Column(type: 'bigint', options: ["comment" => "创建日期"])]
    private $create_at;

    #[ORM\Column(type: 'bigint')]
    private $update_at;

    #[ORM\Column(type: 'smallint')]
    private $deleted;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getGoodsId(): ?int
    {
        return $this->goods_id;
    }

    public function setGoodsId(int $goods_id): self
    {
        $this->goods_id = $goods_id;

        return $this;
    }

    public function getGoodsName(): ?string
    {
        return $this->goods_name;
    }

    public function setGoodsName(string $goods_name): self
    {
        $this->goods_name = $goods_name;

        return $this;
    }

    public function getGoodsLogo(): ?string
    {
        return $this->goods_logo;
    }

    public function setGoodsLogo(string $goods_logo): self
    {
        $this->goods_logo = $goods_logo;

        return $this;
    }

    public function getGoodsSn(): ?string
    {
        return $this->goods_sn;
    }

    public function setGoodsSn(string $goods_sn): self
    {
        $this->goods_sn = $goods_sn;

        return $this;
    }

    public function getGoodsNum(): ?int
    {
        return $this->goods_num;
    }

    public function setGoodsNum(int $goods_num): self
    {
        $this->goods_num = $goods_num;

        return $this;
    }

    public function getGoodsType(): ?int
    {
        return $this->goods_type;
    }

    public function setGoodsType(int $goods_type): self
    {
        $this->goods_type = $goods_type;

        return $this;
    }

    public function getGoodsAmount(): ?string
    {
        return $this->goods_amount;
    }

    public function setGoodsAmount(string $goods_amount): self
    {
        $this->goods_amount = $goods_amount;

        return $this;
    }

    public function getOriginAmount(): ?string
    {
        return $this->origin_amount;
    }

    public function setOriginAmount(string $origin_amount): self
    {
        $this->origin_amount = $origin_amount;

        return $this;
    }

    public function getDiscountAmount(): ?string
    {
        return $this->discount_amount;
    }

    public function setDiscountAmount(string $discount_amount): self
    {
        $this->discount_amount = $discount_amount;

        return $this;
    }

    public function getRealAount(): ?string
    {
        return $this->real_aount;
    }

    public function setRealAount(string $real_aount): self
    {
        $this->real_aount = $real_aount;

        return $this;
    }

    public function getIsGift(): ?int
    {
        return $this->is_gift;
    }

    public function setIsGift(int $is_gift): self
    {
        $this->is_gift = $is_gift;

        return $this;
    }

    public function getGiftId(): ?int
    {
        return $this->gift_id;
    }

    public function setGiftId(int $gift_id): self
    {
        $this->gift_id = $gift_id;

        return $this;
    }

    public function getShippingStatus(): ?int
    {
        return $this->shipping_status;
    }

    public function setShippingStatus(int $shipping_status): self
    {
        $this->shipping_status = $shipping_status;

        return $this;
    }

    public function getShippingNo(): ?string
    {
        return $this->shipping_no;
    }

    public function setShippingNo(string $shipping_no): self
    {
        $this->shipping_no = $shipping_no;

        return $this;
    }

    public function getRefundId(): ?int
    {
        return $this->refund_id;
    }

    public function setRefundId(int $refund_id): self
    {
        $this->refund_id = $refund_id;

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
