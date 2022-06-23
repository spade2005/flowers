<?php

namespace App\Acme\FrontendBundle\src\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\OrderRepository;
use App\Entity\GoodsCate;
use App\Entity\GoodsImage;
use App\Entity\Goods;
use App\Acme\FrontendBundle\src\Form\OrderForm;


#[Route('/mall/order')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'mall_app_order_index', methods: ['GET'])]
    public function index(Request $request, OrderRepository $orderRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $vform = new OrderForm($orderRepository, $member);
        $gid = $request->get('gid');
        $num = $request->get('num');
        //订单开始
        $goods = $vform->checkOrderGoods($gid, $num);
        if (!empty($goods)) {
            $addrList = $vform->getAddress();
            $shippingAmount=10;
            $amount=$shippingAmount;
            $amount = bcadd($amount, $goods['amount']);
            $amount=sprintf("%0.2f",$amount);
            $data = [
                'goods' => [$goods],
                'addrList' => $addrList, 'gid' => $gid, 'num' => $num,
                'amount'=>$amount,'shippingAmount'=>$shippingAmount
            ];
            return $this->render('mall/order/index.html.twig', $data);
        }
        $data = [
            'error' => $vform->getError()
        ];
        return $this->render('mall/order/error.html.twig', $data);
    }


    #[Route('/submit', name: 'mall_app_order_submit', methods: ['POST'])]
    public function submit(Request $request, OrderRepository $orderRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->json(['code' => 1, 'message' => "请登录后再操作"]);
        }
        $vform = new OrderForm($orderRepository, $member);
        $gid = $request->get('gid');
        $num = $request->get('num');
        $addr_id = $request->get('addr_id');
        $payMethod = $request->get('pay_method');
        $mark = $request->get('mark', '');
        if(empty($gid) || empty($num) || $num<=0 || empty($addr_id) || $gid<=0 || $addr_id<=0){
            return $this->json(['code' => 1, 'message' => '提交参数不合法。请检查']);
        }
        //订单开始
        $goods = $vform->checkOrderGoods($gid, $num);
        if (empty($goods)) {
            return $this->json(['code' => 1, 'message' => $vform->getError()]);
        }

        $addrList = $vform->getAddress($addr_id);
        if (empty($addrList[0])) {
            return $this->json(['code' => 1, 'message' => '收货人信息选择错误']);
        }
        $addr = $addrList[0];
        $data = [
            'mark' => $mark, 'addr_id' => $addr['id'], 'addr' => $addr,
            'addr_mark' => '', 'order_from' => '', 'goods' => [$goods],
        ];
        if ($goods['is_test']) {
            $data['order_from'] = 'test';
        }
        try {
            $no = $vform->createOrder($data);
        } catch (\Exception $e) {
            return $this->json(['code' => 1, 'message' => '下单失败:', 'desc' => $e->getMessage()]);
        }
        return $this->json(['code' => 0, 'message' => 'success', 'data' => ['no' => $no]]);
    }


    #[Route('/pay', name: 'mall_app_order_pay', methods: ['GET'])]
    public function pay(Request $request, OrderRepository $orderRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $vform = new OrderForm($orderRepository, $member);
        $no = $request->get('id');
        $order = $vform->getOrderByNumber($no,1);
        if (empty($order)) {
            $data = [
                'error' => $vform->getError()
            ];
            return $this->render('mall/order/error.html.twig', $data);
        }
        $data = [
            'order' => $order
        ];
        return $this->render('mall/order/pay.html.twig', $data);

    }

    #[Route('/success', name: 'mall_app_order_success', methods: ['GET'])]
    public function success(Request $request, OrderRepository $orderRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $vform = new OrderForm($orderRepository, $member);
        $no = $request->get('id');
        $order = $vform->getOrderByNumber($no,1);
        if (empty($order)) {
            $data = [
                'error' => $vform->getError()
            ];
            return $this->render('mall/order/error.html.twig', $data);
        }
        //hack
        if ($order->getOrderFrom() == 'test' && $order->getPayStatus() == '0') {
            $order->setOrderStatus(2);
            $order->setPayStatus(1);
            $order->setPayTime((string)time());
            $orderRepository->add($order);
        }
        //hack end
        if ($order->getPayStatus() == '0') {
            $data = [
                'error' => '请尽快支付订单',
                'title' => '订单信息提示',
                'href' => '/mall/member/order',
                'hrefTitle' => '查看订单'
            ];
            return $this->render('mall/order/error.html.twig', $data);
        }
        $data = [
            'order' => $order
        ];
        return $this->render('mall/order/success.html.twig', $data);

    }



    #[Route('/cart', name: 'mall_app_order_cart', methods: ['GET'])]
    public function cart(Request $request, OrderRepository $orderRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $vform = new OrderForm($orderRepository, $member);
        $id = $request->get('id');
        $ids = !empty($id) ? explode(",",$id) : [];
        if(empty($ids)){
             $data = [
                'error' => '请求参数不合法'
            ];
            return $this->render('mall/order/error.html.twig', $data);
        }

        $goods = $vform->checkCartGoods($ids);
        if (!empty($goods)) {
            $shippingAmount=10;
            $amount=$shippingAmount;
            foreach ($goods as $val){
                $amount = bcadd($amount, $val['amount']);
            }
            $amount=sprintf("%0.2f",$amount);
            $addrList = $vform->getAddress();
            $data = compact('goods','addrList','amount','shippingAmount','id');
            return $this->render('mall/order/cart.html.twig', $data);
        }
        $data = [
            'error' => $vform->getError()
        ];
        return $this->render('mall/order/error.html.twig', $data);
    }

    #[Route('/cart-submit', name: 'mall_app_order_cart_submit', methods: ['POST'])]
    public function cartSubmit(Request $request, OrderRepository $orderRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->json(['code' => 1, 'message' => "请登录后再操作"]);
        }
        $vform = new OrderForm($orderRepository, $member);
        $id = $request->get('id');
        $addr_id = $request->get('addr_id');
        $payMethod = $request->get('pay_method');
        $mark = $request->get('mark', '');
        $ids = !empty($id) ? explode(",",$id) : [];
        if(empty($ids)){
             $data = [
                'error' => '请求参数不合法'
            ];
            return $this->render('mall/order/error.html.twig', $data);
        }

        $goods = $vform->checkCartGoods($ids);
        if (empty($goods)) {
            return $this->json(['code' => 1, 'message' => $vform->getError()]);
        }
        $shippingAmount=10;
        $amount=$shippingAmount;
        foreach ($goods as $val){
            $amount = bcadd($amount, $val['amount']);
        }
        $amount=sprintf("%0.2f",$amount);
        //订单开始
        $addrList = $vform->getAddress($addr_id);
        if (empty($addrList[0])) {
            return $this->json(['code' => 1, 'message' => '收货人信息选择错误']);
        }
        $addr = $addrList[0];
        $data = [
            'ids'=>$ids,'is_cart'=>true,
            'mark' => $mark, 'addr_id' => $addr['id'], 'addr' => $addr,
            'addr_mark' => '', 'order_from' => 'cart', 'goods' => $goods,
        ];
        $f=true;
        foreach($goods as $val){
            if (!$val['is_test']) {
                $f=false;
            }
        }
        $f && $data['order_from'] = 'test';

        try {
            $no = $vform->createOrder($data);
        } catch (\Exception $e) {
            return $this->json(['code' => 1, 'message' => '下单失败:', 'desc' => $e->getMessage()]);
        }
        return $this->json(['code' => 0, 'message' => 'success', 'data' => ['no' => $no]]);
    }

    #[Route('/close', name: 'mall_app_order_close', methods: ['POST'])]
    public function close(Request $request, OrderRepository $orderRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->json(['code' => 1, 'message' => "请登录后再操作"]);
        }
        $id = $request->get('id');
        $vform = new OrderForm($orderRepository, $member);
        $order = $vform->getOrderByNumber($id);
        if (empty($order)) {
            return $this->json(['code' => 1, 'message' => $vform->getError()]);
        }
        if($order->getOrderStatus()!=1){
            return $this->json(['code' => 1, 'message' => '订单状态不合法']);
        }
        try {
            $order->setOrderStatus(9);
            $order->setUpdateAt(time());
            $orderRepository->add($order);
        } catch (\Exception $e) {
            return $this->json(['code' => 1, 'message' => '操作失败:', 'desc' => $e->getMessage()]);
        }
        return $this->json(['code' => 0, 'message' => 'success']);
    }

}
