<?php

namespace App\Acme\BackendBundle\src\Controller;

use App\Acme\BackendBundle\src\Form\UserForm;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @IsGranted("ROLE_ADMIN")
 */
#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'admin_app_user_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->render('admin/user/index.html.twig', [
                'showTable' => true
            ]);
        }

        $name = $request->get("name");
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $q = $userRepository->createQueryBuilder('p')
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
            $list = $q->getQuery()->getArrayResult();
            if (!empty($list)) {
                foreach ($list as &$val) {
                    if (is_array($val['roles']) && in_array('ROLE_ADMIN', $val['roles'])) {
                        $val['roles'] = '超级管理员';
                    } else {
                        $val['roles'] = '普通管理员';
                    }
                    $val['createAt'] = date("Y-m-d H:i:s", $val['createAt']);
                    $val['updateAt'] = date("Y-m-d H:i:s", $val['updateAt']);
                }
            }
        }
        $ret = [
            'data' => $list,
            'iTotalRecords' => count($list),//显示数
            'iTotalDisplayRecords' => $count,//总数
        ];
        return $this->json($ret);
    }

    #[Route('/new', name: 'admin_app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->renderForm('admin/user/new.html.twig');
        }

        $userForm = new UserForm();
        $userForm->username = $request->get("username");
        $userForm->password = $request->get("password");
        $userForm->password2 = $request->get("password2");
        $userForm->role = $request->get("role");

        $errors = $validator->validate(($userForm));
        if (count($errors)) {
            $err = $errors->get(0)->getMessage();
            return $this->json(['code' => 1, 'message' => $err]);
        }
        $tmpUser = $userRepository->findOneBy(["username" => $userForm->username]);
        if (!empty($tmpUser)) {
            return $this->json(['code' => 1, 'message' => "帐号已被使用"]);
        }
        $time = time();
        $user = new User();
        $user->setUsername($userForm->username);
        $user->setPassword($passwordHasher->hashPassword($user, $userForm->password));
        if ($userForm->role == '2') {
            $user->setRoles(['ROLE_ADMIN']);
        }
        $user->setCreateAt($time);
        $user->setUpdateAt($time);
        $userRepository->add($user);
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}', name: 'admin_app_user_show', methods: ['GET'])]
    public function show(UserRepository $userRepository): Response
    {
        $q = $userRepository->createQueryBuilder('p')
            ->where("p.deleted=0")
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1);
        $user = $q->getQuery()->getArrayResult();
        if (empty($user)) {
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }
        $user = $user[0];
        if (is_array($user['roles']) && in_array('ROLE_ADMIN', $user['roles'])) {
            $user['roles'] = '超级管理员';
        } else {
            $user['roles'] = '普通管理员';
        }
        $user['createAt'] = date("Y-m-d H:i:s", $user['createAt']);
        $user['updateAt'] = date("Y-m-d H:i:s", $user['updateAt']);
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->getMethod() == 'GET') {
            return $this->renderForm('admin/user/edit.html.twig', ['user' => $user]);
        }
        $password = $request->get("password");

        $userForm = new UserForm();
        $userForm->username = $user->getUsername();
        $userForm->password = $password;
        $userForm->password2 = $request->get("password2");
        $userForm->role = $request->get("role");
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
        if ($userForm->role == '2') {
            $user->setRoles(['ROLE_ADMIN']);
        } else {
            $user->setRoles([]);
        }
        $user->setUpdateAt($time);
        $userRepository->add($user);
        return $this->json(['code' => 0, "message" => "success"]);
    }

    #[Route('/{id}', name: 'admin_app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
//        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
        if (!empty($user) && $user->getId()) {
            $user->setDeleted(1);
            $userRepository->add($user);
        }
        return $this->json(['code' => 0, "message" => "success"]);
//        return $this->redirectToRoute('admin/app_user_index', [], Response::HTTP_SEE_OTHER);
    }

}
