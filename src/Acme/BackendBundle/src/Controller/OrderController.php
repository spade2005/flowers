<?php

namespace App\Acme\BackendBundle\src\Controller;

use App\Acme\BackendBundle\src\Form\OrderForm;
use App\Entity\Order;
use App\Entity\OrderGoods;
use App\Entity\OrderLog;
use App\Entity\Refund;
use App\Entity\RefundLog;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @IsGranted("ROLE_USER")
 */
#[Route('/admin/order')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'admin_app_order_index', methods: ['GET'])]
    public function index(Request $request, OrderRepository $orderRepository): Response
    {
        $vfrom = new OrderForm($orderRepository);

        if (!$request->isXmlHttpRequest()) {
            return $this->render('admin/order/index.html.twig', [
                'showTable' => true
            ]);
        }
        $orderNo = $request->get('order_no');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $startDay = $request->get('start_day');
        $finishDay = $request->get('finish_day');
        $userName = $request->get('user_name');
        $orderStatus = $request->get('order_status');

        $data = compact('orderNo', 'start', 'length', 'startDay', 'finishDay', 'userName', 'orderStatus');
        list($count, $list) = $vfrom->getList($data);
        $ret = [
            'data' => $list,
            'iTotalRecords' => count($list),//显示数
            'iTotalDisplayRecords' => $count,//总数
        ];
        return $this->json($ret);
    }


    #[Route('/{id}', name: 'admin_app_order_show', methods: ['GET'])]
    public function show(OrderRepository $orderRepository, Order $model): Response
    {
        $vfrom = new OrderForm($orderRepository);
        $goodsList = $vfrom->getOrderGoods($model->getId());
        $member['name'] = $vfrom->getMember($model->getMemberId());
        $member['level'] = $vfrom->getLevelStr($model->getMemberLevel());
        return $this->render('admin/order/edit.html.twig', [
            'model' => $model, 'goodsList' => current($goodsList), 'member' => $member,
            'logList'=>$vfrom->getOrderLog($model->getId())
        ]);
    }

    #[Route('/{id}', name: 'admin_app_order_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Order $model, OrderRepository $orderRepository): Response
    {
        if (!empty($model) && $model->getId()) {
            $model->setDeleted(1);
            $orderRepository->add($model);
        }

        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}/close', name: 'admin_app_order_close', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function close(Request $request, Order $model, OrderRepository $orderRepository): Response
    {
        if (!empty($model) && $model->getId()) {
            if ($model->getOrderStatus() != 1) {
                $this->json(['code' => 1, 'message' => '订单已支付，无法关闭']);
            }
            $orderMark = $request->get('order_mark');
            $addrMark = $request->get('addr_mark');

            $time = time();
            $model->setOrderMark($orderMark);
            $model->setAddrMark($addrMark);
            $model->setOrderStatus(9);
            $model->setUpdateAt($time);
            $orderRepository->add($model);
            //add log
            $log = new OrderLog();
            $log->setOrderId($model->getId());
            $log->setType(1);
            $log->setMessage("管理员关闭订单(" . $model->getOrderNo() . ")");
            $log->setCreateBy($this->getUser()->getUsername());
            $log->setCreateAt($time);
            $log->setUpdateAt($time);
            $log->setDeleted(0);
            $orderRepository->getEm()->persist($log);
            $orderRepository->getEm()->flush();
        }
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}/shipping', name: 'admin_app_order_shipping', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function shipping(Request $request, Order $model, OrderRepository $orderRepository): Response
    {
        if (!empty($model) && $model->getId()) {
            if (!in_array($model->getOrderStatus(), [2, 3])) {
                $this->json(['code' => 1, 'message' => '订单不需要发货']);
            }
            if ($model->getShippingWay(true) != 1) {
                $this->json(['code' => 1, 'message' => '订单为自提。不需要发货']);
            }
            $orderMark = $request->get('order_mark');
            $addrMark = $request->get('addr_mark');
            $shippingId = $request->get('shipping_id');
            $shippingNo = $request->get('shipping_no');
            if (empty($shipping_id)) {
                $this->json(['code' => 1, 'message' => '提交参数不合法.']);
            }
            $conn = $orderRepository->getEm()->getConnection();
            $conn->beginTransaction();
            try {
                $time = time();
                $model->setOrderMark($orderMark);
                $model->setAddrMark($addrMark);
                $model->setOrderStatus(3);
                $model->setShippingStatus(1);//1发，2部分发，直接看goods
                $model->setShippingTime($time);
                $model->setUpdateAt($time);
                $orderRepository->add($model);
                foreach ($shippingId as $key => $id) {
                    if (empty($shippingNo[$key])) {
                        throw  new \Exception("提交发货参数不合法");
                    }
                    $q = $orderRepository->getEm()->createQueryBuilder();
                    $q->select("p")->from(OrderGoods::class, "p")
                        ->where("p.id =:id and p.deleted=0")->setParameter("id", $id);
                    $orderGoods = $q->getQuery()->getSingleResult();
                    if (empty($orderGoods)) {
                        throw new \Exception("提交发货参数不合法.not found detail");
                    }
                    $orderGoods->setShippingStatus(1);
                    $orderGoods->setShippingNo($shippingNo[$key]);
                    $orderRepository->getEm()->persist($orderGoods);
                    $orderRepository->getEm()->flush();
                }
                //add log
                $log = new OrderLog();
                $log->setOrderId($model->getId());
                $log->setType(1);
                $log->setMessage("管理员操作订单(" . $model->getOrderNo() . ")发货;" .
                    implode(",", $shippingId));
                $log->setCreateBy($this->getUser()->getUsername());
                $log->setCreateAt($time)->setUpdateAt($time)->setDeleted(0);
                $orderRepository->getEm()->persist($log);
                $orderRepository->getEm()->flush();
                $conn->commit();
            } catch (\Exception $ex) {
                $conn->rollBack();
                return $this->json(['code' => 1, 'message' => $ex->getMessage()]);
            }
        }
        return $this->json(['code' => 0, "message" => "success"]);
    }


    #[Route('/{id}/refund', name: 'admin_app_order_refund', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function refund(Request $request, Order $model, OrderRepository $orderRepository): Response
    {
        if (!empty($model) && $model->getId()) {
            if (!in_array($model->getOrderStatus(), [2, 3, 4, 5])) {
                $this->json(['code' => 1, 'message' => '订单不需要售后']);
            }
            $orderMark = $request->get('order_mark');
            $addrMark = $request->get('addr_mark');

            $conn = $orderRepository->getEm()->getConnection();
            $conn->beginTransaction();
            try {
                $idStr = str_pad($model->getMemberId(), 4, "0", STR_PAD_LEFT);
                //20220321-1005-0000-rand000
                $no = "RF" . date("ymdHi") . $idStr . random_int(1000, 9999);;
                $time = time();
                //add refund
                $refund = new Refund();
                $refund->setOrderId($model->getId());
                $refund->setOrderNo($model->getOrderNo());
                $refund->setRefundNo($no);
                $refund->setMemberId($model->getMemberId());
                $refund->setRefundStatus(0);
                $refund->setType(1);
                $refund->setMark("");
                $refund->setReason('');
                $refund->setReasonImgs('');
                $refund->setShippingNo('');
                $refund->setCreateAt($time);
                $refund->setUpdateAt($time);
                $refund->setDeleted(0);
                $orderRepository->getEm()->persist($refund);
                $orderRepository->getEm()->flush();

                $model->setOrderMark($orderMark);
                $model->setAddrMark($addrMark);
                $model->setOrderStatus(10);
                $model->setUpdateAt($time);
                $model->setRefundId($refund->getId());
                $orderRepository->add($model);

                //add log
                $log = new OrderLog();
                $log->setOrderId($model->getId());
                $log->setType(1);
                $log->setMessage("管理员操作订单(" . $model->getOrderNo() . ")发起售后申请;");
                $log->setCreateBy($this->getUser()->getUsername());
                $log->setCreateAt($time);
                $log->setUpdateAt($time);
                $log->setDeleted(0);
                $orderRepository->getEm()->persist($log);
                $orderRepository->getEm()->flush();

                //add log
                $log = new RefundLog();
                $log->setRefundId($model->getId());
                $log->setType(1);
                $log->setMessage("管理员生成退货单(" . $refund->getRefundNo() . ");");
                $log->setCreateBy($this->getUser()->getUsername());
                $log->setCreateAt($time);
                $log->setUpdateAt($time);
                $log->setDeleted(0);
                $orderRepository->getEm()->persist($log);
                $orderRepository->getEm()->flush();
                $conn->commit();
            } catch (\Exception $ex) {
                $conn->rollBack();
                return $this->json(['code' => 1, 'message' => $ex->getMessage()]);
            }
        }
        return $this->json(['code' => 0, "message" => "success"]);
    }


}
