<?php
// src/Twig/AppExtension.php

namespace App\twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_route', [$this, 'getCurrentRoute']),
        ];
    }

    public function getCurrentRoute(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request ? $request->attributes->get('_route') : null;
    }
}
