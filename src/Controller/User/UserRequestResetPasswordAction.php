<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Component\SecretKey\SecretKeyFactory;
use App\Component\SecretKey\SecretKeyManager;
use App\Controller\Base\AbstractController;
use App\Entity\User;
use App\Message\GreetingResetPasswordByEmail;
use App\Repository\SecretKeyRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class UserRequestResetPasswordAction extends AbstractController
{
    public function __invoke(
        Request $request,
        SecretKeyFactory $secretKeyFactory,
        SecretKeyManager $secretKeyManager,
        UserRepository $userRepository,
        SecretKeyRepository $secretKeyRepository,
        MessageBusInterface $messageBus,
    ): JsonResponse {
        $this->validate($request);
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $user = $userRepository->findOneByEmail($email);

        if ($user === null) {
            throw new BadRequestHttpException('This user does not exist in the system!');
        }

        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            throw new BadRequestHttpException('You are not Admin');
        }

        $this->checkExistingToken($secretKeyRepository, $user);
        $secretKey = $secretKeyFactory->create($user);

        $secretKeyManager->save($secretKey, true);
        $url = $request->headers->get('referer');

        $messageBus->dispatch(new GreetingResetPasswordByEmail($email, $secretKey->getSecretKey(), $url));

        return new JsonResponse(['message' => "A link to reset your password has been sent to your email."]);
    }

    private function checkExistingToken(
        SecretKeyRepository $secretKeyRepository,
        User $user
    ): void {
        $secretKey = $secretKeyRepository->findOneByUser($user);

        if ($secretKey) {
            $secretKeyRepository->remove($secretKey);
        }
    }
}
