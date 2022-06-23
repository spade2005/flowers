<?php

namespace App\Acme\BackendBundle\src\Controller;

use App\Acme\BackendBundle\src\Form\GoodsCateForm;
use App\Entity\GoodsCate;
use App\Repository\GoodsCateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @IsGranted("ROLE_USER")
 */
#[Route('/admin/goods_cate')]
class GoodsCateController extends AbstractController
{
    #[Route('/', name: 'admin_app_goodsCate_index', methods: ['GET'])]
    public function index(Request $request, GoodsCateRepository $goodsCateRepository): Response
    {
        if (!$request->isXmlHttpRequest()) {
            $list = $this->helpParentList($goodsCateRepository);
            return $this->render('admin/goods_cate/index.html.twig', [
                'showTable' => true,'parentList'=>json_encode($list)
            ]);
        }
        $name = $request->get("name");
        $start = $request->get('start',0);
        $length = $request->get('length',10);
        $q = $goodsCateRepository->createQueryBuilder('p')
            ->where("p.deleted=0")
            ->orderBy('p.id', 'DESC');
        if (!empty($name)) {
            $q->where("p.title like :title")
                ->setParameter('title', $name . '%');
        }
        $c = $q->select('count(p.id)');
        $count = $c->getQuery()->getSingleScalarResult();
        $list = [];
        if ($count) {
            $q->select("p")->setMaxResults($length)->setFirstResult($start);
            $list = $q->getQuery()->getArrayResult();
            if(!empty($list)){
                $parentList=$this->helpParentList($goodsCateRepository);
                $parentList=array_column($parentList,null,'id');
                foreach ($list as &$val){
                    $val['parentId'] = isset($parentList[$val['parent_id']]) ? $parentList[$val['parent_id']]['title'] : '-';
                    $val['createAt'] = date("Y-m-d H:i:s",$val['create_at']);
                    $val['updateAt'] = date("Y-m-d H:i:s",$val['update_at']);
                    unset($val['create_at'],$val['update_at'],$val['parent_id']);
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

    #[Route('/new', name: 'admin_app_goodsCate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, GoodsCateRepository $goodsCateRepository, ValidatorInterface $validator): Response
    {
        if ($request->getMethod() == 'GET') {
            $list = $this->helpParentList($goodsCateRepository);
            return $this->renderForm('admin/goods_cate/new.html.twig', ['parentList' => $list]);
        }

        $vform = new GoodsCateForm();
        $vform->title = $request->get("title");
        $vform->parent_id = $request->get("parent_id");
        $vform->mark = $request->get("mark");

        $errors = $validator->validate(($vform));
        if (count($errors)) {
            $err = $errors->get(0)->getMessage();
            return $this->json(['code' => 1, 'message' => $err]);
        }
        $time = time();
        $model = new GoodsCate();
        $model->setTitle($vform->title);
        $model->setParentId($vform->parent_id);
        $model->setMark($vform->mark);
        $model->setCreateAt($time);
        $model->setUpdateAt($time);
        $model->setDeleted(0);
        $goodsCateRepository->add($model);
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}', name: 'admin_app_goodsCate_show', methods: ['GET'])]
    public function show(GoodsCateRepository $goodsCateRepository, GoodsCate $model): Response
    {
        if (empty($model)) {
            return $this->redirectToRoute('admin/app_goodsCate_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('admin/member/show.html.twig', [
            'model' => $model,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_app_goodsCate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GoodsCate $model, GoodsCateRepository $goodsCateRepository, ValidatorInterface $validator): Response
    {
        if ($request->getMethod() == 'GET') {
            $list = $this->helpParentList($goodsCateRepository);
            return $this->renderForm('admin/goods_cate/edit.html.twig', ['model' => $model,'parentList'=>$list]);
        }

        $vform = new GoodsCateForm();
        $vform->title = $request->get("title");
        $vform->parent_id = $request->get("parent_id");
        $vform->mark = $request->get("mark");

        $errors = $validator->validate(($vform));
        if (count($errors)) {
            $err = $errors->get(0)->getMessage();
            return $this->json(['code' => 1, 'message' => $err]);
        }
        $time = time();
        $model->setTitle($vform->title);
        $model->setParentId($vform->parent_id);
        $model->setMark($vform->mark);
        $model->setUpdateAt($time);
        $goodsCateRepository->add($model);
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}', name: 'admin_app_goodsCate_delete', methods: ['POST'])]
    public function delete(Request $request, GoodsCate $model, GoodsCateRepository $goodsCateRepository): Response
    {
        if (!empty($model) && $model->getId()) {
            $model->setDeleted(1);
            $goodsCateRepository->add($model);
        }

        return $this->json(['code' => 0, "message" => "success"]);
    }

    private function helpParentList(GoodsCateRepository $goodsCateRepository)
    {
        $q = $goodsCateRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.parent_id=0")
            ->orderBy('p.id', 'DESC')
            ->select("p.id,p.title");
        return $q->getQuery()->getArrayResult();
    }
}
