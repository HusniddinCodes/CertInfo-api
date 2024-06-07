<?php

declare(strict_types=1);

namespace App\Component\SecretKey;

use App\Component\Core\AbstractManager;
use App\Entity\SecretKey;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class SecretKeyManager
 *
 * @method save(SecretKey $entity, bool $needToFlush = false): void
 * @package App\Component\User
 */
class SecretKeyManager extends AbstractManager
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }
}
