<?php

namespace App\Acme\FrontendBundle\src\Form;

use App\Entity\Goods;
use App\Entity\GoodsCate;
use App\Entity\GoodsContent;
use App\Entity\GoodsImage;
use App\Repository\GoodsRepository;

class GoodsForm
{
    private GoodsRepository $goodsRepository;

    public function __construct(GoodsRepository $goodsRepository)
    {
        $this->goodsRepository = $goodsRepository;
    }


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
        $q->select("p.image_src,p.is_default")->from(GoodsImage::class, "p")
            ->where("p.deleted=0 and p.goods_id=:goods_id")->setParameter("goods_id", $id)
            ->orderBy("p.id", "ASC");
        $list = $q->getQuery()->getArrayResult();
        if (empty($list)) {
            return [];
        }
        return $list;
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

    public function helpGoodsTop($start=0,$length=5){
        $select='p.id,p.title,p.price,p.real_price';
        $q = $this->goodsRepository->createQueryBuilder('p')
            ->select($select)
            ->where("p.deleted=0 and p.is_on_sale = 1 and p.is_search=1");
        $q->setMaxResults($length)->setFirstResult($start)
            ->orderBy('p.id', 'DESC');
        $list = $q->getQuery()->getArrayResult();
        if(!empty($list)){
            $images = $this->getImages(array_column($list,'id'));
            $images=array_column($images,null,'goods_id');
            foreach($list as &$val){
                $val['logo']=isset($images[$val['id']]) ? $images[$val['id']]['image_src'] : '';
            }
            unset($val);
        }
        return $list;
    }

    public function getImages(array $ids)
    {
        if(empty($ids)){
            return [];
        }
        $q = $this->goodsRepository->getEm()->createQueryBuilder()
            ->select("p.goods_id,p.image_src")
            ->from(GoodsImage::class, "p")
            ->where('p.deleted=0 and p.is_default=1 and p.goods_id in (:ids)')
            ->setParameter("ids",$ids);
        return $q->getQuery()->getArrayResult();
    }

    public function getListByIds(array $ids){
        $select='p.id,p.title,p.price,p.real_price,p.is_on_sale,p.quantity';
        $q = $this->goodsRepository->createQueryBuilder('p')->select($select)
            ->where('p.deleted=0 and p.id in(:ids) ')
            ->setParameter("ids", $ids);
        $list = $q->getQuery()->getArrayResult();
        if(!empty($list)){
            $images = $this->getImages(array_column($list,'id'));
            $images=array_column($images,null,'goods_id');
            foreach($list as &$val){
                $val['logo']=isset($images[$val['id']]) ? $images[$val['id']]['image_src'] : '';
            }
            unset($val);
        }
        return $list;
    }

}
