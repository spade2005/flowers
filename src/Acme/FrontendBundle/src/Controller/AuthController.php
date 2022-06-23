<?php

namespace App\Acme\FrontendBundle\src\Controller;

use App\Entity\Member;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/mall/auth')]
class AuthController extends AbstractController
{
    #[Route('/', name: 'mall_app_auth_index', methods: ['GET', 'POST'])]
    public function index(Request $request, MemberRepository $memberRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (!empty($member)) {
            return $this->redirect("/mall");
        }

        $error = '';
        $username = $request->get("_username");
        if ($request->getMethod() == 'POST') {
            $flag = $this->checkLogin($request, $memberRepository, $requestStack->getSession());
            if ($flag === true) {
                return $this->redirect("/mall");
            }
            $error = $flag;
        }
        return $this->render('mall/auth/index.html.twig', [
            'error' => $error, 'username' => $username
        ]);
    }

    public function checkLogin(Request $request, MemberRepository $memberRepository, Session $session)
    {
        $username = $request->get("_username");
        $password = $request->get("_password");
        $csrf = $request->get("_csrf_token");

        if (empty($username) || empty($password)) {
            return '请输入账号密码';
        }
        $nameLen = mb_strlen($username, 'utf8');
        $passLen = mb_strlen($password, 'utf8');
        if ($nameLen < 4 || $nameLen >= 20) {
            return '账号不合法';
        }
        if ($passLen < 4 || $passLen >= 20) {
            return '密码不合法';
        }
        $q = $memberRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.username=:username")
            ->setParameter('username', $username);
        $member = $q->getQuery()->getOneOrNullResult();
        if (empty($member)) {
            return "账号不存在";
        }
        if (!password_verify($password, $member->getPassword())) {
            return '账号或者密码不正确';
        }

        $session->set("auth_user", $member);
        return true;
    }


    public function checkRegister(Request $request, MemberRepository $memberRepository, Session $session)
    {
        $username = $request->get("_username");
        $password = $request->get("_password");
        $phone = $request->get("_phone");
        $csrf = $request->get("_csrf_token");

        if (empty($phone)) {
            return '请输入手机号码';
        }

        if (empty($username) || empty($password)) {
            return '请输入账号密码';
        }
        $nameLen = mb_strlen($username, 'utf8');
        $passLen = mb_strlen($password, 'utf8');
        $phoneLen = mb_strlen($phone, 'utf8');
        if ($nameLen < 4 || $nameLen >= 20) {
            return '账号不合法';
        }
        if ($passLen < 4 || $passLen >= 20) {
            return '密码不合法';
        }
        if (!is_numeric($phone) || $phoneLen < 11 || $phoneLen > 11) {
            return '手机号码不合法';
        }
        $q = $memberRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.username=:username")
            ->setParameter('username', $username);
        $member = $q->getQuery()->getOneOrNullResult();
        if (!empty($member)) {
            return "账号已存在";
        }
        $q = $memberRepository->createQueryBuilder('p')
            ->where("p.deleted=0 and p.phone=:phone")
            ->setParameter('phone', $phone);
        $member = $q->getQuery()->getOneOrNullResult();
        if (!empty($member)) {
            return "手机号码已存在";
        }
        //reg
        $time=time();
        $model = new Member();
        $model->setUsername($username);
        $model->setPhone($phone);
        $model->setHeadImg('');
        $model->setRealName('');
        $model->setSex(0);
        $model->setMark('');
        $model->setLevel(1);
        $model->setTotalPoint(0);
        $model->setPoint(0);
        $model->setPassword(password_hash($password, PASSWORD_DEFAULT));
        $model->setCreateAt($time);
        $model->setUpdateAt($time);
        $model->setDeleted(0);
        $memberRepository->add($model);
        $member = compact('phone', 'username');
        $session->set("auth_reg_user", $member);
        return true;
    }


    #[Route('/logout', name: 'mall_app_auth_logout', methods: ['GET', 'POST'])]
    public function logout(RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (!empty($member)) {
            $requestStack->getSession()->set('auth_user', null);
        }
        return $this->redirect("/mall");
    }


    #[Route('/register', name: 'mall_app_auth_register', methods: ['GET', 'POST'])]
    public function register(Request $request, MemberRepository $memberRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_user");
        if (!empty($member)) {
            return $this->redirect("/mall");
        }

        $error = '';
        $username = $request->get("_username");
        $phone = $request->get("_phone");
        if ($request->getMethod() == 'POST') {
            $flag = $this->checkRegister($request, $memberRepository, $requestStack->getSession());
            if ($flag === true) {
                return $this->redirect("/mall/auth/success");
            }
            $error = $flag;
        }
        return $this->render('mall/auth/register.html.twig', [
            'error' => $error, 'username' => $username, 'phone' => $phone
        ]);
    }

    #[Route('/success', name: 'mall_app_auth_success', methods: ['GET'])]
    public function success(Request $request, MemberRepository $memberRepository, RequestStack $requestStack): Response
    {
        $member = $requestStack->getSession()->get("auth_reg_user");
        if (empty($member)) {
            return $this->redirect("/mall/");
        }
        $requestStack->getSession()->set("auth_reg_user", null);
        return $this->render('mall/auth/success.html.twig', [
            'member' => $member
        ]);
    }
}
