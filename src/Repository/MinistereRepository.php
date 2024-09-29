<?php

namespace App\Repository;

use App\Entity\Ministere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ministere>
 *
 * @method Ministere|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ministere|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ministere[]    findAll()
 * @method Ministere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MinistereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ministere::class);
    }

    // Ajoutez vos méthodes personnalisées ici si nécessaire
}
