<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\ChangeMemberRole;
use GlobalEmergency\Apuntate\Application\Services\InviteMember;
use GlobalEmergency\Apuntate\Application\Services\ListMembers;
use GlobalEmergency\Apuntate\Application\Services\RemoveMember;
use GlobalEmergency\Apuntate\Entity\OrganizationMember;
use GlobalEmergency\Apuntate\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/organizations/{organizationId}/members', name: 'api_members_')]
final class MembersController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(string $organizationId, ListMembers $listMembers): JsonResponse
    {
        try {
            $members = $listMembers->execute($organizationId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(array_map(fn (OrganizationMember $m) => $this->serialize($m), $members));
    }

    #[Route('', name: 'invite', methods: ['POST'])]
    public function invite(string $organizationId, Request $request, InviteMember $inviteMember): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $member = $inviteMember->execute(
                $organizationId,
                $data['email'] ?? '',
                $data['name'] ?? '',
                $data['surname'] ?? '',
                $data['role'] ?? OrganizationMember::ROLE_MEMBER,
                $data['password'] ?? null,
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serialize($member), Response::HTTP_CREATED);
    }

    #[Route('/{userId}', name: 'remove', methods: ['DELETE'])]
    public function remove(string $organizationId, string $userId, RemoveMember $removeMember): JsonResponse
    {
        try {
            $removeMember->execute($organizationId, $userId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{userId}/role', name: 'change_role', methods: ['PUT'])]
    public function changeRole(
        string $organizationId,
        string $userId,
        Request $request,
        ChangeMemberRole $changeMemberRole,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $member = $changeMemberRole->execute(
                $organizationId,
                $userId,
                $data['role'] ?? '',
            );
        } catch (\DomainException|\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serialize($member));
    }

    private function serialize(OrganizationMember $m): array
    {
        $user = $m->getUser();

        return [
            'id' => $m->getId()->toRfc4122(),
            'role' => $m->getRole(),
            'user' => [
                'id' => $user->getId()->toRfc4122(),
                'name' => $user->getName(),
                'surname' => $user->getSurname(),
                'email' => $user->getEmail(),
                'dateStart' => $user->getDateStart()?->format('Y-m-d'),
                'requirements' => array_map(fn ($r) => [
                    'id' => $r->getId()->toRfc4122(),
                    'name' => $r->getName(),
                ], $user->getRequirements()->toArray()),
            ],
        ];
    }
}
