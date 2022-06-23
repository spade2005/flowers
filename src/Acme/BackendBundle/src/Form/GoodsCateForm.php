<?php

namespace App\Acme\BackendBundle\src\Form;

use Symfony\Component\Validator\Constraints as Assert;

//check from https://symfony.com/doc/current/validation.html
class GoodsCateForm
{

    /**
     * @Assert\NotBlank(message="名称不能为空")
     * @Assert\Length(min=2,max=50,minMessage="名称不合法",maxMessage="名称不合法")
     */
    public $title;
    /**
     * @Assert\NotBlank(message="层级不能为空")
     * @Assert\GreaterThanOrEqual(0)
     */
    public $parent_id;
    /**
     * @Assert\Length(min=0,max=255,minMessage="备注不合法",maxMessage="备注不合法")
     */
    public $mark;



}
