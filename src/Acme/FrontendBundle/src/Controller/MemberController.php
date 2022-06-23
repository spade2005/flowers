<?php

namespace App\Acme\FrontendBundle\src\Controller;

use App\Acme\FrontendBundle\src\Form\OrderForm;
use App\Repository\MemberRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/mall/member')]
class MemberController extends AbstractController
{
    public RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/', name: 'mall_app_member_index', methods: ['GET', 'POST'])]
    public function index(Request $request, MemberRepository $memberRepository): Response
    {
        $member = $this->requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $q = $memberRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.id=:id")
            ->setParameter('id', $member->getId());
        $model = $q->getQuery()->getOneOrNullResult();
        if ($request->getMethod() == 'GET') {
            return $this->render('mall/member/index.html.twig', [
                'member' => $model
            ]);
        }
        $head_img = $request->get('head_img');
        $real_name = $request->get('real_name');
        $sex = $request->get('sex');
        $time = time();

        $model->setHeadImg($head_img);
        $model->setRealName($real_name);
        $model->setSex(intval($sex));
        $model->setUpdateAt(strval($time));
        $memberRepository->add($model);
        return $this->json(['code' => 0, 'message' => 'success']);
    }

    #[Route('/password', name: 'mall_app_member_password', methods: ['GET', 'POST'])]
    public function password(Request $request, MemberRepository $memberRepository): Response
    {
        $member = $this->requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $q = $memberRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.id=:id")
            ->setParameter('id', $member->getId());
        $model = $q->getQuery()->getOneOrNullResult();
        if ($request->getMethod() == 'GET') {
            return $this->render('mall/member/password.html.twig', [
                'member' => $model
            ]);
        }
        $userpass = $request->get('userpass');
        $newpass = $request->get('newpass');
        $newpass2 = $request->get('newpass2');
        $time = time();
        if (!password_verify($userpass, $model->getPassword())) {
            return $this->json(['code' => 1, 'message' => '原密码不正确']);
        }
        if (mb_strlen($newpass) < 4 || mb_strlen($newpass) > 20) {
            return $this->json(['code' => 1, 'message' => '新密码不合法']);
        }
        if ($newpass != $newpass2) {
            return $this->json(['code' => 1, 'message' => '两次密码不一致']);
        }

        $model->setPassword(password_hash($newpass, PASSWORD_DEFAULT));
        $model->setUpdateAt(strval($time));
        $memberRepository->add($model);
        //注销当前
        $this->requestStack->getSession()->set("auth_user",null);
        return $this->json(['code' => 0, 'message' => 'success']);
    }

    #[Route('/order', name: 'mall_app_member_order', methods: ['GET', 'POST'])]
    public function order(Request $request, OrderRepository $orderRepository): Response
    {
        $member = $this->requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $orderNo = $request->get('order_no');
        $orderStatus = $request->get('order_status');
        $startDay = $request->get('start_day');
        $finishDay = $request->get('finish_day');

        $start <= 0 && $start = 0;
        $length <= 0 && $length = 10;
        $length >= 20 && $length = 20;

        $vform = new OrderForm($orderRepository, $member);
        $data = compact('start', 'length',
            'orderNo', 'orderStatus', 'startDay', 'finishDay'
        );
        list($count, $list) = $vform->getOrderList($data);
        $page = empty($start) ? 1 : ($start/2)+1;
        $maxPage=ceil($count/$length);
        $data['start']= $start - $length;
        $prevUrl='/mall/member/order?'.http_build_query($data);
        $data['start']=$start + $length;
        $nextUrl='/mall/member/order?'.http_build_query($data);
        $ret=compact('list','count','orderNo','orderStatus','startDay','finishDay'
            ,'page','maxPage','prevUrl','nextUrl'
        );
        return $this->render('mall/member/order.html.twig',$ret);
    }

    #[Route('/order-detail', name: 'mall_app_member_order_detail', methods: ['GET', 'POST'])]
    public function orderDetail(Request $request, OrderRepository $orderRepository): Response
    {
        $member = $this->requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $id = $request->get('id', 0);
        $vform = new OrderForm($orderRepository, $member);

        $model = $vform->getOrderByNumber($id);
        $order = [];
        if (empty($model)) {
            return $this->redirect("/mall/member/order");
        } else {
            if (!empty($model)) {
                $order['goods'] = array_values($vform->_getOrderGoods($model->getId()))[0];
                $order['goods_count'] = count($order['goods']);
                $order['createAt'] = $model->getCreateAt();
                $order['status'] = $vform->_getOrderStatus($model->getOrderStatus());
                $order['member'] = $vform->_getMember($model->getMemberId());
            }
        }
        return $this->render('mall/member/order_detail.html.twig', [
            'order' => $order, 'model' => $model
        ]);
    }


}
