<?php

namespace App\Acme\BackendBundle\src\Controller;

use App\Acme\BackendBundle\src\Form\MemberForm;
use App\Entity\Member;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @IsGranted("ROLE_USER")
 */
#[Route('/admin/member')]
class MemberController extends AbstractController
{
    #[Route('/', name: 'admin_app_member_index', methods: ['GET'])]
    public function index(Request $request, MemberRepository $memberRepository): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render('admin/member/index.html.twig', [
                'showTable' => true
            ]);
        }
        $name = $request->get("name");
        $start = $request->get('start',0);
        $length = $request->get('length',10);
        $q = $memberRepository->createQueryBuilder('p')
            ->where("p.deleted=0")
            ->orderBy('p.id', 'DESC');
        if (!empty($name)) {
            $q->where("p.username like :username")
                ->setParameter('username', $name . '%');
        }
        $c = $q->select('count(p.id)');
        $count = $c->getQuery()->getSingleScalarResult();
        $list = [];
        if ($count) {
            $q->select("p")->setMaxResults($length)->setFirstResult($start);
            $list = $q->getQuery()->getResult();
            if(!empty($list)){
                foreach ($list as &$val){
                    $val->setPassword('');
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

    #[Route('/new', name: 'admin_app_member_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MemberRepository $memberRepository, ValidatorInterface $validator): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->renderForm('admin/member/new.html.twig');
        }

        $userForm = new MemberForm();
        $userForm->username = $request->get("username");
        $userForm->password = $request->get("password");
        $userForm->password2 = $request->get("password2");
        $userForm->phone = $request->get("phone");
        $userForm->real_name = $request->get("real_name");
        $userForm->sex = $request->get("sex");
        $userForm->mark = $request->get("mark");
        $userForm->level = $request->get("level");

        $errors = $validator->validate(($userForm));
        if (count($errors)) {
            $err = $errors->get(0)->getMessage();
            return $this->json(['code' => 1, 'message' => $err]);
        }
        $tmpUser = $memberRepository->findOneBy(["username" => $userForm->username]);
        if (!empty($tmpUser)) {
            return $this->json(['code' => 1, 'message' => "帐号已被使用"]);
        }
        $time = time();
        $user = new Member();
        $user->setUsername($userForm->username);
        $user->setPassword(password_hash($userForm->password, PASSWORD_DEFAULT));
        $user->setPhone($userForm->phone);
        $user->setRealName($userForm->real_name);
        $user->setSex($userForm->sex);
        $user->setHeadImg("");
        $user->setTotalPoint(0)->setPoint(0);
        $user->setMark($userForm->mark);
        $user->setLevel($userForm->level);
        $user->setCreateAt($time);
        $user->setUpdateAt($time);
        $user->setDeleted(0);
        $memberRepository->add($user);
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}', name: 'admin_app_member_show', methods: ['GET'])]
    public function show(MemberRepository $memberRepository,Member $user): Response
    {
        if (empty($user)) {
            return $this->redirectToRoute('admin/app_member_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('admin/member/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_app_member_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Member $user, MemberRepository $memberRepository, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->renderForm('admin/member/edit.html.twig', ['user' => $user]);
        }
        if (empty($user) || !$user->getId()) {
            return $this->json(['code' => 1, 'message' => "member not found"]);
        }
        $password = $request->get("password");

        $userForm = new MemberForm();
        $userForm->username = $user->getUsername();
        $userForm->password = $password;
        $userForm->password2 = $request->get("password2");
        $userForm->phone = $request->get("phone");
        $userForm->real_name = $request->get("real_name");
        $userForm->sex = $request->get("sex");
        $userForm->mark = $request->get("mark");
        $userForm->level = $request->get("level");
        if (empty($password) && empty($userForm->password2)) {
            $userForm->password = "123456";
            $userForm->password2 = "123456";
        }

        $errors = $validator->validate(($userForm));
        if (count($errors)) {
            $err = $errors->get(0)->getMessage();
            return $this->json(['code' => 1, 'message' => $err]);
        }
        $time = time();
        if (!empty($password)) {
            $user->setPassword($passwordHasher->hashPassword($user, $userForm->password));
        }
        $user->setPhone($userForm->phone);
        $user->setRealName($userForm->real_name);
        $user->setSex($userForm->sex);
        $user->setMark($userForm->mark);
        $user->setLevel($userForm->level);
        $user->setUpdateAt($time);
        $memberRepository->add($user);
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}', name: 'admin_app_member_delete', methods: ['POST'])]
    public function delete(Request $request, Member $user, MemberRepository $memberRepository): Response
    {
        if (!empty($user) && $user->getId()) {
            $user->setDeleted(1);
            $memberRepository->add($user);
        }

//        return $this->redirectToRoute('admin/app_member_index', [], Response::HTTP_SEE_OTHER);
        return $this->json(['code' => 0, "message" => "success"]);
    }

}
