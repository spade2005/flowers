<?php

namespace App\Acme\FrontendBundle\src\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\MemberOrderAddressRepository;
use App\Entity\MemberOrderAddress;
use App\Acme\FrontendBundle\src\Form\AddressForm;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/mall/address')]
class AddressController extends AbstractController
{
    #[Route('/', name: 'mall_app_address_index', methods: ['GET'])]
    public function index(MemberOrderAddressRepository $memberOrderAddressRepository,RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        $select=[
            'p.province','p.city','p.district','p.address'
            ,'p.name','p.mobile','p.id','p.is_default'
        ];
        $q = $memberOrderAddressRepository->createQueryBuilder('p')
            ->select($select)
            ->where('p.deleted=0 and p.member_id = :member_id')
            ->setParameter("member_id",$member->getId())
            ->orderBy("p.is_default","DESC")->setMaxResults(20);
        $list= $q->getQuery()->getArrayResult();
        return $this->render('mall/address/index.html.twig', [
            'addrList'=>$list
        ]);
    }

    #[Route('/new', name: 'mall_app_address_add', methods: ['GET','POST'])]
    public function new(Request $request,MemberOrderAddressRepository $memberOrderAddressRepository,ValidatorInterface $validator,RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        if ($request->getMethod() == 'GET') {
            $referer = $request->headers->get('referer');
            return $this->render('mall/address/new.html.twig', [
                'referer'=>$referer
            ]);
        }
        $vform = new AddressForm($memberOrderAddressRepository);

        $vform->name=$request->get('name');
        $vform->mobile=$request->get('mobile');
        $vform->province=$request->get('province');
        $vform->city=$request->get('city');
        $vform->district=$request->get('district');
        $vform->address=$request->get('address');
        $vform->is_default=$request->get('is_default');

        $errors = $validator->validate(($vform));
        if (count($errors)) {
            $err = $errors->get(0)->getMessage();
            return $this->json(['code' => 1, 'message' => $err]);
        }
        $vform->createAddr($member->getId());
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}/edit', name: 'mall_app_address_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, MemberOrderAddress $model, MemberOrderAddressRepository $memberOrderAddressRepository, ValidatorInterface $validator,RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (empty($member)) {
            return $this->redirect("/mall/auth");
        }
        if ($request->getMethod() == 'GET') {
            return $this->render('mall/address/edit.html.twig', [
                'model'=>$model
            ]);
        }

        $vform = new AddressForm($memberOrderAddressRepository);

        $vform->name=$request->get('name');
        $vform->mobile=$request->get('mobile');
        $vform->province=$request->get('province');
        $vform->city=$request->get('city');
        $vform->district=$request->get('district');
        $vform->address=$request->get('address');
        $vform->is_default=$request->get('is_default');

        $errors = $validator->validate(($vform));
        if (count($errors)) {
            $err = $errors->get(0)->getMessage();
            return $this->json(['code' => 1, 'message' => $err]);
        }
        $vform->updateAddr($model);
        return $this->json(['code' => 0, "message" => "success"]);
    }


    #[Route('/{id}', name: 'mall_app_address_delete', methods: ['POST'])]
    public function delete(Request $request, MemberOrderAddress $model, MemberOrderAddressRepository $memberOrderAddressRepository): Response
    {
        if (!empty($model) && $model->getId()) {
            $model->setDeleted(1);
            $memberOrderAddressRepository->add($model);
        }

        return $this->json(['code' => 0, "message" => "success"]);
    }


}
