<?php

namespace App\Traits;

use App\Model\SlugInterface;
use Doctrine\ORM\Mapping as ORM;

trait SluggableTrait
{
    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug;

    public function setSlug(string $slug): SlugInterface
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }
}
