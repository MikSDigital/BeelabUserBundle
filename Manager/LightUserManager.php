<?php

namespace Beelab\UserBundle\Manager;

use Beelab\UserBundle\User\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Light User manager.
 */
class LightUserManager
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $repository;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoder;

    /**
     * @param string                  $class
     * @param ObjectManager           $em
     * @param EncoderFactoryInterface $encoder
     */
    public function __construct(string $class, ObjectManager $em, EncoderFactoryInterface $encoder)
    {
        $this->className = $class;
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->encoder = $encoder;
    }

    /**
     * Get new instance of User.
     *
     * @return UserInterface
     */
    public function getInstance(): UserInterface
    {
        return new $this->className();
    }

    /**
     * Create new user.
     *
     * @param UserInterface $user
     * @param bool          $flush
     */
    public function create(UserInterface $user, bool $flush = true)
    {
        $this->updatePassword($user);
        $this->em->persist($user);
        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * Update existing user.
     *
     * @param UserInterface $user
     * @param bool          $flush
     */
    public function update(UserInterface $user, bool $flush = true)
    {
        if (!is_null($user->getPlainPassword())) {
            $this->updatePassword($user);
        }
        if ($flush) {
            $this->em->flush();
        }
    }

    /**
     * Password update.
     *
     * @param UserInterface $user
     */
    protected function updatePassword(UserInterface $user)
    {
        $passwordEncoder = $this->encoder->getEncoder($user);
        $password = $passwordEncoder->encodePassword($user->getPlainPassword(), $user->getSalt());
        $user->setPassword($password);
    }
}
