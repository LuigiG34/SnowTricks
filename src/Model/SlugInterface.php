<?php

namespace App\Model;

interface SlugInterface
{
    public function setSlug(string $slug): self;

    public function getSlug(): ?string;

    public function __toString(): string;
}