<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AvatarHelper
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateAvatar(User $user): self
    {
        $curl = curl_init($user->getAvatar());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $returnValue = curl_exec($curl);

        // TODO: error checking!!!

        $imageData = chunk_split(base64_encode($returnValue));
        curl_close($curl);

        $user->setAvatarEncoded('data:image/jpg;base64,'.$imageData);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this;
    }
}