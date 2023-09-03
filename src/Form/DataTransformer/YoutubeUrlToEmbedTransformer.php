<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class YoutubeUrlToEmbedTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        // Transform the embedded URL back to the input value (if needed)
        return $value;
    }

    public function reverseTransform($value)
    {
        // Transform the YouTube video URL to the embedded URL
        if (preg_match('/^https:\/\/www\.youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $value, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }
    }
}
