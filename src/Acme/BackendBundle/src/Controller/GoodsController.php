<?php

namespace App\Acme\BackendBundle\src\Controller;

use App\Acme\BackendBundle\src\Form\GoodsForm;
use App\Entity\Goods;
use App\Entity\GoodsQuantityLog;
use App\Repository\GoodsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @IsGranted("ROLE_USER")
 */
#[Route('/admin/goods')]
class GoodsController extends AbstractController
{
    #[Route('/', name: 'admin_app_goods_index', methods: ['GET'])]
    public function index(Request $request, GoodsRepository $goodsRepository): Response
    {
        $vfrom = new GoodsForm($goodsRepository);

        if (!$request->isXmlHttpRequest()) {
            return $this->render('admin/goods/index.html.twig', [
                'showTable' => true, 'cateList' => $vfrom->helpCateList(true)
            ]);
        }
        $name = $request->get("name");
        $goodsSn = $request->get("goods_sn");
        $cateId = $request->get("cate_id");
        $onSale = $request->get("on_sale");
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $q = $goodsRepository->createQueryBuilder('p')
            ->where("p.deleted=0")
            ->orderBy('p.id', 'DESC');
        if (!empty($name)) {
            $q->where("p.title like :title")
                ->setParameter('title', $name . '%');
        }
        if (!empty($goodsSn)) {
            if (is_numeric($goodsSn)) {
                $q->where("p.id =:id")
                    ->setParameter('id', $goodsSn);
            } else {
                $q->where("p.goods_sn=:goods_sn")
                    ->setParameter('goods_sn', $goodsSn);
            }
        }
        if (!empty($cateId)) {
            $q->where("p.cate_id =:cate_id")
                ->setParameter('cate_id', $cateId);
        }
        if (isset($onSale) && $onSale >= 0) {
            $q->where("p.is_on_sale =:is_on_sale")
                ->setParameter('is_on_sale', $onSale);
        }
        $c = $q->select('count(p.id)');
        $count = $c->getQuery()->getSingleScalarResult();
        $list = [];
        if ($count) {
            $q->select("p")->setMaxResults($length)->setFirstResult($start);
            $list = $q->getQuery()->getArrayResult();
            if (!empty($list)) {
                $cateList = $vfrom->helpCateList();
                $cateList = array_column($cateList, null, "id");
                foreach ($list as &$val) {
                    $val['cateStr'] = isset($cateList[$val['cate_id']]) ? $cateList[$val['cate_id']]['title'] : '-';
                    $val['testStr'] = $val['is_test'] == 1 ? '是' : '否';
                    $val['is_on_sale'] = $val['is_on_sale'] == 1 ? '已上架' : '已下架';
                    $val['createAt'] = date("Y-m-d H:i:s", $val['create_at']);
                    $val['updateAt'] = date("Y-m-d H:i:s", $val['update_at']);
                    unset($val['create_at'], $val['update_at']);
                }
                unset($val);
            }
        }
        $ret = [
            'data' => $list,
            'iTotalRecords' => count($list),//显示数
            'iTotalDisplayRecords' => $count,//总数
        ];
        return $this->json($ret);
    }

    #[Route('/new', name: 'admin_app_goods_new', methods: ['GET', 'POST'])]
    public function new(Request $request, GoodsRepository $goodsRepository, ValidatorInterface $validator): Response
    {
        $vform = new GoodsForm($goodsRepository);
        if ($request->getMethod() == 'GET') {
            return $this->renderForm('admin/goods/new.html.twig', ['cateList' => $vform->helpCateList(true)]);
        }
        $vform->title = $request->get("title");
        $vform->cate_id = $request->get("cate_id");
        $vform->intro = $request->get("intro");
        $vform->brand = $request->get("brand");
        $vform->store = $request->get("store");
        $vform->is_on_sale = $request->get("is_on_sale");
        $vform->buy_limit = $request->get("buy_limit");
        $vform->price = $request->get("price");
        $vform->real_price = $request->get("real_price");
        $vform->quantity = $request->get("quantity");
        $vform->is_cart = $request->get("is_cart");
        $vform->is_test = $request->get("is_test");
        $vform->is_search = $request->get("is_search");
        $vform->images = $request->get('images');
        $vform->content1 = $request->get('content1');
        $vform->content2 = $request->get('content2');
        $vform->content3 = $request->get('content3');
        $errors = $validator->validate(($vform));
        if (count($errors)) {
            $err = $errors->get(0)->getMessage();
            return $this->json(['code' => 1, 'message' => $err]);
        }
        $vform->createGoods();
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}/edit', name: 'admin_app_goods_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Goods $model, GoodsRepository $goodsRepository, ValidatorInterface $validator): Response
    {
        $vform = new GoodsForm($goodsRepository);
        if ($request->getMethod() == 'GET') {
            return $this->renderForm('admin/goods/edit.html.twig', [
                    'model' => $model, 'cateList' => $vform->helpCateList(true),
                    'images' => $vform->helpImageList($model->getId()), 'content' => $vform->helpContentList($model->getId())
                ]
            );
        }
        $vform->title = $request->get("title");
        $vform->cate_id = $request->get("cate_id");
        $vform->intro = $request->get("intro");
        $vform->brand = $request->get("brand");
        $vform->store = $request->get("store");
        $vform->is_on_sale = $request->get("is_on_sale");
        $vform->buy_limit = $request->get("buy_limit");
        $vform->price = $request->get("price");
        $vform->real_price = $request->get("real_price");
        $vform->quantity = 100;
        $vform->is_cart = $request->get("is_cart");
        $vform->is_test = $request->get("is_test");
        $vform->is_search = $request->get("is_search");
        $vform->images = $request->get('images');
        $vform->content1 = $request->get('content1');
        $vform->content2 = $request->get('content2');
        $vform->content3 = $request->get('content3');
        $errors = $validator->validate(($vform));
        if (count($errors)) {
            $err = $errors->get(0)->getMessage();
            return $this->json(['code' => 1, 'message' => $err]);
        }
        $vform->updateGoods($model);
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}', name: 'admin_app_goods_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Goods $model, GoodsRepository $goodsRepository): Response
    {
        if (!empty($model) && $model->getId()) {
            $model->setDeleted(1);
            $goodsRepository->add($model);
        }

        return $this->json(['code' => 0, "message" => "success"]);
    }


    #[Route('/upload', name: 'admin_app_goods_upload')]
    public function upload(Request $request): Response
    {
        if (empty($_FILES['myfile'])) {
            $j = json_encode(array('error' => 1, 'msg' => '请选择图片进行上传。'));
            $s = '<script>parent.ajax_upload_return(\'' . $j . '\')</script>';
            exit($s);
        }
        //判断只能上传图片
        $ext = explode('.', $_FILES['myfile']['name']);
        $ext = end($ext);
        $ext = strtolower($ext);
        if (!in_array($ext, array('jpg', 'gif', 'png', 'jpeg'))) {
            $j = json_encode(array('error' => 1, 'msg' => '图片格式不正确'));
            $s = '<script>parent.ajax_upload_return(\'' . $j . '\')</script>';
            exit($s);
        }
        $maxSize = 1024 * 400;//400KB最大
        if ($_FILES['myfile']['size'] > $maxSize) {
            $j = json_encode(array('error' => 1, 'msg' => '图片大小不能超过400KB'));
            $s = '<script>parent.ajax_upload_return(\'' . $j . '\')</script>';
            exit($s);
        }
        $projectDir = $this->getParameter('kernel.project_dir') . '/public';
        $_path = '/upload/goods/' . date("ym");
        if (!is_dir($projectDir . $_path)) {
            mkdir($projectDir . $_path, 755, true);
        }
        $id = date("dHi") . $this->getUser()->getId() . '_' . random_int(1, 99999);
        $path = $projectDir . $_path . '/' . $id . '.' . $ext;
        if (move_uploaded_file($_FILES['myfile']['tmp_name'], $path)) {
            $j = json_encode(array('error' => 0, 'src' => $_path . '/' . $id . '.' . $ext));
            $s = '<script>parent.ajax_upload_return(\'' . $j . '\')</script>';
            exit($s);
        }
        $j = json_encode(array('error' => 1, 'msg' => '服务器空间不支持.'));
        $s = '<script>parent.ajax_upload_return(\'' . $j . '\')</script>';
        exit($s);
    }

    #[Route('/{id}/quantity', name: 'admin_app_goods_quantity', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function quantity(Request $request, Goods $model, GoodsRepository $goodsRepository): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->renderForm('admin/goods/quantity.html.twig', [
                    'model' => $model
                ]
            );
        }
        $optType = $request->get("opt_type");
        $quantiy = $request->get("quantiy");
        $conn = $goodsRepository->getEm()->getConnection();
        $time = time();
        $log = new GoodsQuantityLog();
        $log->setGoodsId($model->getId());
        $log->setQuantity($quantiy);
        $log->setSource(0);
        $log->setCreateBy($this->getUser()->getId());
        $log->setCreateAt($time);
        $log->setDeleted(0);
        switch ($optType) {
            case 'add':
                $sql = "update com_goods set quantity=quantity+:quantity,total_quantity=total_quantity+:totalQuantity where id=:id";
                $stmt = $conn->prepare($sql);
                $resultSet = $stmt->executeQuery(['quantity' => $quantiy, 'totalQuantity' => $quantiy, 'id' => $model->getId()]);
                $log->setType(1);
                break;
            case 'div':
                if ($model->getQuantity() < $quantiy) {
                    return $this->json(['code' => 1, 'message' => '库存不足无法操作']);
                }
                $sql = "update com_goods set quantity=quantity-:quantity,total_quantity=total_quantity-:totalQuantity where id=:id and (quantity-:quantity2)>=0";
                $stmt = $conn->prepare($sql);
                $num = $stmt->executeStatement(['quantity' => $quantiy, 'quantity2' => $quantiy, 'totalQuantity' => $quantiy, 'id' => $model->getId()]);
                if ($num <= 0) {
                    return $this->json(['code' => 1, 'message' => '库存不足操作失败']);
                }
                $log->setType(2);
                break;
            default:
                return $this->json(['code' => 1, 'message' => '操作不合法']);
                break;
        }
        $em = $goodsRepository->getEm();
        $em->persist($log);
        $em->flush();
        return $this->json(['code' => 0, "message" => "success"]);
    }

}
