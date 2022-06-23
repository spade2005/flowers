<?php

namespace App\Acme\BackendBundle\src\Form;

use Symfony\Component\Validator\Constraints as Assert;

//check from https://symfony.com/doc/current/validation.html
class MemberForm
{

    /**
     * @Assert\NotBlank(message="帐号不能为空")
     * @Assert\Length(min=2,max=50,minMessage="帐号不合法",maxMessage="帐号不合法")
     */
    public $username;
    /**
     * @Assert\NotBlank(message="密码不能为空")
     * @Assert\Length(min=2,max=20,minMessage="帐号不合法",maxMessage="帐号不合法")
     */
    public $password;
    /**
     * @Assert\NotBlank(message="密码不能为空")
     * @Assert\Length(min=2,max=20,minMessage="帐号不合法",maxMessage="帐号不合法")
     */
    public $password2;

    /**
     * @Assert\NotBlank(message="手机不能为空")
     * @Assert\Length(min=11,max=11,minMessage="手机格式不合法",maxMessage="手机格式不合法")
     */
    public $phone;

    /**
     * @Assert\Length(min=2,max=50,minMessage="帐号不合法",maxMessage="姓名不合法")
     */
    public $real_name;
    /**
     * @Assert\NotBlank(message="性别不能为空")
     * @Assert\Range(min=0,max=2,notInRangeMessage="性别选择不合法")
     */
    public $sex;
    /**
     */
    public $mark;
    /**
     * @Assert\NotBlank(message="等级不能为空")
     * @Assert\Range(min=0,max=3,notInRangeMessage="等级选择不合法")
     */
    public $level;


    /**
     * @Assert\IsTrue(message="两次密码不一致")
     */
    public function isPasswordSafe()
    {
        return $this->password === $this->password2;

    }

}
