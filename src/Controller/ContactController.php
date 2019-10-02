<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ContactController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $contactRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->contactRepository = $entityManager->getRepository(Contact::class);
    }

    /**
     * @Route("/api/contacts/{id}", name="contacts_get", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function get(int $id): JsonResponse
    {
        $contact = $this->contactRepository->find($id);
        if (!$contact) {
            throw new NotFoundHttpException();
        }

        return $this->createJsonResponse($contact);
    }

    /**
     * @Route("/api/contacts", name="contacts_post", methods={"POST"})
     */
    public function postAction(Request $request): JsonResponse
    {
        $contact = new Contact();

        $this->updateEntity($contact, json_decode($request->getContent(), true));
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $this->createJsonResponse($contact);
    }

    /**
     * @Route("/api/contacts/{id}", name="contacts_put", methods={"PUT"}, requirements={"id"="\d+"})
     */
    public function putAction(Request $request, int $id): JsonResponse
    {
        $contact = $this->contactRepository->find($id);
        if (!$contact) {
            throw new NotFoundHttpException();
        }

        $this->updateEntity($contact, json_decode($request->getContent(), true));
        $this->entityManager->flush();

        return $this->createJsonResponse($contact);
    }

    /**
     * @Route("/api/contacts/{id}", name="contacts_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function deleteAction(int $id)
    {
        $contact = $this->contactRepository->find($id);
        if (!$contact) {
            throw new NotFoundHttpException();
        }

        $this->entityManager->remove($contact);
        $this->entityManager->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function createJsonResponse(Contact $contact): JsonResponse
    {
        $jsonData = [
            'id' => $contact->getId(),
            'name' => $contact->getName(),
            'email' => $contact->getEmail(),
            'country' => $contact->getCountry(),
        ];

        return new JsonResponse($jsonData);
    }

    private function updateEntity(Contact $contact, array $updateData)
    {
        $contact->setName($updateData['name']);
        $contact->setCountry($updateData['country']);

        if (array_key_exists('email', $updateData)) {
            $contact->setEmail($updateData['email']);
        }
    }
}
