<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Linkin\Bundle\SwaggerResolverBundle\Factory\SwaggerResolverFactory;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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

    /**
     * @var SwaggerResolverFactory
     */
    private $swaggerResolverFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        SwaggerResolverFactory $swaggerResolverFactory,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->contactRepository = $entityManager->getRepository(Contact::class);
        $this->swaggerResolverFactory = $swaggerResolverFactory;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/contacts/{id}", name="contacts_get", methods={"GET"}, requirements={"id"="\d+"})
     *
     * @SWG\Get(
     *     summary="Returns a specific contact.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns status 200 and the contact.",
     *         @SWG\Schema(
     *             type="object",
     *             properties={
     *                 @SWG\Property(property="id", type="integer"),
     *                 @SWG\Property(property="name", type="string"),
     *                 @SWG\Property(property="email", type="string"),
     *                 @SWG\Property(property="country", type="string")
     *             }
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Returns status 404 if there is no contact with the given id."
     *     )
     * )
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
     *
     * @SWG\Post(
     *     summary="Adds a new contact.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="body",
     *         description="Post data.",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *             required={"name", "country"},
     *             @SWG\Property(
     *                 property="name",
     *                 type="string",
     *                 minLength=1,
     *                 example="Mia Muster"
     *             ),
     *             @SWG\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="mia@muster.com"
     *             ),
     *             @SWG\Property(
     *                 property="country",
     *                 description="ISO-2 country code in capital letters.",
     *                 type="string",
     *                 pattern="^[A-Z]{2}$",
     *                 example="AT"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns status 200 and the new contact.",
     *         @SWG\Schema(
     *             type="object",
     *             properties={
     *                 @SWG\Property(property="id", type="integer"),
     *                 @SWG\Property(property="name", type="string"),
     *                 @SWG\Property(property="email", type="string"),
     *                 @SWG\Property(property="country", type="string")
     *             }
     *         )
     *     )
     * )
     */
    public function postAction(Request $request): JsonResponse
    {
        $this->validateRequest($request);
        $contact = $this->serializer->deserialize($request->getContent(),Contact::class, 'json');

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $this->createJsonResponse($contact);
    }

    /**
     * @Route("/api/contacts/{id}", name="contacts_put", methods={"PUT"}, requirements={"id"="\d+"})
     *
     * @SWG\Put(
     *     summary="Updates an existing contact.",
     *     produces={"application/json"},
     *     @SWG\Parameter(name="id", in="path", required=true, type="integer"),
     *     @SWG\Parameter(
     *         name="body",
     *         description="Post data.",
     *         in="body",
     *         required=true,
     *         @SWG\Schema(
     *             type="object",
     *             required={"name", "country"},
     *             @SWG\Property(
     *                 property="name",
     *                 type="string",
     *                 minLength=1,
     *                 example="Mia Muster"
     *             ),
     *             @SWG\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 example="mia@muster.com"
     *             ),
     *             @SWG\Property(
     *                 property="country",
     *                 description="ISO-2 country code in capital letters.",
     *                 type="string",
     *                 pattern="^[A-Z]{2}$",
     *                 example="AT"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns status 200 and the modified contact.",
     *         @SWG\Schema(
     *             type="object",
     *             properties={
     *                 @SWG\Property(property="id", type="integer"),
     *                 @SWG\Property(property="name", type="string"),
     *                 @SWG\Property(property="email", type="string"),
     *                 @SWG\Property(property="country", type="string")
     *             }
     *         )
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Returns status 404 if there is no contact with the given id."
     *     )
     * )
     */
    public function putAction(Request $request, int $id): JsonResponse
    {
        $this->validateRequest($request);

        $contact = $this->contactRepository->find($id);
        if (!$contact) {
            throw new NotFoundHttpException();
        }

        $this->updateObject($contact, $request->getContent());
        $this->entityManager->flush();

        return $this->createJsonResponse($contact);
    }

    /**
     * @Route("/api/contacts/{id}", name="contacts_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     *
     * @SWG\Delete(
     *     summary="Deletes the given contact.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returns status 200 if the contact was deleted."
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Returns status 404 if there is no contact with the given id."
     *     )
     * )
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

    private function updateObject(object $target, string $jsonData)
    {
        $this->serializer->deserialize(
            $jsonData,
            get_class($target),
            'json',
            ['object_to_populate' => $target]
        );
    }

    private function createJsonResponse(object $data): JsonResponse
    {
        $jsonData = $this->serializer->serialize($data, 'json');

        return new JsonResponse($jsonData, Response::HTTP_OK, [], true);
    }

    protected function validateRequest(Request $request)
    {
        $swaggerResolver = $this->swaggerResolverFactory->createForRequest($request);
        $swaggerResolver->resolve(array_merge(
            json_decode($request->getContent(), true),
            $request->attributes->get('_route_params')
        ));
    }
}
