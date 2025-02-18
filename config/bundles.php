<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],
    Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    ApiPlatform\Symfony\Bundle\ApiPlatformBundle::class => ['all' => true],
    Vich\UploaderBundle\VichUploaderBundle::class => ['all' => true],
    Pontedilana\WeasyprintBundle\WeasyprintBundle::class => ['all' => true],
    Knp\Bundle\SnappyBundle\KnpSnappyBundle::class => ['all' => true],
];
