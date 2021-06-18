<?php

namespace Riddlestone\Brokkr\Users\Mvc\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Laminas\View\Model\ViewModel;
use Riddlestone\Brokkr\Users\Repository\UserRepository;

class UsersController extends AbstractActionController
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @param UserRepository $userRepository
     */
    public function setUserRepository(UserRepository $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    public function indexAction()
    {
        $page = $this->params()->fromQuery('page', 1);
        $perPage = 50;
        $query = $this->userRepository->createQueryBuilder('user')
            ->orderBy('user.lastName', 'ASC')
            ->addOrderBy('user.firstName', 'ASC')
            ->setMaxResults($perPage)
            ->setFirstResult($perPage * ($page - 1));
        $pagination = new Paginator($query->getQuery());
        $viewModel = new ViewModel([
            'pages' => ceil(count($pagination) / $perPage),
            'page' => $page,
            'users' => iterator_to_array($pagination),
        ]);
        $viewModel->setTemplate('brokkr/users/users/index');
        return $viewModel;
    }
}
