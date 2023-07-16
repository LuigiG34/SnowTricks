<?php

namespace App\Traits;

use App\Model\TimestampInterface;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

trait TimestampableTrait
{
    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt;

    public function setCreatedAt(DateTimeImmutable $date): TimestampInterface
    {
        $this->createdAt = $date;
        return $this;
    }

    public function setUpdatedAt(DateTimeImmutable $date): TimestampInterface
    {
        $this->updatedAt = $date;
        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
