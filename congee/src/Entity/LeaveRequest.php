<?php

namespace App\Entity;

use App\Repository\LeaveRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeaveRequestRepository::class)]
#[ORM\Table(name: "leave_requests")]
class LeaveRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $employeeId = null;

    #[ORM\Column]
    private ?int $companyId = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $leaveType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfPath = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $isConfirmed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmployeeId(): ?int
    {
        return $this->employeeId;
    }

    public function setEmployeeId(int $employeeId): self
    {
        $this->employeeId = $employeeId;
        return $this;
    }

    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    public function setCompanyId(int $companyId): self
    {
        $this->companyId = $companyId;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLeaveType(): ?string
    {
        return $this->leaveType;
    }

    public function setLeaveType(string $leaveType): self
    {
        $this->leaveType = $leaveType;
        return $this;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(?string $pdfPath): self
    {
        $this->pdfPath = $pdfPath;
        return $this;
    }

    public function isConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    public function setConfirmed(bool $confirmed): self
    {
        $this->isConfirmed = $confirmed;
        return $this;
    }
}