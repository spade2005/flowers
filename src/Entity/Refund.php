<?php

namespace App\Entity;

use App\Repository\RefundRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RefundRepository::class),ORM\Table(name: "com_refund")]
#[ORM\Index(name: "idx_order_id", columns: ["order_id"])]
#[ORM\Index(name: "idx_member_id", columns: ["member_id"])]
class Refund
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $order_id;

    #[ORM\Column(type: 'string', length: 20)]
    private $order_no;

    #[ORM\Column(type: 'string', length: 20)]
    private $refund_no;

    #[ORM\Column(type: 'integer')]
    private $member_id;

    #[ORM\Column(type: 'smallint', options: ["comment" => "0待审核1待收货2待验货3验货ok4验货失败5退货成功6退款成功9审核不通过"])]
    private $refund_status;

    #[ORM\Column(type: 'smallint', options: ["comment" => "1退款2退货"])]
    private $type;

    #[ORM\Column(type: 'string', length: 255)]
    private $mark;

    #[ORM\Column(type: 'string', length: 255, options: ["comment" => "退款原因"])]
    private $reason;

    #[ORM\Column(type: 'text', options: ["comment" => "退款拍照记录"])]
    private $reason_imgs;

    #[ORM\Column(type: 'string', length: 50, options: ["comment" => "退货单号"])]
    private $shipping_no;

    #[ORM\Column(type: 'bigint')]
    private $create_at;

    #[ORM\Column(type: 'bigint')]
    private $update_at;

    #[ORM\Column(type: 'smallint')]
    private $deleted;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2, options: ["comment" => "实际付款"])]
    private $order_amount;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2, options: ["comment" => "实际付款"])]
    private $refund_amount;

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

    public function getRefundNo(): ?string
    {
        return $this->refund_no;
    }

    public function setRefundNo(string $refund_no): self
    {
        $this->refund_no = $refund_no;

        return $this;
    }

    public function getOrderNo(): ?string
    {
        return $this->order_no;
    }

    public function setOrderNo(string $order_no): self
    {
        $this->order_no = $order_no;

        return $this;
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

    public function getRefundStatusStr(): ?string
    {
        $str = '';
        switch ($this->refund_status) {
            case 0:
                $str = '待审核';
                break;
            case 1:
                $str = '待收货';
                break;
            case 2:
                $str = '待验货';
                break;
            case 3:
                $str = '验货成功';
                break;
            case 4:
                $str = '验货失败';
                break;
            case 5:
                $str = '退货成功';
                break;
            case 6:
                $str = '退款成功';
                break;
            case 9:
                $str = '审核不通过';
                break;
        }
        return $str;
    }

    public function getRefundStatus(): ?int
    {
        return $this->refund_status;
    }

    public function setRefundStatus(int $refund_status): self
    {
        $this->refund_status = $refund_status;

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

    public function getMark(): ?string
    {
        return $this->mark;
    }

    public function setMark(string $mark): self
    {
        $this->mark = $mark;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getReasonImgs(): ?string
    {
        return $this->reason_imgs;
    }

    public function setReasonImgs(string $reason_imgs): self
    {
        $this->reason_imgs = $reason_imgs;

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

    public function getCreateAt(): ?string
    {
        return $this->create_at ? date("Y-m-d H/i/s",$this->create_at) : '';
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

    public function getOrderAmount(): ?string
    {
        return $this->order_amount;
    }

    public function setOrderAmount(string $order_amount): self
    {
        $this->order_amount = $order_amount;

        return $this;
    }

    public function getRefundAmount(): ?string
    {
        return $this->refund_amount;
    }

    public function setRefundAmount(string $refund_amount): self
    {
        $this->refund_amount = $refund_amount;

        return $this;
    }
}
