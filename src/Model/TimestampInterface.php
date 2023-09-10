<?php

namespace App\Model;

interface TimestampInterface
{
    public function getCreatedAt(): ?\DateTimeImmutable;

    public function getUpdatedAt(): ?\DateTimeImmutable;

    public function setCreatedAt(\DateTimeImmutable $date): self;

    public function setUpdatedAt(\DateTimeImmutable $date): self;
}
