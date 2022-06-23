<?php

namespace App\Acme\FrontendBundle\src\Form;

use App\Entity\GoodsQuantityLog;
use App\Entity\Order;
use App\Entity\Member;
use App\Entity\Goods;
use App\Entity\GoodsImage;
use App\Entity\MemberOrderAddress;
use App\Entity\OrderGoods;
use App\Entity\OrderLog;
use App\Entity\MemberCart;
use App\Repository\OrderRepository;

class OrderForm
{
    private string $error;
    private OrderRepository $orderRepository;
    private Member $member;

    public function __construct(OrderRepository $orderRepository, Member $member)
    {
        $this->orderRepository = $orderRepository;
        $this->member = $member;
    }

    public function getError()
    {
        return $this->error;
    }

    public function checkCartGoods(array $ids){
        $select=['p.id','p.goods_id','p.goods_number'];
        $q = $this->orderRepository->getEm()->createQueryBuilder();
        $q->select($select)->from(MemberCart::class, "p")
            ->where("p.deleted=0 and p.status=0 and p.member_id=:member_id and p.id in(:id)")
            ->setParameter("member_id", $this->member->getId())
            ->setParameter("id", $ids);
        $list = $q->getQuery()->getArrayResult();
        if(empty($list)){
            $this->error = "提交参数不合法";
            return false;
        }
        $diff=array_diff($ids,array_column($list,'id'));
        if(!empty($diff)){
            $this->error = "提交参数不合法，请重新提交";
            return false;
        }
        $tmp=[];
        foreach($list as $val){
            $f=$this->checkOrderGoods($val['goods_id'],$val['goods_number']);
            if(empty($f)){
                return false;
            }
            $tmp[]=$f;
        }
        return $tmp;
    }

    public function checkOrderGoods(int $gid, int $num)
    {
        $select = [
            'p.id', 'p.title', 'p.goods_sn', 'p.is_on_sale', 'p.buy_limit'
            , 'p.price', 'p.real_price', 'p.quantity', 'p.is_test'
        ];
        $q = $this->orderRepository->getEm()->createQueryBuilder();
        $q->select($select)->from(Goods::class, "p")
            ->where("p.deleted=0 and p.id=:id")
            ->setParameter("id", $gid);
        $goods = $q->getQuery()->getOneOrNullResult();
        if (empty($goods)) {
            $this->error = "商品不存在";
            return false;
        }
        if ($goods['is_on_sale'] == 0) {
            $this->error = "商品已下架";
            return false;
        }
        if ($goods['quantity'] <= 0) {
            $this->error = "商品库存不足";
            return false;
        }
        if ($goods['quantity'] < $num) {
            $this->error = "购买的商品库存不足";
            return false;
        }
        $goods['num'] = $num;
        $goods['amount'] = bcmul($goods['real_price'], $num);
        $goods['image'] = $this->getImages($goods['id']);
        return $goods;
    }

    public function getImages(int $id)
    {
        if (empty($id)) {
            return '';
        }
        $q = $this->orderRepository->getEm()->createQueryBuilder()
            ->select("p.goods_id,p.image_src")
            ->from(GoodsImage::class, "p")
            ->where('p.deleted=0 and p.is_default=1 and p.goods_id = :id')
            ->setParameter("id", $id);
        $row = $q->getQuery()->getOneOrNullResult();
        return $row['image_src'] ?? '';
    }

    public function getAddress(int $aid = 0)
    {
        $select = [
            'p.province', 'p.city', 'p.district', 'p.address'
            , 'p.name', 'p.mobile', 'p.id', 'p.is_default'
        ];
        $q = $this->orderRepository->getEm()->createQueryBuilder()
            ->select($select)
            ->from(MemberOrderAddress::class, "p")
            ->where('p.deleted=0 and p.member_id = :member_id')
            ->setParameter("member_id", $this->member->getId());
        if ($aid > 0) {
            $q->andWhere('p.id = :id')->setParameter("id", $aid);
        }
        $q->orderBy("p.is_default", "DESC");
        return $q->getQuery()->getArrayResult();
    }

    public function helpCreateNo($id = 0)
    {
        $idStr = str_pad($id, 4, "0", STR_PAD_LEFT);
        //20220321-1005-0000-rand000
        return "F" . date("ymdHi") . $idStr . random_int(10000, 99999);
    }

    public function helpOrderModel(array &$data)
    {
        $member = $this->member;
        $shippAmount = 10;
        $amount = 0;
        foreach ($data['goods'] as $good) {
            $amount += $good['amount'];
        }
        $realAmount = $amount + $shippAmount;
        $time = time();
        $model = new Order();
        $model->setMemberId($member->getId());
        $model->setMemberLevel($member->getLevel(true));
        $no = $this->helpCreateNo($member->getId());
        $model->setOrderNo($no);
        $model->setOrderStatus(1);
        $model->setOrderMark($data['mark'] ?? '');
        $model->setPayStatus(0);
        $model->setPayTime('0');
        $model->setShippingStatus(0);
        $model->setShippingTime('0');
        $model->setShippingWay(1);
        $model->setOriginAmount(strval($amount));//订单总金额
        $model->setShippingPrice(strval($shippAmount));
        $model->setDiscountAmount('0');
        $model->setPointAmount(0);
        $model->setCouponAmount(0);
        $model->setRealAmount(strval($realAmount));//实际需要支付金额
        $model->setUsePoint(0);
        $model->setUseCoupon('');
        $model->setUseDiscount('');
        $model->setGetPoint(intval($realAmount));//订单获得积分
        $model->setAddrId($data['addr_id']);
        $model->setAddrName($data['addr']['name']);
        $model->setAddrMobile($data['addr']['mobile']);
        $address = $data['addr']['province'] . $data['addr']['city'] . $data['addr']['district'];
        $model->setAddress($address);
        $model->setAddrMark($data['addr_mark'] ?? '');
        $model->setConfirmTime('0');
        $model->setRefundId(0);
        $model->setOrderFrom($data['order_from']);
        $model->setTimeOfDay(date("Ymd", $time));
        $model->setTimeOfHour(date("H", $time));
        $model->setCreateAt($time);
        $model->setUpdateAt($time);
        $model->setDeleted(0);
        return $model;
    }

    public function helpGoodsQuantity(array &$data)
    {
        $member = $this->member;
        //扣减库存
        $em = $this->orderRepository->getEm();
        $time = time();
        foreach ($data['goods'] as $good) {
            $quantiy = $good['num'];
            $sql = "update com_goods set quantity=quantity-:quantity,total_quantity=total_quantity-:totalQuantity,total_sales=total_sales+1
where id=:id and (quantity-:quantity2)>=0";
            $stmt = $em->getConnection()->prepare($sql);
            $num = $stmt->executeStatement(['quantity' => $quantiy, 'quantity2' => $quantiy, 'totalQuantity' => $quantiy
                , 'id' => $good['id']]);
            if ($num <= 0) {
                throw new \Exception("库存不足操作失败");
            }
            $log = new GoodsQuantityLog();
            $log->setGoodsId($good['id']);
            $log->setQuantity($quantiy);
            $log->setType(2);
            $log->setSource(2);
            $log->setCreateBy($member->getUsername());
            $log->setCreateAt($time);
            $log->setDeleted(0);
            $em->persist($log);
            $em->flush();
        }
    }

    public function helpOrderGoods(array &$data, Order $model)
    {
        $em = $this->orderRepository->getEm();
        $time = time();
        //写订单商品
        foreach ($data['goods'] as $good) {
            $modelGoods = new OrderGoods();
            $modelGoods->setOrderId($model->getId());
            $modelGoods->setGoodsId($good['id']);
            $modelGoods->setGoodsName($good['title']);
            $modelGoods->setGoodsLogo($good['image']);
            $modelGoods->setGoodsSn($good['goods_sn']);
            $modelGoods->setGoodsNum($good['num']);
            $modelGoods->setGoodsType(0);
            $modelGoods->setGoodsAmount($good['real_price']);
            $modelGoods->setOriginAmount($good['amount']);
            $modelGoods->setDiscountAmount(0);
            $modelGoods->setRealAount($good['amount']);
            $modelGoods->setIsGift(0);
            $modelGoods->setGiftId(0);
            $modelGoods->setShippingStatus(0);
            $modelGoods->setShippingNo('');
            $modelGoods->setRefundId(0);
            $modelGoods->setCreateAt($time);
            $modelGoods->setUpdateAt($time);
            $modelGoods->setDeleted(0);
            $em->persist($modelGoods);
            $em->flush();
        }
    }

    public function helpOrderLog(Order $model)
    {
        $member = $this->member;
        $em = $this->orderRepository->getEm();
        $time = time();
        //写订单日志
        $log = new OrderLog();
        $log->setOrderId($model->getId());
        $log->setType(0);
        $log->setMessage('用户' . $member->getUsername() . '下单(' . $model->getOrderNo() . ')成功');
        $log->setCreateBy($member->getUsername());
        $log->setCreateAt($time);
        $log->setUpdateAt($time);
        $log->setDeleted(0);
        $em->persist($log);
        $em->flush();
    }

    public function helpCart(array $data,Order $model)
    {
        if(empty($data['is_cart']) || empty($data['ids'])){
            return 0;
        }

        $member = $this->member;
        $time = time();
        $qb = $this->orderRepository->getEm()->createQueryBuilder();
        $qb->update(MemberCart::class, "p")
            ->set('p.status',2)->set('p.order_id',$model->getId())
            ->set('p.update_at',$time)
            ->where("p.member_id=:member_id and p.id in(:id)")
            ->setParameter(":id",$data['ids'])
            ->setParameter(":member_id",$member->getId());
        $qb->getQuery()->execute();
        return 1;
    }


    public function createOrder(array $data)
    {
        $model = $this->helpOrderModel($data);

        $em = $this->orderRepository->getEm();
        $em->getConnection()->beginTransaction();
        try {
            $this->helpGoodsQuantity($data);
            //写入订单
            $this->orderRepository->add($model);
            $this->helpOrderGoods($data, $model);
            $this->helpOrderLog($model);
            //购物车的处理。
            $this->helpCart($data,$model);
            $em->getConnection()->commit();
        } catch (\Exception $ex) {
            $em->getConnection()->rollBack();
            throw $ex;
        }
        return $model->getOrderNo();
    }


    public function getOrderByNumber(string $no, $orderStatus = 0)
    {
        if (empty($no)) {
            $this->error = "未找到对应状态订单";
            return false;
        }
        $q = $this->orderRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.order_no=:order_no
             and p.member_id=:member_id")
            ->setParameter('order_no', $no)
            ->setParameter('member_id', $this->member->getId());
        if ($orderStatus > 0) {
            $q->andWhere("p.order_status=:order_status")
                ->setParameter("order_status", $orderStatus);
        }
        $q->select("p");
        return $q->getQuery()->getOneOrNullResult();
    }

    public function getOrderList(array $data)
    {
        $start = $data['start'];
        $length = $data['length'];

        $q = $this->orderRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.member_id=:member_id")
            ->setParameter(':member_id',$this->member->getId())
            ->orderBy('p.id', 'DESC');
        if (!empty($data['orderNo'])) {
            $q->andWhere("p.order_no  like :order_no")
                ->setParameter('order_no', '%'.$data['orderNo'].'%');
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

        $c = $q->select('count(p.id)');
        $count = $c->getQuery()->getSingleScalarResult();
        $list = [];
        if ($count) {
            $q->select("p")->setMaxResults($length)->setFirstResult($start);
            $list = $q->getQuery()->getArrayResult();
            if (!empty($list)) {
                $orderGoods = $this->_getOrderGoods(array_column($list, 'id'));
                foreach ($list as &$val) {
                    $val['createAt'] = date("Y-m-d H:i:s", $val['create_at']);
                    $val['goods'] = [];
                    $val['goods_count'] = 0;
                    if (isset($orderGoods[$val['id']])) {
                        $val['goods'] = $orderGoods[$val['id']];
                        $val['goods_count'] = count($val['goods']);
                    }
                    $val['status'] = $this->_getOrderStatus($val['order_status']);
                    unset($val['create_at'], $val['update_at']);
                }
                unset($val);
            }
        }
        return [$count, $list];
    }

    public function _getOrderGoods($orderIds)
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

    public function _getOrderStatus($st)
    {
        $str = '';
        switch ($st) {
            case 1:
                $str = '待支付';
                break;
            case 2:
                $str = '待发货';
                break;
            case 3:
                $str = '待收货';
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

    public function _getMember($memberId)
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
}
