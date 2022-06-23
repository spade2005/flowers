<?php

namespace App\Acme\BackendBundle\src\Controller;

use App\Acme\BackendBundle\src\Form\RefundForm;
use App\Entity\Order;
use App\Entity\OrderGoods;
use App\Entity\OrderLog;
use App\Entity\Refund;
use App\Entity\RefundLog;
use App\Repository\RefundRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @IsGranted("ROLE_USER")
 */
#[Route('/admin/refund')]
class RefundController extends AbstractController
{
    #[Route('/', name: 'admin_app_refund_index', methods: ['GET'])]
    public function index(Request $request, RefundRepository $refundRepository): Response
    {
        $vfrom = new RefundForm($refundRepository);

        if (!$request->isXmlHttpRequest()) {
            return $this->render('admin/refund/index.html.twig', [
                'showTable' => true
            ]);
        }
        $orderNo = $request->get('order_no');
        $refundNo = $request->get('refund_no');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $startDay = $request->get('start_day');
        $finishDay = $request->get('finish_day');
        $userName = $request->get('user_name');

        $data = compact('orderNo', 'refundNo', 'start', 'length', 'startDay', 'finishDay', 'userName');
        list($count, $list) = $vfrom->getList($data);
        $ret = [
            'data' => $list,
            'iTotalRecords' => count($list),//显示数
            'iTotalDisplayRecords' => $count,//总数
        ];
        return $this->json($ret);
    }


    #[Route('/{id}', name: 'admin_app_refund_show', methods: ['GET'])]
    public function show(RefundRepository $refundRepository, Refund $model): Response
    {
        $vfrom = new RefundForm($refundRepository);
        $goodsList = $vfrom->getOrderGoods($model->getOrderId());
        $member['name'] = $vfrom->getMember($model->getMemberId());
        return $this->render('admin/refund/edit.html.twig', [
            'model' => $model,
            'goodsList' => current($goodsList), 'member' => $member,
            'logList' => $vfrom->getOrderLog($model->getId())
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_app_order_edit', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function edit(Request $request, Refund $model, RefundRepository $refundRepository): Response
    {
        $refundAmount = $request->get('refund_amount');
        $type = $request->get('type');
        $reason = $request->get('reason');
        $mark = $request->get('mark');
        $flag = $request->get('flag');

        if ($model->getOrderAmount() < $refundAmount) {
            return $this->json(['code' => 1, 'message' => '退款金额不能大于订单金额']);
        }
        $model->setType($type);
        $model->setReason($reason);
        $model->setMark($mark);
        $model->setRefundAmount($refundAmount);
        $time = time();
        $log = new RefundLog();
        $log->setRefundId($model->getId());
        $log->setType(1);
        $log->setCreateBy($this->getUser()->getUsername());
        $log->setCreateAt($time);
        $log->setUpdateAt($time);
        $log->setDeleted(0);
        switch ($model->getRefundStatus()) {
            case 0:
                if (!empty($flag)) {
                    $log->setMessage("管理员审核退单通过(" . $model->getRefundNo() . ");");
                    $model->setRefundStatus(1);
                } else {
                    $log->setMessage("管理员审核退单不通过(" . $model->getRefundNo() . ");");
                    $model->setRefundStatus(9);
                }
                break;
            case 1:
                if (!empty($flag)) {
                    $log->setMessage("管理员验货成功(" . $model->getRefundNo() . ");");
                    $model->setRefundStatus(3);
                } else {
                    $log->setMessage("管理员验货失败(" . $model->getRefundNo() . ");");
                    $model->setRefundStatus(4);
                }
                break;
            case 3:
                //发起退款todo
                $log->setMessage("管理员发起退单退款成功(" . $model->getRefundNo() . ");");
                $model->setRefundStatus(6);
                break;
            case 4:
                $log->setMessage("管理员验货失败后审核拒绝(" . $model->getRefundNo() . ");");
                $model->setRefundStatus(9);
                break;
            default:
                return $this->json(['code' => 1, 'message' => '退单无需更新']);
                break;
        }


        $model->setUpdateAt($time);
        $refundRepository->add($model);
        $refundRepository->getEm()->persist($log);
        $refundRepository->getEm()->flush();
        return $this->json(['code' => 0, "message" => "success"]);
    }

}
