<?php

namespace App\Acme\FrontendBundle\src\Form;

use App\Repository\MemberOrderAddressRepository;
use App\Entity\MemberOrderAddress;
use Symfony\Component\Validator\Constraints as Assert;

//check from https://symfony.com/doc/current/validation.html
class AddressForm
{

    /**
     * @Assert\NotBlank(message="名称不能为空")
     * @Assert\Length(min=2,max=50,minMessage="名称不合法",maxMessage="名称不合法")
     */
    public $name;
    /**
     * @Assert\NotBlank(message="手机不能为空")
     * @Assert\Length(min=11,max=11,minMessage="手机格式不合法",maxMessage="手机格式不合法")
     */
    public $mobile;
    /**
     * @Assert\NotBlank(message="省份不能为空")
     * @Assert\Length(min=2,max=50,minMessage="省份不合法",maxMessage="省份不合法")
     */
    public $province;
    /**
     * @Assert\NotBlank(message="城市不能为空")
     * @Assert\Length(min=2,max=50,minMessage="城市不合法",maxMessage="城市不合法")
     */
    public $city;

    public $district;
    /**
     * @Assert\NotBlank(message="地址不能为空")
     * @Assert\Length(min=2,max=255,minMessage="地址不合法",maxMessage="地址不合法")
     */
    public $address;
    /**
     * @Assert\NotBlank(message="是否默认为必填")
     * @Assert\Range(min=0,max=1,notInRangeMessage="请选择是否默认地址")
     */
    public $is_default;

    private MemberOrderAddressRepository $memberOrderAddressRepository;

    public function __construct(MemberOrderAddressRepository $memberOrderAddressRepository){
        $this->memberOrderAddressRepository = $memberOrderAddressRepository;
    }


    public function createAddr($memberId){
        $time=time();
        $model = new MemberOrderAddress();
        $model->setName($this->name);
        $model->setMemberId($memberId);
        $model->setMobile($this->mobile);
        $model->setProvince($this->province);
        $model->setCity($this->city);
        $model->setDistrict($this->district);
        $model->setAddress($this->address);
        $model->setZipcode("");
        $model->setMark("");
        $model->setIsDefault(boolval($this->is_default));
        $model->setCreateAt($time);
        $model->setUpdateAt($time);
        $model->setDeleted(0);
        $this->memberOrderAddressRepository->add($model);
        $this->updateDefault($model);
        return $model;
    }

    public function updateAddr(MemberOrderAddress $model){
        $time=time();
        $model->setName($this->name);
        $model->setMobile($this->mobile);
        $model->setProvince($this->province);
        $model->setCity($this->city);
        $model->setDistrict($this->district);
        $model->setAddress($this->address);
        $model->setIsDefault(boolval($this->is_default));
        $model->setUpdateAt($time);
        $this->memberOrderAddressRepository->add($model);
        $this->updateDefault($model);
        return $model;
    }

    public function updateDefault(MemberOrderAddress $model){
        if(!$this->is_default){
            return ;
        }
        $conn=$this->memberOrderAddressRepository->getEm()->getConnection();

        $sql = '
            UPDATE com_member_order_address p SET p.is_default=0
            WHERE p.id != :id AND p.is_default=1
            AND p.member_id=:member_id
            ';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $model->getId()
            ,'member_id'=>$model->getMemberId()]);
    }


}
