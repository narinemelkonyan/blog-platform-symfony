<?php

namespace App\Handler;

use App\DTO\FormResult;
use App\Entity\User;
use App\Form\ProfileType;
use App\Service\EmailQueueService;
use App\Service\ImageProcessorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProfileHandler
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly ImageProcessorService $imageProcessor,
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EmailQueueService $emailQueueService,
        private readonly string $uploadsDir,
    ) {}

    /**
     * Handles profile edit form submission.
     * Processes avatar upload if provided and redirects on success.
     */
    public function profile(Request $request, User $user): FormResult
    {
        $form = $this->formFactory->create(ProfileType::class, $user);
        $userEmail = $user->getEmail();
        $oldAvatar = null;

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $avatarFile = $form->get('avatar')->getData();
            if($avatarFile != null){
                $oldAvatar = $user->getAvatar();
                $filename = $this->imageProcessor->processAvatar($avatarFile, $this->uploadsDir);
                $user->setAvatar($filename);
            }
            if ($user->getEmail() !== $userEmail) {
                $user->setPendingEmail($user->getEmail());
                $user->setEmail($userEmail);
                $user->setEmailConfirmed(false);
            }
            $this->em->flush();

            if ($oldAvatar !== null) {
                $this->imageProcessor->deleteFile($oldAvatar, $this->uploadsDir);
            }

            if($user->getPendingEmail() !== null){
                $this->emailQueueService->sendEmailConfirmation($user);
                return FormResult::successWithEmailChange();
            }

           return FormResult::success();
        }

        return FormResult::failure($form);

    }

}
