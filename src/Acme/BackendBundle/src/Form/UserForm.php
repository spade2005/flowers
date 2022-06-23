<?php

namespace App\Acme\BackendBundle\src\Form;

use Symfony\Component\Validator\Constraints as Assert;

//check from https://symfony.com/doc/current/validation.html
class UserForm
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
     * @Assert\NotBlank(message="帐号不能为空")
     * @Assert\Range(min=1,max=2,notInRangeMessage="角色选择不合法")
     */
    public $role;


    /**
     * @Assert\IsTrue(message="两次密码不一致")
     */
    public function isPasswordSafe()
    {
        return $this->password === $this->password2;

    }

}
