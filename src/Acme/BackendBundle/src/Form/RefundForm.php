<?php

namespace App\Acme\BackendBundle\src\Form;

use App\Entity\Member;
use App\Entity\OrderGoods;
use App\Entity\RefundLog;
use App\Repository\RefundRepository;

//check from https://symfony.com/doc/current/validation.html
class RefundForm
{
    private RefundRepository $refundRepository;

    public function __construct(RefundRepository $refundRepository)
    {
        $this->refundRepository = $refundRepository;
    }

    public function getList(array $data)
    {
        $start = $data['start'];
        $length = $data['length'];

        $q = $this->refundRepository->createQueryBuilder('p')
            ->where("p.deleted=0")
            ->orderBy('p.id', 'DESC');
        if (!empty($data['refundNo'])) {
            $q->andWhere("(p.refund_no like :refund_no)")
                ->setParameter('refund_no', '%'.$data['refundNo'].'%');
        }
        if (!empty($data['orderNo'])) {
            $q->andWhere("(p.order_no like :order_no)")
                ->setParameter('order_no', '%'.$data['orderNo'].'%');
        }
        if (!empty($data['startDay'])) {
            $q->andWhere("p.create_at >=:startDay")
                ->setParameter('startDay', strtotime($data['startDay']));
        }
        if (!empty($data['finishDay'])) {
            $q->andWhere("p.create_at <=:finishDay")
                ->setParameter('finishDay', strtotime($data['finishDay'])+86399);
        }
        if (!empty($data['userName'])) {
            $memberId = $this->getMemberId($data['userName']);
            if (!empty($memberId)) {
                $q->andWhere("p.member_id =:member_id")
                    ->setParameter('member_id', $memberId);
            }
        }

        $c = $q->select('count(p.id)');
        $count = $c->getQuery()->getSingleScalarResult();
        $list = [];
        if ($count) {
            $q->select("p")->setMaxResults($length)->setFirstResult($start);
            $list = $q->getQuery()->getArrayResult();
            if (!empty($list)) {
                $orderGoods = $this->getOrderGoods(array_column($list, 'order_id'));
                foreach ($list as &$val) {
                    $val['createAt'] = date("Y-m-d H:i:s", $val['create_at']);
                    $val['goods'] = [];
                    if (isset($orderGoods[$val['order_id']])) {
                        $val['goods'] = $orderGoods[$val['order_id']];
                    }
                    $val['status'] = $this->getStatus($val['refund_status']);
                    unset($val['create_at'], $val['update_at']);
                }
                unset($val);
            }
        }
        return [$count, $list];
    }

    public function getStatus($st)
    {
        $str = '';
        switch ($st) {
            case 0:
                $str = '待审核';
                break;
            case 1:
                $str = '待收货';
                break;
            case 2:
                $str = '待验货';
                break;
            case 3:
                $str = '验货成功';
                break;
            case 4:
                $str = '验货失败';
                break;
            case 5:
                $str = '退货成功';
                break;
            case 6:
                $str = '退款成功';
                break;
            case 9:
                $str = '审核不通过';
                break;
        }
        return $str;
    }

    public function getOrderGoods($orderIds)
    {
        $q = $this->refundRepository->getEm()->createQueryBuilder();
        $q->select("p")->from(OrderGoods::class, "p")
            ->where("p.order_id in(:ids) and p.deleted=0")->setParameter("ids", $orderIds);
        $list = $q->getQuery()->getArrayResult();
        if (empty($list)) {
            return [];
        }
        $tmp = [];
        foreach ($list as $val) {
            $tmp[$val['order_id']][] = $val;
        }
        return $tmp;
    }

    public function getMemberId($username)
    {
        $q = $this->refundRepository->getEm()->createQueryBuilder();
        $q->select("p.id")->from(Member::class, "p")
            ->where("p.username=:username")->setParameter("username", $username);
        $id = $q->getQuery()->getSingleScalarResult();
        if (empty($id)) {
            return false;
        }
        return $id;
    }

    public function getMember($memberId)
    {
        $q = $this->refundRepository->getEm()->createQueryBuilder();
        $q->select("p.username")->from(Member::class, "p")
            ->where("p.id=:id")->setParameter("id", $memberId);
        $id = $q->getQuery()->getSingleScalarResult();
        if (empty($id)) {
            return false;
        }
        return $id;
    }

    public function getLevelStr($lv)
    {
        $str = '默';
        switch ($lv) {
            case 1:
                $str = '普';
                break;
            case 2:
                $str = '金';
                break;
            case 3:
                $str = '黑';
                break;
        }
        return $str;
    }
    public function getOrderLog(int $id){
        $q = $this->refundRepository->getEm()->createQueryBuilder();
        $select = [
            'p.id', 'p.type', 'p.message', 'p.create_at','p.create_by'
        ];
        $q->select($select)->from(RefundLog::class, "p")
            ->where("p.refund_id =:refund_id and p.deleted=0")->setParameter("refund_id", $id);
        $list = $q->getQuery()->getArrayResult();
        if (!empty($list)) {
            foreach ($list as &$v) {
                $v['create_at'] = date("Y-m-d H:i:s", $v['create_at']);
            }
            unset($v);
        }
        return $list;
    }

}
