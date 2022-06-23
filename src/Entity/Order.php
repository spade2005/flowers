<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class), ORM\Table(name: "com_order")]
#[ORM\Index(name: "idx_member_id", columns: ["member_id"])]
#[ORM\Index(name: "idx_time_of_day", columns: ["time_of_day"])]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $member_id;

    #[ORM\Column(type: 'integer')]
    private $member_level;

    #[ORM\Column(type: 'string', length: 20)]
    private $order_no;

    #[ORM\Column(type: 'smallint', options: ["comment" => "1已下单2已支付,3已发货4已完成5已评价,9已取消10售后单"])]
    private $order_status;

    #[ORM\Column(type: 'string', length: 255)]
    private $order_mark;

    #[ORM\Column(type: 'smallint', options: ["comment" => "1已付"])]
    private $pay_status;

    #[ORM\Column(type: 'bigint')]
    private $pay_time;

    #[ORM\Column(type: 'smallint', options: ["comment" => "1已发2部分发"])]
    private $shipping_status;

    #[ORM\Column(type: 'bigint')]
    private $shipping_time;

    #[ORM\Column(type: 'smallint', options: ["comment" => "1快递2自提"])]
    private $shipping_way;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2)]
    private $origin_amount;

    #[ORM\Column(type: 'decimal', precision: 10, scale: '2', options: ["comment" => "快递费"])]
    private $shipping_price;

    #[ORM\Column(type: 'decimal', precision: 20, scale: '2', options: ["comment" => "折扣优惠"])]
    private $discount_amount;

    #[ORM\Column(type: 'integer', options: ["comment" => "积分优惠"])]
    private $point_amount;

    #[ORM\Column(type: 'integer', options: ["comment" => "卡券优惠"])]
    private $coupon_amount;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2, options: ["comment" => "实际付款"])]
    private $real_amount;

    #[ORM\Column(type: 'integer', options: ["comment" => "使用积分"])]
    private $use_point;

    #[ORM\Column(type: 'string', length: 255, options: ["comment" => "卡券"])]
    private $use_coupon;

    #[ORM\Column(type: 'string', length: 255, options: ["comment" => "折扣"])]
    private $use_discount;

    #[ORM\Column(type: 'integer', options: ["comment" => "获得积分"])]
    private $get_point;

    #[ORM\Column(type: 'integer')]
    private $addr_id;

    #[ORM\Column(type: 'string', length: 50)]
    private $addr_name;

    #[ORM\Column(type: 'string', length: 20)]
    private $addr_mobile;

    #[ORM\Column(type: 'string', length: 255)]
    private $address;

    #[ORM\Column(type: 'string', length: 255)]
    private $addr_mark;

    #[ORM\Column(type: 'bigint', options: ["comment" => "确认时间"])]
    private $confirm_time;

    #[ORM\Column(type: 'integer', options: ["comment" => "是否售后单"])]
    private $refund_id;

    #[ORM\Column(type: 'string', length: 20, options: ["comment" => "来源"])]
    private $order_from;

    #[ORM\Column(type: 'integer')]
    private $time_of_day;

    #[ORM\Column(type: 'integer')]
    private $time_of_hour;

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

    public function getMemberId(): ?int
    {
        return $this->member_id;
    }

    public function setMemberId(int $member_id): self
    {
        $this->member_id = $member_id;

        return $this;
    }

    public function getMemberLevel(): ?int
    {
        return $this->member_level;
    }

    public function setMemberLevel(int $member_level): self
    {
        $this->member_level = $member_level;

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

    public function getOrderStatusStr()
    {

        $str = '';
        switch ($this->order_status) {
            case 1:
                $str = '已下单,待付款';
                break;
            case 2:
                $str = '已支付,待发货';
                break;
            case 3:
                $str = '已发货,待收货';
                break;
            case 4:
                $str = '已完成';
                break;
            case 5:
                $str = '已评价';
                break;
            case 9:
                $str = '已取消';
                break;
            case 10:
                $str = '售后单';
                break;
        }
        return $str;
    }

    public function getOrderStatus(): ?int
    {
        return $this->order_status;
    }

    public function setOrderStatus(int $order_status): self
    {
        $this->order_status = $order_status;

        return $this;
    }

    public function getOrderMark(): ?string
    {
        return $this->order_mark;
    }

    public function setOrderMark(string $order_mark): self
    {
        $this->order_mark = $order_mark;

        return $this;
    }

    public function getPayStatus(): ?int
    {
        return $this->pay_status;
    }

    public function setPayStatus(int $pay_status): self
    {
        $this->pay_status = $pay_status;

        return $this;
    }

    public function getPayTime(bool $isReal = false): ?string
    {
        if ($isReal)
            return $this->pay_time;
        return date("Y-m-d H/i/s", $this->pay_time);
    }

    public function setPayTime(string $pay_time): self
    {
        $this->pay_time = $pay_time;

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

    public function getShippingTime(bool $isReal=false): ?string
    {
        if($isReal)
            return $this->shipping_time;
        return $this->shipping_time ? date("Y-m-d H/i/s",$this->shipping_time) : '-';
    }

    public function setShippingTime(string $shipping_time): self
    {
        $this->shipping_time = $shipping_time;

        return $this;
    }

    public function getShippingWay(bool $isReal = false)
    {
        if ($isReal)
            return $this->shipping_way;
        return $this->shipping_way == 1 ? '快递' : '自提';
    }

    public function setShippingWay(int $shipping_way): self
    {
        $this->shipping_way = $shipping_way;

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

    public function getShippingPrice(): ?string
    {
        return $this->shipping_price;
    }

    public function setShippingPrice(string $shipping_price): self
    {
        $this->shipping_price = $shipping_price;

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

    public function getPointAmount(): ?int
    {
        return $this->point_amount;
    }

    public function setPointAmount(int $point_amount): self
    {
        $this->point_amount = $point_amount;

        return $this;
    }

    public function getCouponAmount(): ?int
    {
        return $this->coupon_amount;
    }

    public function setCouponAmount(int $coupon_amount): self
    {
        $this->coupon_amount = $coupon_amount;

        return $this;
    }

    public function getRealAmount(): ?string
    {
        return $this->real_amount;
    }

    public function setRealAmount(string $real_amount): self
    {
        $this->real_amount = $real_amount;

        return $this;
    }

    public function getUsePoint(): ?int
    {
        return $this->use_point;
    }

    public function setUsePoint(int $use_point): self
    {
        $this->use_point = $use_point;

        return $this;
    }

    public function getUseCoupon(): ?string
    {
        return $this->use_coupon;
    }

    public function setUseCoupon(string $use_coupon): self
    {
        $this->use_coupon = $use_coupon;

        return $this;
    }

    public function getUseDiscount(): ?string
    {
        return $this->use_discount;
    }

    public function setUseDiscount(string $use_discount): self
    {
        $this->use_discount = $use_discount;

        return $this;
    }

    public function getGetPoint(): ?int
    {
        return $this->get_point;
    }

    public function setGetPoint(int $get_point): self
    {
        $this->get_point = $get_point;

        return $this;
    }

    public function getAddrId(): ?int
    {
        return $this->addr_id;
    }

    public function setAddrId(int $addr_id): self
    {
        $this->addr_id = $addr_id;

        return $this;
    }

    public function getAddrName(): ?string
    {
        return $this->addr_name;
    }

    public function setAddrName(string $addr_name): self
    {
        $this->addr_name = $addr_name;

        return $this;
    }

    public function getAddrMobile(): ?string
    {
        return $this->addr_mobile;
    }

    public function setAddrMobile(string $addr_mobile): self
    {
        $this->addr_mobile = $addr_mobile;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddrMark(): ?string
    {
        return $this->addr_mark;
    }

    public function setAddrMark(string $addr_mark): self
    {
        $this->addr_mark = $addr_mark;

        return $this;
    }

    public function getConfirmTime(bool $isReal=false): ?string
    {
        if($isReal)
            return $this->confirm_time;
        return $this->confirm_time ? date("Y-m-d H/i/s",$this->confirm_time) : '-';
    }

    public function setConfirmTime(string $confirm_time): self
    {
        $this->confirm_time = $confirm_time;

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

    public function getOrderFrom(): ?string
    {
        return $this->order_from;
    }

    public function setOrderFrom(string $order_from): self
    {
        $this->order_from = $order_from;

        return $this;
    }

    public function getTimeOfDay(): ?int
    {
        return $this->time_of_day;
    }

    public function setTimeOfDay(int $time_of_day): self
    {
        $this->time_of_day = $time_of_day;

        return $this;
    }

    public function getTimeOfHour(): ?int
    {
        return $this->time_of_hour;
    }

    public function setTimeOfHour(int $time_of_hour): self
    {
        $this->time_of_hour = $time_of_hour;

        return $this;
    }

    public function getCreateAt(): ?string
    {
        return $this->create_at ? date("Y-m-d H:i:s", $this->create_at) : '-';
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
