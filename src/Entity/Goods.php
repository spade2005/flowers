<?php

namespace App\Entity;

use App\Repository\GoodsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoodsRepository::class),ORM\Table(name: "com_goods")]
#[ORM\Index(name: "idx_goods_sn", columns: ["goods_sn"])]
#[ORM\Index(name: "idx_cate_id", columns: ["cate_id"])]
class Goods
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'integer', options: ["comment" => "所属分类"])]
    private $cate_id;

    #[ORM\Column(type: 'smallint', options: ["comment" => "类型"])]
    private $type;

    #[ORM\Column(type: 'string', length: 20,unique: true, options: ["comment" => "唯一编码"])]
    private $goods_sn;

    #[ORM\Column(type: 'string', length: 255, options: ["comment" => "描述"])]
    private $intro;

    #[ORM\Column(type: 'string', length: 100)]
    private $brand;

    #[ORM\Column(type: 'string', length: 100)]
    private $store;

    #[ORM\Column(type: 'integer', options: ["comment" => "从小到大排序"])]
    private $sort_by;

    #[ORM\Column(type: 'smallint', options: ["comment" => "0下架1上架2上架不显示"])]
    private $is_on_sale;

    #[ORM\Column(type: 'smallint', options: ["comment" => "0长久有效1到期下架"])]
    private $effect_type;

    #[ORM\Column(type: 'bigint')]
    private $on_time;

    #[ORM\Column(type: 'bigint')]
    private $off_time;

    #[ORM\Column(type: 'integer', options: ["comment" => "0不限购"])]
    private $buy_limit;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2, options: ["comment" => "显示价"])]
    private $price;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 2, options: ["comment" => "划线价"])]
    private $real_price;

    #[ORM\Column(type: 'integer', options: ["comment" => "当前库存"])]
    private $quantity;

    #[ORM\Column(type: 'integer', options: ["comment" => "总库存"])]
    private $total_quantity;

    #[ORM\Column(type: 'boolean', options: ["comment" => "0不可加入购物车1可"])]
    private $is_cart;

    #[ORM\Column(type: 'boolean', options: ["comment" => "0不可搜索1可"])]
    private $is_search;

    #[ORM\Column(type: 'boolean', options: ["comment" => "1为测试"])]
    private $is_test;

    #[ORM\Column(type: 'integer', options: ["comment" => "允许退货天数"])]
    private $return_day;

    #[ORM\Column(type: 'integer', options: ["comment" => "总销量"])]
    private $total_sales;

    #[ORM\Column(type: 'integer', options: ["comment" => "总浏览"])]
    private $total_views;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCateId(): ?int
    {
        return $this->cate_id;
    }

    public function setCateId(int $cate_id): self
    {
        $this->cate_id = $cate_id;

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

    public function getGoodsSn(): ?string
    {
        return $this->goods_sn;
    }

    public function setGoodsSn(string $goods_sn): self
    {
        $this->goods_sn = $goods_sn;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getStore(): ?string
    {
        return $this->store;
    }

    public function setStore(string $store): self
    {
        $this->store = $store;

        return $this;
    }

    public function getSortBy(): ?int
    {
        return $this->sort_by;
    }

    public function setSortBy(int $sort_by): self
    {
        $this->sort_by = $sort_by;

        return $this;
    }

    public function getIsOnSale(): ?int
    {
        return $this->is_on_sale;
    }

    public function setIsOnSale(int $is_on_sale): self
    {
        $this->is_on_sale = $is_on_sale;

        return $this;
    }

    public function getEffectType(): ?int
    {
        return $this->effect_type;
    }

    public function setEffectType(int $effect_type): self
    {
        $this->effect_type = $effect_type;

        return $this;
    }

    public function getOnTime(): ?string
    {
        return $this->on_time;
    }

    public function setOnTime(string $on_time): self
    {
        $this->on_time = $on_time;

        return $this;
    }

    public function getOffTime(): ?string
    {
        return $this->off_time;
    }

    public function setOffTime(string $off_time): self
    {
        $this->off_time = $off_time;

        return $this;
    }

    public function getBuyLimit(): ?int
    {
        return $this->buy_limit;
    }

    public function setBuyLimit(int $buy_limit): self
    {
        $this->buy_limit = $buy_limit;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

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

    public function getTotalQuantity(): ?int
    {
        return $this->total_quantity;
    }

    public function setTotalQuantity(int $total_quantity): self
    {
        $this->total_quantity = $total_quantity;

        return $this;
    }

    public function getIsCart(): ?bool
    {
        return $this->is_cart;
    }

    public function setIsCart(bool $is_cart): self
    {
        $this->is_cart = $is_cart;

        return $this;
    }

    public function getIsSearch(): ?bool
    {
        return $this->is_search;
    }

    public function setIsSearch(bool $is_search): self
    {
        $this->is_search = $is_search;

        return $this;
    }

    public function getIsTest(): ?bool
    {
        return $this->is_test;
    }

    public function setIsTest(bool $is_test): self
    {
        $this->is_test = $is_test;

        return $this;
    }

    public function getReturnDay(): ?int
    {
        return $this->return_day;
    }

    public function setReturnDay(int $return_day): self
    {
        $this->return_day = $return_day;

        return $this;
    }

    public function getTotalSales(): ?int
    {
        return $this->total_sales;
    }

    public function setTotalSales(int $total_sales): self
    {
        $this->total_sales = $total_sales;

        return $this;
    }

    public function getTotalViews(): ?int
    {
        return $this->total_views;
    }

    public function setTotalViews(int $total_views): self
    {
        $this->total_views = $total_views;

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

    public function getRealPrice(): ?string
    {
        return $this->real_price;
    }

    public function setRealPrice(string $real_price): self
    {
        $this->real_price = $real_price;

        return $this;
    }
}
