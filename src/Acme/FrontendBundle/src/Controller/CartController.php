<?php

namespace App\Acme\FrontendBundle\src\Controller;

use App\Acme\FrontendBundle\src\Form\GoodsForm;
use App\Acme\FrontendBundle\src\Form\OrderForm;
use App\Entity\MemberCart;
use App\Repository\GoodsRepository;
use App\Repository\MemberCartRepository;
use App\Repository\MemberRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/mall/cart')]
class CartController extends AbstractController
{
    public RequestStack $requestStack;
    public MemberCartRepository $memberCartRepository;

    public function __construct(RequestStack $requestStack, MemberCartRepository $memberCartRepository)
    {
        $this->requestStack = $requestStack;
        $this->memberCartRepository = $memberCartRepository;
    }

    #[Route('/', name: 'mall_app_cart_index', methods: ['GET', 'POST'])]
    public function index(Request $request, GoodsRepository $goodsRepository): Response
    {
        $member = $this->requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $q = $this->memberCartRepository->createQueryBuilder('p')
            ->select(['p.id', 'p.goods_id', 'p.goods_number'])
            ->where('p.deleted=0 and p.member_id=:member_id and p.status=0 ')
            ->setParameter("member_id", $member->getId());
        $cartList = $q->getQuery()->getArrayResult();
        if (!empty($cartList)) {
            $gids = array_column($cartList, 'goods_id');
            $goodsForm = new GoodsForm($goodsRepository);
            $goodsList = $goodsForm->getListByIds($gids);
            $goodsList = array_column($goodsList, null, 'id');
            foreach ($cartList as &$cart) {
                if (isset($goodsList[$cart['goods_id']])) {
                    $cart['goods'] = $goodsList[$cart['goods_id']];
                    $cart['amount'] = $cart['goods_number'] * $cart['goods']['real_price'];
                }
            }
            unset($cart);
        }
        return $this->render('mall/cart/index.html.twig', [
            'list' => $cartList
        ]);
    }


    #[Route('/change', name: 'mall_app_cart_change', methods: ['POST'])]
    public function change(Request $request, GoodsRepository $goodsRepository): Response
    {
        $member = $this->requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->json(['code' => 1, 'message' => '请先登录']);
        }
        $id = $request->get('id');
        $num = $request->get('num');

        if (empty($id) || empty($num)) {
            return $this->json(['code' => 1, 'message' => '请求参数不合法']);
        }
        $q = $this->memberCartRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.member_id=:member_id and p.id=:id and p.status=0")
            ->setParameter('member_id', $member->getId())
            ->setParameter('id', $id);
        $model = $q->getQuery()->getOneOrNullResult();
        if(empty($model)){
            return $this->json(['code' => 1, 'message' => '请求内容不合法']);
        }

        $q = $goodsRepository->createQueryBuilder('p')
            ->select(['p.id', 'p.quantity','p.is_on_sale'])
            ->where('p.deleted=0 and p.id=:id ')
            ->setParameter("id", $model->getGoodsId());
        $goods = $q->getQuery()->getOneOrNullResult();
        if (empty($goods)) {
            return $this->json(['code' => 1, 'message' => '请求商品不合法']);
        }
        if ($goods['quantity'] < $num) {
            return $this->json(['code' => 1, 'message' => '商品库存不足']);
        }
        if ($goods['is_on_sale'] ==0) {
            return $this->json(['code' => 1, 'message' => '商品已下架']);
        }

        $time = time();
        $model->setGoodsNumber($num);
        $model->setUpdateAt($time);
        $this->memberCartRepository->add($model);
        return $this->json(['code' => 0, 'message' => 'success']);
    }

    #[Route('/check', name: 'mall_app_cart_check', methods: ['POST'])]
    public function check(Request $request, GoodsRepository $goodsRepository): Response
    {
        $member = $this->requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->json(['code' => 1, 'message' => '请先登录']);
        }
        $ids = $request->get('id');

        if (empty($ids)) {
            return $this->json(['code' => 1, 'message' => '请求参数不合法']);
        }
        $q = $this->memberCartRepository->createQueryBuilder('p')
            ->select(["p.goods_id","p.goods_number"])
            ->where("p.deleted=0 and p.member_id=:member_id and p.id in(:id) and p.status=0")
            ->setParameter('member_id', $member->getId())
            ->setParameter('id', $ids);
        $list = $q->getQuery()->getResult();
        if(empty($list)){
            return $this->json(['code' => 1, 'message' => '请求内容不合法']);
        }

        $q = $goodsRepository->createQueryBuilder('p')
            ->select(['p.id', 'p.quantity','p.real_price'])
            ->where('p.deleted=0 and p.id  in(:id) ')
            ->setParameter("id", array_column($list,"goods_id"));
        $goods = $q->getQuery()->getResult();
        if (empty($goods)) {
            return $this->json(['code' => 1, 'message' => '请求商品不合法']);
        }
        $goods=array_column($goods,null,'id');
        $amount=0.00;
        foreach($list as $val){
            $gid=$val['goods_id'];
            $num=$val['goods_number'];
            if(isset($goods[$gid])){
                $amount = bcadd($amount, bcmul($goods[$gid]['real_price'], $num));
            }
        }
        $amount=sprintf("%0.2f",$amount);
        return $this->json(['code' => 0, 'message' => 'success','data'=>compact('amount')]);
    }


    #[Route('/new', name: 'mall_app_cart_new', methods: ['POST'])]
    public function new(Request $request, GoodsRepository $goodsRepository): Response
    {
        $member = $this->requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->json(['code' => 1, 'message' => '请先登录']);
        }
        $gid = $request->get('gid');
        $num = $request->get('num');

        if (empty($gid) || empty($num)) {
            return $this->json(['code' => 1, 'message' => '请求参数不合法']);
        }
        $q = $goodsRepository->createQueryBuilder('p')
            ->select(['p.id', 'p.quantity'])
            ->where('p.deleted=0 and p.id=:id ')
            ->setParameter("id", $gid);
        $goods = $q->getQuery()->getOneOrNullResult();
        if (empty($goods)) {
            return $this->json(['code' => 1, 'message' => '请求商品不合法']);
        }
        if ($goods['quantity'] < $num) {
            return $this->json(['code' => 1, 'message' => '商品库存不足']);
        }
        $q = $this->memberCartRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.member_id=:member_id and p.goods_id=:goods_id and p.status=0")
            ->setParameter('member_id', $member->getId())
            ->setParameter('goods_id', $gid);
        $model = $q->getQuery()->getOneOrNullResult();
        $time = time();
        if (empty($model)) {
            $model = new MemberCart();
            $model->setMemberId($member->getId());
            $model->setGoodsId($gid);
            $model->setGoodsNumber(0);
            $model->setStatus(0);
            $model->setOrderId(0);
            $model->setCreateAt($time);
            $model->setDeleted(0);
        }
        $model->setGoodsNumber($model->getGoodsNumber() + $num);
        $model->setUpdateAt($time);
        $this->memberCartRepository->add($model);
        return $this->json(['code' => 0, 'message' => '加入购物车成功.']);
    }


    #[Route('/del', name: 'mall_app_cart_del', methods: ['POST'])]
    public function del(Request $request, GoodsRepository $goodsRepository): Response
    {
        $member = $this->requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->json(['code' => 1, 'message' => '请先登录']);
        }
        $id = $request->get('id');
        if (empty($id)) {
            return $this->json(['code' => 1, 'message' => '请求参数不合法']);
        }
        $q = $this->memberCartRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.member_id=:member_id and p.id=:id and p.status=0")
            ->setParameter('member_id', $member->getId())
            ->setParameter('id', $id);
        $model = $q->getQuery()->getOneOrNullResult();
        if(empty($model)){
            return $this->json(['code' => 1, 'message' => '请求内容不合法']);
        }

        $time = time();
        $model->setDeleted(1);
        $model->setUpdateAt($time);
        $this->memberCartRepository->add($model);
        return $this->json(['code' => 0, 'message' => 'success']);
    }

}
