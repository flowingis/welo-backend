<?php

namespace TaskManagement;
use TaskManagement\Controller\AcceptancesController;
use TaskManagement\Controller\ApprovalsController;
use TaskManagement\Controller\AttachmentsController;
use TaskManagement\Controller\EstimationsController;
use TaskManagement\Controller\MembersController;
use TaskManagement\Controller\OwnerController;
use TaskManagement\Controller\MemberStatsController;
use TaskManagement\Controller\RemindersController;
use TaskManagement\Controller\Console\RemindersController as ConsoleRemindersController;
use TaskManagement\Controller\Console\VotingResultsController as ConsoleVotingsController;
use TaskManagement\Controller\SharesController;
use TaskManagement\Controller\StreamsController;
use TaskManagement\Controller\TasksController;
use TaskManagement\Controller\TransitionsController;
use TaskManagement\Controller\VotingResultsController;
use TaskManagement\Controller\HistoryController;
use TaskManagement\Service\AssignCreditsListener;
use TaskManagement\Service\CloseTaskListener;
use TaskManagement\Service\EventSourcingStreamService;
use TaskManagement\Service\EventSourcingTaskService;
use TaskManagement\Service\NotifyMailListener;
use TaskManagement\Service\StreamCommandsListener;
use TaskManagement\Service\TaskCommandsListener;
use TaskManagement\Service\TransferCreditsListener;
use TaskManagement\Service\CloseItemIdeaListener;
use TaskManagement\Service\AcceptCompletedItemListener;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Kanbanize\Service;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
	public function getControllerConfig()
	{
		return [
			'factories' => [
				'TaskManagement\Controller\Tasks' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$streamService = $locator->get('TaskManagement\StreamService');
					$organizationService = $locator->get('People\OrganizationService');
					$kanbanizeService = $locator->get('Kanbanize\KanbanizeService');

					$controller = new TasksController(
						$taskService,
						$streamService,
						$organizationService,
						$kanbanizeService
					);

					return $controller;
				},
				'TaskManagement\Controller\Members' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
					$taskService = $locator->get('TaskManagement\TaskService');
					$userService = $locator->get('Application\UserService');
					$controller = new MembersController($orgService, $taskService, $userService);
					return $controller;
				},
				'TaskManagement\Controller\Owner' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
					$taskService = $locator->get('TaskManagement\TaskService');
					$userService = $locator->get('Application\UserService');
					$controller = new OwnerController($orgService, $taskService, $userService);
					return $controller;
				},
				'TaskManagement\Controller\Transitions' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$orgService = $locator->get('People\OrganizationService');

					$controller = new TransitionsController(
						$taskService,
						$orgService
					);

					return $controller;
				},
				'TaskManagement\Controller\Estimations' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new EstimationsController($taskService);
					return $controller;
				},
				'TaskManagement\Controller\Shares' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new SharesController($taskService);
					return $controller;
				},
				'TaskManagement\Controller\Streams' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$streamService = $locator->get('TaskManagement\StreamService');
					$organizationService = $locator->get('People\OrganizationService');
					$controller = new StreamsController($streamService, $organizationService);
					return $controller;
				},
				'TaskManagement\Controller\Reminders' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$notificationService = $locator->get('TaskManagement\NotifyMailListener');
					$taskService = $locator->get('TaskManagement\TaskService');
					$orgService = $locator->get('People\OrganizationService');

					$controller = new RemindersController(
						$notificationService,
						$taskService,
						$orgService);

					return $controller;
				},
				'TaskManagement\Controller\MemberStats' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$orgService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new MemberStatsController($orgService, $taskService, $userService);
					return $controller;
				},
				'TaskManagement\Controller\VotingResults' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$orgService = $locator->get('People\OrganizationService');

					$controller = new VotingResultsController(
						$taskService,
						$orgService
					);

					return $controller;
				},
				'TaskManagement\Controller\Approvals' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new ApprovalsController($taskService);
					return $controller;
				},
				'TaskManagement\Controller\Attachments' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new AttachmentsController($taskService);
					return $controller;
				},
				'TaskManagement\Controller\Acceptances' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new AcceptancesController($taskService);
					return $controller;
				},
				'TaskManagement\Controller\Console\Reminders' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$orgService = $locator->get('People\OrganizationService');
					$mailService = $locator->get('AcMailer\Service\MailService');

					$controller = new ConsoleRemindersController(
						$taskService,
						$mailService,
						$orgService
					);

					$config = $locator->get('Config');
					if(isset($config['mail_domain'])) {
						$controller->setHost($config['mail_domain']);
					}

					return $controller;
				},
				'TaskManagement\Controller\Console\VotingResults' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$orgService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');

					$controller = new ConsoleVotingsController(
						$taskService,
						$orgService,
						$userService
					);

					return $controller;
				},
				'TaskManagement\Controller\History' => function ($sm) {
					$locator = $sm->getServiceLocator();
					$taskService = $locator->get('TaskManagement\TaskService');
					$controller = new HistoryController($taskService);
					return $controller;
				}
			]
		];
	}

	public function getServiceConfig()
	{
		return [
			'factories' => [
				'TaskManagement\StreamService' => function ($locator) {
					$eventStore = $locator->get('prooph.event_store');
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingStreamService($eventStore, $entityManager);
				},
				'TaskManagement\NotifyMailListener'=> function ($locator){
					$mailService = $locator->get('AcMailer\Service\MailService');
					$userService = $locator->get('Application\UserService');
					$taskService = $locator->get('TaskManagement\TaskService');
					$orgService = $locator->get('People\OrganizationService');
					$rv = new NotifyMailListener($mailService, $userService, $taskService, $orgService);
					$config = $locator->get('Config');
					if(isset($config['mail_domain'])) {
						$rv->setHost($config['mail_domain']);
					}
					return $rv;
				},
				'TaskManagement\TaskService' => function ($locator) {
					$eventStore = $locator->get('prooph.event_store');
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new EventSourcingTaskService($eventStore, $entityManager);
				},
				'TaskManagement\TaskCommandsListener' => function ($locator) {
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					$kanbanizeService = $locator->get('Kanbanize\KanbanizeService');
					$orgService = $locator->get('People\OrganizationService');
					return new TaskCommandsListener($entityManager,$kanbanizeService,$orgService);
				},
				'TaskManagement\StreamCommandsListener' => function ($locator) {
					$entityManager = $locator->get('doctrine.entitymanager.orm_default');
					return new StreamCommandsListener($entityManager);
				},
				'TaskManagement\TransferCreditsListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$transactionManager = $locator->get('prooph.event_store');
					$organizationService = $locator->get('People\OrganizationService');
					$accountService = $locator->get('Accounting\CreditsAccountsService');
					$userService = $locator->get('Application\UserService');
					return new TransferCreditsListener($taskService, $organizationService, $accountService, $userService, $transactionManager);
				},
				'TaskManagement\CloseTaskListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$userService = $locator->get('Application\UserService');
					$transactionManager = $locator->get('prooph.event_store');
					return new CloseTaskListener($taskService, $userService, $transactionManager);
				},
				'TaskManagement\CloseItemIdeaListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$organizationService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');
					$transactionManager = $locator->get('prooph.event_store');
					return new CloseItemIdeaListener($taskService,$userService, $organizationService, $transactionManager);
				},
				'TaskManagement\AcceptCompletedItemListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$organizationService = $locator->get('People\OrganizationService');
					$userService = $locator->get('Application\UserService');
					$transactionManager = $locator->get('prooph.event_store');
					return new AcceptCompletedItemListener($taskService,$userService, $organizationService, $transactionManager);
				},
				'TaskManagement\AssignCreditsListener' => function ($locator) {
					$taskService = $locator->get('TaskManagement\TaskService');
					$userService = $locator->get('Application\UserService');
					$transactionManager = $locator->get('prooph.event_store');
					return new AssignCreditsListener($taskService, $userService, $transactionManager);
				}
			],
		];
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig()
	{
		return array(
			'Zend\Loader\ClassMapAutoloader' => array(
					__DIR__ . '/autoload_classmap.php',
			),
			'Zend\Loader\StandardAutoloader' => array(
					'namespaces' => array(
							__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
					)
			)
		);
	}
}