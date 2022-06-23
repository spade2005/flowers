<?php

namespace App\Acme\FrontendBundle\src\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\GoodsRepository;
use App\Entity\GoodsCate;
use App\Entity\GoodsImage;
use App\Entity\Goods;


#[Route('/mall')]
class MallController extends AbstractController
{
    #[Route('/', name: 'mall_app_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('mall/index.html.twig', [
        ]);
    }

    #[Route('/cate', name: 'mall_app_cate', methods: ['GET'])]
    public function cate(Request $request,GoodsRepository $goodsRepository): Response
    {
        $cid=$request->get('cid');
        $start = $request->get('start', 0);
        $length = $request->get('length', 20);
        $q = $goodsRepository->getEm()->createQueryBuilder()
            ->select("p.id,p.title,p.mark")
            ->from(GoodsCate::class, "p")
            ->where("p.deleted=0 and p.parent_id=0")
            ->orderBy('p.id', 'ASC');
        $cateList = $q->getQuery()->getArrayResult();
        $cate=['id'=>0];
        if(!empty($cateList)){
            if(empty($cid)){
                $cate=$cateList[0];
            }else{
                foreach ($cateList as $cat){
                    if($cat['id']==$cid){
                        $cate=$cat;
                        break;
                    }
                }
            }
        }
        $select='p.id,p.title,p.price,p.real_price';
        $q = $goodsRepository->createQueryBuilder('p')
            ->select($select)
            ->where("p.deleted=0 and p.is_on_sale = 1 and p.is_search=1");
        if(!empty($cid)){
            $q->where('p.cate_id=:cate_id')->setParameter('cate_id', $cid);
        }
        $q->setMaxResults($length)->setFirstResult($start)
            ->orderBy('p.id', 'DESC');
        $list = $q->getQuery()->getArrayResult();
        if(!empty($list)){
            $images = $this->getImages(array_column($list,'id'),$goodsRepository);
            $images=array_column($images,null,'goods_id');
            foreach($list as &$val){
                $val['logo']=isset($images[$val['id']]) ? $images[$val['id']]['image_src'] : '';
            }
            unset($val);
            $list=array_chunk($list, 4);
            $tmp=end($list);
            if(count($tmp)!=4){
                $tmp=range(1, 4-count($tmp));
                foreach($tmp as $val){
                    $list[count($list)-1][]=['id'=>0];
                }
            }
        }
        return $this->render('mall/cate/index.html.twig', [
            'cateList'=>$cateList,'goodsList'=>$list,'cate'=>$cate
        ]);
    }

    public function getImages(array $ids,GoodsRepository $goodsRepository)
    {
        if(empty($ids)){
            return [];
        }
        $q = $goodsRepository->getEm()->createQueryBuilder()
            ->select("p.goods_id,p.image_src")
            ->from(GoodsImage::class, "p")
            ->where('p.deleted=0 and p.is_default=1 and p.goods_id in (:ids)')
            ->setParameter("ids",$ids);
        return $q->getQuery()->getArrayResult();
    }
}
