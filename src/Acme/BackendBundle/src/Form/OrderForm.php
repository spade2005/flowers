<?php

namespace App\Acme\BackendBundle\src\Form;

use App\Entity\Member;
use App\Entity\OrderGoods;
use App\Entity\OrderLog;
use App\Repository\OrderRepository;

//check from https://symfony.com/doc/current/validation.html
class OrderForm
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getList(array $data)
    {
        $start = $data['start'];
        $length = $data['length'];

        $q = $this->orderRepository->createQueryBuilder('p')
            ->where("p.deleted=0")
            ->orderBy('p.id', 'DESC');
        if (!empty($data['orderNo'])) {
            $q->andWhere("(p.order_no like :order_no OR p.id=:id)")
                ->setParameter('order_no', '%'.$data['orderNo'].'%')
                ->setParameter('id', $data['orderNo']);
        }
        if (!empty($data['orderStatus'])) {
            $q->andWhere("p.order_status =:order_status")
                ->setParameter('order_status', $data['orderStatus']);
        }
        if (!empty($data['startDay'])) {
            $q->andWhere("p.time_of_day >=:startDay")
                ->setParameter('startDay', str_replace(['-','/'],'',$data['startDay']));
        }
        if (!empty($data['finishDay'])) {
            $q->andWhere("p.time_of_day <=:finishDay")
                ->setParameter('finishDay', str_replace(['-','/'],'',$data['finishDay']));
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
                $orderGoods = $this->getOrderGoods(array_column($list, 'id'));
                foreach ($list as &$val) {
                    $val['createAt'] = date("Y-m-d H:i:s", $val['create_at']);
                    $val['goods'] = [];
                    if (isset($orderGoods[$val['id']])) {
                        $val['goods'] = $orderGoods[$val['id']];
                    }
                    $val['status'] = $this->getOrderStatus($val['order_status']);
                    unset($val['create_at'], $val['update_at']);
                }
                unset($val);
            }
        }
        return [$count, $list];
    }

    public function getOrderStatus($st)
    {
        $str = '';
        switch ($st) {
            case 1:
                $str = '已下单';
                break;
            case 2:
                $str = '已支付';
                break;
            case 3:
                $str = '已发货';
                break;
            case 4:
                $str = '已完成';
                break;
            case 5:
                $str = '已评价';
                break;
            case 9:
                $str = '已取消';
                break;
            case 10:
                $str = '售后单';
                break;
        }
        return $str;
    }

    public function getOrderGoods($orderIds)
    {
        $q = $this->orderRepository->getEm()->createQueryBuilder();
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
        $q = $this->orderRepository->getEm()->createQueryBuilder();
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
        $q = $this->orderRepository->getEm()->createQueryBuilder();
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
        $q = $this->orderRepository->getEm()->createQueryBuilder();
        $select = [
            'p.id', 'p.type', 'p.message', 'p.create_at','p.create_by'
        ];
        $q->select($select)->from(OrderLog::class, "p")
            ->where("p.order_id =:order_id and p.deleted=0")->setParameter("order_id", $id);
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
