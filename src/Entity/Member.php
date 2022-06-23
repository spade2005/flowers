<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberRepository::class), ORM\Table(name: "com_member")]
#[ORM\Index(name: "idx_phone", columns: ["phone"])]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private $username;

    #[ORM\Column(type: 'string', length: 30)]
    private $phone;

    #[ORM\Column(type: 'string', length: 255)]
    private $headImg;

    #[ORM\Column(type: 'string', length: 50, options: ["comment" => "名称"])]
    private $real_name;

    #[ORM\Column(type: 'smallint', options: ["comment" => "0未知1男2女"])]
    private $sex;

    #[ORM\Column(type: 'string', length: 255)]
    private $mark;

    #[ORM\Column(type: 'integer', options: ["comment" => "1普2金3黑"])]
    private $level;

    #[ORM\Column(type: 'integer', options: ["comment" => "获得总积分"])]
    private $total_point;

    #[ORM\Column(type: 'integer', options: ["comment" => "可用积分"])]
    private $point;

    #[ORM\Column(type: 'string', length: 255)]
    private $password;

    #[ORM\Column(type: 'bigint', options: ["comment" => "创建时间"])]
    private $create_at;

    #[ORM\Column(type: 'bigint')]
    private $update_at;

    #[ORM\Column(type: 'smallint')]
    private $deleted;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getHeadImg(): ?string
    {
        return $this->headImg;
    }

    public function setHeadImg(string $headImg): self
    {
        $this->headImg = $headImg;

        return $this;
    }

    public function getRealName(): ?string
    {
        return $this->real_name;
    }

    public function setRealName(string $real_name): self
    {
        $this->real_name = $real_name;

        return $this;
    }

    public function getSex($isReal=false)
    {
        if($isReal){
            return $this->sex;
        }
        return $this->getSexStr();
    }

    public function setSex(int $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getSexStr()
    {
        $str = '未知';
        switch ($this->sex) {
            case 1:
                $str = '男';
                break;
            case 2:
                $str = '女';
                break;
        }
        return $str;
    }

    public function getLevelStr()
    {
        $str = '默';
        switch ($this->level) {
            case 1:
                $str = '普';
                break;
            case 2:
                $str = '金';
                break;
            case 3:
                $str = '黑';
                break;
        }
        return $str;
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

    public function getLevel($isReal=false)
    {
        if($isReal) {
            return $this->level;
        }
        return  $this->getLevelStr();
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getTotalPoint(): ?int
    {
        return $this->total_point;
    }

    public function setTotalPoint(int $total_point): self
    {
        $this->total_point = $total_point;

        return $this;
    }

    public function getPoint(): ?int
    {
        return $this->point;
    }

    public function setPoint(int $point): self
    {
        $this->point = $point;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getCreateAt(): ?string
    {
        return $this->create_at ? date("Y-m-d H:i:s", $this->create_at) : '-';
    }

    public function setCreateAt(string $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getUpdateAt(): ?string
    {
        return $this->update_at ? date("Y-m-d H:i:s", $this->update_at) : '-';
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
