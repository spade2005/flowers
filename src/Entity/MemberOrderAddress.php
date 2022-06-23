<?php

namespace App\Entity;

use App\Repository\MemberOrderAddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberOrderAddressRepository::class),ORM\Table(name: "com_member_order_address")]
#[ORM\Index(name: "idx_member_id", columns: ["member_id"])]
class MemberOrderAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $member_id;

    #[ORM\Column(type: 'string', length: 50,options: ["comment" => "收货人名称"])]
    private $name;

    #[ORM\Column(type: 'string', length: 30,options: ["comment" => "手机"])]
    private $mobile;

    #[ORM\Column(type: 'string', length: 50)]
    private $province;

    #[ORM\Column(type: 'string', length: 50)]
    private $city;

    #[ORM\Column(type: 'string', length: 50)]
    private $district;

    #[ORM\Column(type: 'string', length: 255)]
    private $address;

    #[ORM\Column(type: 'string', length: 20)]
    private $zipcode;

    #[ORM\Column(type: 'string', length: 255,options: ["comment" => "备注"])]
    private $mark;

    #[ORM\Column(type: 'boolean',options: ["comment" => "1为默认"])]
    private $is_default;

    #[ORM\Column(type: 'bigint')]
    private $create_at;

    #[ORM\Column(type: 'bigint')]
    private $update_at;

    #[ORM\Column(type: 'smallint')]
    private $deleted;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMemberId(): ?string
    {
        return $this->member_id;
    }

    public function setMemberId(string $member_id): self
    {
        $this->member_id = $member_id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(string $province): self
    {
        $this->province = $province;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(string $district): self
    {
        $this->district = $district;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getMark(): ?string
    {
        return $this->mark;
    }

    public function setMark(string $mark): self
    {
        $this->mark = $mark;

        return $this;
    }

    public function getIsDefault(): ?bool
    {
        return $this->is_default;
    }

    public function setIsDefault(bool $is_default): self
    {
        $this->is_default = $is_default;

        return $this;
    }


    public function getCreateAt(): ?string
    {
        return $this->create_at;
    }

    public function setCreateAt(string $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getUpdateAt(): ?string
    {
        return $this->update_at;
    }

    public function setUpdateAt(string $update_at): self
    {
        $this->update_at = $update_at;

        return $this;
    }

    public function getDeleted(): ?int
    {
        return $this->deleted;
    }

    public function setDeleted(int $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
}
