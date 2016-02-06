<?php

namespace Doctrine\AnnotationsTests\Fixtures\Parser;

/** @Annotation */
class SettingsAnnotation
{
    public $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }
}