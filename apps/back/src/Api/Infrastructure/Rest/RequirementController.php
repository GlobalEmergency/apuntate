<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\RenameRequirement;
use GlobalEmergency\Apuntate\Entity\Requirement;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\RequirementRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/requirements')]
class RequirementController extends AbstractController
{
    public function __construct(
        private RequirementRepositoryInterface $requirementRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $requirements = $this->requirementRepository->findAll();

        return new JsonResponse(array_map(fn (Requirement $r) => [
            'id' => $r->getId()->toRfc4122(),
            'name' => $r->getName(),
        ], $requirements));
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $name = $data['name'] ?? '';

        if ('' === trim($name)) {
            return new JsonResponse(['error' => 'Name is required.'], Response::HTTP_BAD_REQUEST);
        }

        $requirement = new Requirement();
        $requirement->setName($name);
        $this->requirementRepository->save($requirement);

        return new JsonResponse([
            'id' => $requirement->getId()->toRfc4122(),
            'name' => $requirement->getName(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{requirementId}', methods: ['PUT'])]
    public function rename(string $requirementId, Request $request, RenameRequirement $renameRequirement): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $requirement = $renameRequirement->execute($requirementId, $data['name'] ?? '');
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'id' => $requirement->getId()->toRfc4122(),
            'name' => $requirement->getName(),
        ]);
    }

    #[Route('/{requirementId}', methods: ['DELETE'])]
    public function delete(string $requirementId): JsonResponse
    {
        $requirement = $this->requirementRepository->findById($requirementId);
        if (null === $requirement) {
            return new JsonResponse(['error' => 'Requirement not found.'], Response::HTTP_NOT_FOUND);
        }

        $this->requirementRepository->delete($requirement);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/user', methods: ['GET'])]
    public function myRequirements(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse(array_map(fn (Requirement $r) => [
            'id' => $r->getId()->toRfc4122(),
            'name' => $r->getName(),
        ], $user->getRequirements()->toArray()));
    }

    #[Route('/user/{requirementId}', methods: ['POST'])]
    public function addToUser(string $requirementId): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $requirement = $this->requirementRepository->findById($requirementId);

        if (null === $requirement) {
            return new JsonResponse(['error' => 'Requirement not found.'], Response::HTTP_NOT_FOUND);
        }

        $user->addRequirement($requirement);
        $this->userRepository->save($user);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/user/{requirementId}', methods: ['DELETE'])]
    public function removeFromUser(string $requirementId): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $requirement = $this->requirementRepository->findById($requirementId);

        if (null === $requirement) {
            return new JsonResponse(['error' => 'Requirement not found.'], Response::HTTP_NOT_FOUND);
        }

        $user->removeRequirement($requirement);
        $this->userRepository->save($user);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
