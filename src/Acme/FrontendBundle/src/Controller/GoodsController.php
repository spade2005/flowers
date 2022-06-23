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
use App\Acme\FrontendBundle\src\Form\GoodsForm;


#[Route('/mall/goods')]
class GoodsController extends AbstractController
{
    #[Route('/{id}', name: 'mall_app_goods_show',requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Goods $model,GoodsRepository $goodsRepository): Response
    {
        $start=0;
        $vform = new GoodsForm($goodsRepository);
        $extends=[
            'images'=>$vform->helpImageList($model->getId()),'content'=>$vform->helpContentList($model->getId()),
            'topList'=>$vform->helpGoodsTop($start)
        ];

        return $this->render('mall/goods/show.html.twig', [
            'goods' => $model,'extends'=>$extends
        ]);
    }


}
