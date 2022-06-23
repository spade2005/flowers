<?php

namespace App\Acme\BackendBundle\src\Form;

use App\Entity\Goods;
use App\Entity\GoodsCate;
use App\Entity\GoodsContent;
use App\Entity\GoodsImage;
use App\Repository\GoodsRepository;
use Symfony\Component\Validator\Constraints as Assert;

//check from https://symfony.com/doc/current/validation.html
class GoodsForm
{
    private GoodsRepository $goodsRepository;

    public function __construct(GoodsRepository $goodsRepository)
    {
        $this->goodsRepository = $goodsRepository;
    }

    /**
     * @Assert\NotBlank(message="标题不能为空")
     * @Assert\Length(min=2,max=50,minMessage="标题不合法",maxMessage="标题不合法")
     */
    public $title;
    /**
     * @Assert\NotBlank(message="分类不能为空")
     * @Assert\GreaterThanOrEqual(0)
     */
    public $cate_id;
    /**
     * @Assert\NotBlank(message="描述不能为空")
     * @Assert\Length(min=1,max=255,minMessage="描述不合法",maxMessage="描述不合法")
     */
    public $intro;
    /**
     * @Assert\NotBlank(message="品牌不能为空")
     * @Assert\Length(min=1,max=50,minMessage="品牌不合法",maxMessage="品牌不合法")
     */
    public $brand;
    /**
     * @Assert\NotBlank(message="店铺不能为空")
     * @Assert\Length(min=1,max=50,minMessage="店铺不合法",maxMessage="店铺不合法")
     */
    public $store;
    /**
     * @Assert\NotBlank(message="状态不能为空")
     * @Assert\Range(min="0",max="1",minMessage="状态不合法",maxMessage="状态不合法")
     */
    public $is_on_sale;
    /**
     * @Assert\NotBlank(message="限购不能为空")
     * @Assert\Range(min="0",max="10000",minMessage="限购不合法",maxMessage="限购不合法")
     */
    public $buy_limit;
    /**
     * @Assert\NotBlank(message="划线价不能为空")
     * @Assert\Range(min="0",max="99999999",minMessage="划线价不合法",maxMessage="划线价不合法")
     */
    public $price;
    /**
     * @Assert\NotBlank(message="实价不能为空")
     * @Assert\Range(min="0",max="99999999",minMessage="实价不合法",maxMessage="实价不合法")
     */
    public $real_price;
    /**
     * @Assert\NotBlank(message="库存不能为空")
     * @Assert\Range(min="0",max="999999",minMessage="库存不合法",maxMessage="库存不合法")
     */
    public $quantity;

    /**
     * @Assert\NotBlank(message="加入购物车不能为空")
     * @Assert\Range(min="0",max="1",minMessage="加入购物车不合法",maxMessage="加入购物车不合法")
     */
    public $is_cart;
    /**
     * @Assert\NotBlank(message="是否搜索不能为空")
     * @Assert\Range(min="0",max="1",minMessage="是否搜索不合法",maxMessage="是否搜索不合法")
     */
    public $is_search;

    /**
     * @Assert\NotBlank(message="测试不能为空")
     * @Assert\Range(min="0",max="1",minMessage="测试不合法",maxMessage="测试不合法")
     */
    public $is_test;
    /**
     * @Assert\Type("array")
     */
    public $images;
    /**
     * @Assert\NotBlank(message="详情内容不能为空")
     */
    public $content1;
    /**
     * @Assert\NotBlank(message="售后内容不能为空")
     */
    public $content2;
    /**
     * @Assert\NotBlank(message="物流内容不能为空")
     */
    public $content3;


    public function helpCateList($format = false)
    {
        $q = $this->goodsRepository->getEm()->createQueryBuilder();
        $q->select("p.parent_id,p.id,p.title")->from(GoodsCate::class, "p")->where("p.deleted=0");
        $list = $q->getQuery()->getArrayResult();
        if (empty($list)) {
            return [];
        }
        if (!$format) {
            return $list;
        }
        $tmp = [];
        foreach ($list as $val) {
            if ($val['parent_id'] == 0) {
                $tmp[$val['id']] = $val;
            }
        }

        foreach ($list as $val) {
            if ($val['parent_id'] > 0) {
                if (isset($tmp[$val['parent_id']])) {
                    $tmp[$val['parent_id']]['sub'][] = $val;
                }
            }
        }
        return array_values($tmp);
    }

    public function helpImageList(int $id)
    {
        $q = $this->goodsRepository->getEm()->createQueryBuilder();
        $q->select("p.image_src")->from(GoodsImage::class, "p")
            ->where("p.deleted=0 and p.goods_id=:goods_id")->setParameter("goods_id", $id)
            ->orderBy("p.id", "ASC");
        $list = $q->getQuery()->getArrayResult();
        if (empty($list)) {
            return [];
        }
        return array_column($list, 'image_src');
    }

    public function helpContentList(int $id)
    {
        $q = $this->goodsRepository->getEm()->createQueryBuilder();
        $q->select("p.content,p.type")->from(GoodsContent::class, "p")
            ->where("p.goods_id=:goods_id")->setParameter("goods_id", $id)
            ->orderBy("p.id", "ASC");
        $list = $q->getQuery()->getArrayResult();
        if (empty($list)) {
            return ['content1' => '', 'content2' => '', 'content3' => ''];
        }
        $list = array_column($list, null, 'type');
        return ['content1' => $list[0]['content'] ?? '', 'content2' => $list[1]['content'] ?? '', 'content3' => $list[2]['content'] ?? ''];
    }

    public function helpCreateNo($id = 0)
    {
        if (empty($id)) {
            return '';
        }
        return "F" . str_pad($id, 5, "0", STR_PAD_LEFT);
    }

    public function createGoods()
    {
        $time = time();
        $model = new Goods();
        $model->setTitle($this->title);
        $model->setCateId($this->cate_id);
        $model->setIntro($this->intro);
        $model->setBrand($this->brand);
        $model->setStore($this->store);
        $model->setIsOnSale($this->is_on_sale);
        $model->setBuyLimit($this->buy_limit);
        $model->setPrice($this->price);
        $model->setRealPrice($this->real_price);
        $model->setQuantity($this->quantity);
        $model->setTotalQuantity($this->quantity);
        $model->setIsCart($this->is_cart);
        $model->setIsSearch($this->is_search);
        $model->setIsTest($this->is_test);
        //默认填充
        $model->setType(1);
        $model->setGoodsSn($this->helpCreateNo());
        $model->setSortBy(100);
        $model->setEffectType(0);
        $model->setOnTime(0);
        $model->setOffTime(0);
        $model->setReturnDay(7);
        $model->setTotalSales(0);
        $model->setTotalViews(0);
        $model->setCreateAt($time);
        $model->setUpdateAt($time);
        $model->setDeleted(0);
        $em = $this->goodsRepository->getEm();
        $em->getConnection()->beginTransaction();
        try {
            $this->goodsRepository->add($model);
            $model->setGoodsSn($this->helpCreateNo($model->getId()));
            $this->goodsRepository->add($model);
            //add image and add content
            if (!empty($this->images)) {
                $isDef = 1;
                foreach ($this->images as $img) {
                    $goodsImage = new GoodsImage();
                    $goodsImage->setGoodsId($model->getId());
                    $goodsImage->setType(0);
                    $goodsImage->setImageSrc($img);
                    $goodsImage->setIsDefault($isDef);
                    $goodsImage->setCreateAt($time);
                    $goodsImage->setUpdateAt($time);
                    $goodsImage->setDeleted(0);
                    $em->persist($goodsImage);
                    $em->flush();
                    $isDef = 0;
                }
            }
            if (!empty($this->content1)) {
                $goodsContent = new GoodsContent();
                $goodsContent->setGoodsId($model->getId());
                $goodsContent->setType(0);
                $goodsContent->setContent($this->content1);
                $em->persist($goodsContent);
                $em->flush();
            }
            if (!empty($this->content2)) {
                $goodsContent = new GoodsContent();
                $goodsContent->setGoodsId($model->getId());
                $goodsContent->setType(1);
                $goodsContent->setContent($this->content2);
                $em->persist($goodsContent);
                $em->flush();
            }
            if (!empty($this->content3)) {
                $goodsContent = new GoodsContent();
                $goodsContent->setGoodsId($model->getId());
                $goodsContent->setType(2);
                $goodsContent->setContent($this->content3);
                $em->persist($goodsContent);
                $em->flush();
            }
            $em->getConnection()->commit();
        } catch (\Exception $ex) {
            $em->getConnection()->rollBack();
            throw $ex;
        }
        return true;
    }

    public function updateGoods(Goods $model)
    {
        $time = time();
        $model->setTitle($this->title);
        $model->setCateId($this->cate_id);
        $model->setIntro($this->intro);
        $model->setBrand($this->brand);
        $model->setStore($this->store);
        $model->setIsOnSale($this->is_on_sale);
        $model->setBuyLimit($this->buy_limit);
        $model->setPrice($this->price);
        $model->setRealPrice($this->real_price);
        $model->setIsCart($this->is_cart);
        $model->setIsSearch($this->is_search);
        $model->setIsTest($this->is_test);
        //默认填充
//        $model->setSortBy(100);
//        $model->setEffectType(0);
//        $model->setOnTime(0);
//        $model->setOffTime(0);
//        $model->setReturnDay(7);
        $model->setUpdateAt($time);
        $em = $this->goodsRepository->getEm();
        $em->getConnection()->beginTransaction();
        try {
            $this->goodsRepository->add($model);
            //add image and add content
            $q = $this->goodsRepository->getEm()->createQueryBuilder();
            $q->select("p")->from(GoodsImage::class, "p")
                ->where("p.deleted=0 and p.goods_id=:goods_id")->setParameter("goods_id", $model->getId())
                ->orderBy("p.id", "ASC");
            $list = $q->getQuery()->getResult();
            if (!empty($this->images)) {
                $isDef = 1;
                foreach ($this->images as $img) {
                    $goodsImage = array_shift($list);
                    if (empty($goodsImage)) {
                        $goodsImage = new GoodsImage();
                        $goodsImage->setGoodsId($model->getId());
                        $goodsImage->setType(0);
                        $goodsImage->setCreateAt($time);
                        $goodsImage->setDeleted(0);
                    }
                    $goodsImage->setIsDefault($isDef);
                    $goodsImage->setImageSrc($img);
                    $goodsImage->setUpdateAt($time);
                    $em->persist($goodsImage);
                    $em->flush();
                    $isDef = 0;
                }
            }
            if (!empty($list)) {
                foreach ($list as $img) {
                    $img->setDeleted(1);
                    $em->persist($img);
                    $em->flush();
                }
            }
            $q = $this->goodsRepository->getEm()->createQueryBuilder();
            $q->select("p")->from(GoodsContent::class, "p")
                ->where("p.goods_id=:goods_id")->setParameter("goods_id", $model->getId())
                ->orderBy("p.id", "ASC");
            $list = $q->getQuery()->getResult();

            $tmp = [];
            foreach ($list as $content) {
                $tmp[$content->getType()] = $content;
            }

            if (!empty($this->content1)) {
                if (isset($tmp[0])) {
                    $goodsContent = $tmp[0];
                } else {
                    $goodsContent = new GoodsContent();
                    $goodsContent->setGoodsId($model->getId());
                    $goodsContent->setType(0);
                }
                $goodsContent->setContent($this->content1);
                $em->persist($goodsContent);
                $em->flush();
            }
            if (!empty($this->content2)) {
                if (isset($tmp[1])) {
                    $goodsContent = $tmp[1];
                } else {
                    $goodsContent = new GoodsContent();
                    $goodsContent->setGoodsId($model->getId());
                    $goodsContent->setType(1);
                }
                $goodsContent->setContent($this->content2);
                $em->persist($goodsContent);
                $em->flush();
            }
            if (!empty($this->content3)) {
                if (isset($tmp[2])) {
                    $goodsContent = $tmp[2];
                } else {
                    $goodsContent = new GoodsContent();
                    $goodsContent->setGoodsId($model->getId());
                    $goodsContent->setType(2);
                }
                $goodsContent->setContent($this->content3);
                $em->persist($goodsContent);
                $em->flush();
            }
            $em->getConnection()->commit();
        } catch (\Exception $ex) {
            $em->getConnection()->rollBack();
            throw $ex;
        }
        return true;
    }
}
