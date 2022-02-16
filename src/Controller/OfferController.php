<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Offer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class OfferController extends AbstractController
{
    /**
     * @Route("/", name="offers")
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $offers = $entityManager->getRepository(Offer::class)->findAll();

        return $this->render(
            'offer/index.html.twig',
            [
                'offers' => $offers,
            ]
        );
    }

    /**
     * @Route("/offer/{id}/apply", name="offer_apply")
     * @IsGranted("ROLE_APPLICANT")
     */
    public function apply(Offer $offer, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();


        if ($applicant = $entityManager->getRepository(Applicant::class)->findOneBy(
            [
                'user' => $user,
            ]
        )) {
            $offer->addApplicant($applicant);
            $entityManager->persist($offer);

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Solicitud recibida!');
            } catch (\Exception $exception) {
                $this->addFlash('danger', 'La solicitud no pudo almacenarse. Por favor intente nuevamente.');
            }

            return $this->redirectToRoute('offers');
        }
    }
}
