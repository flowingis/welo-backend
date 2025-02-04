<?php
namespace People;

use People\Projector\OrganizationMembershipProjector;
use People\Projector\OrganizationProjector;

return array(
	'router' => array(
		'routes' => array(
            'user' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/users[/:id]',
                    'defaults' => [
                        '__NAMESPACE__' => 'People\Controller',
                        'controller' => 'Users',
                    ],
                ],
            ],
			'invites' => [
				'type' => 'Segment',
				'options' => [
					'route' => '/organizations/acceptinvite',
					'defaults' => [
						'__NAMESPACE__' => 'People\Controller',
						'controller' => 'Organizations',
					],
				],
			],
			'organizations' => [
				'type' => 'Segment',
				'options' => [
					'route' => '/organizations[/:id][/:controller]',
					'constraints' => [
						'id' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})'
					],
					'defaults' => [
						'__NAMESPACE__' => 'People\Controller',
						'controller' => 'Organizations',
					],
				],
			],
			'lanesettings' => [
				'type' => 'Segment',
				'options' => [
					'route' => '/:orgId/settings/lanes[/:id]',
					'constraints' => [
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
                        'id'    => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})'
                    ],
					'defaults' => [
						'__NAMESPACE__' => 'People\Controller',
						'controller' => 'LanesSettings',
					],
				],
			],
			'members' => [
				'type' => 'Segment',
				'options' => [
					'route' => '/:orgId/people/members[/:id][/:controller]',
					'constraints' => [
						'orgId' => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})',
						'id'    => '([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})'
					],
					'defaults' => [
						'__NAMESPACE__' => 'People\Controller',
						'controller'    => 'Members'
					],
				],
			]
		),
	),
	'view_manager' => array(
		'strategies' => array(
			'ViewJsonStrategy',
		),
		'template_path_stack' => array(
			__NAMESPACE__ => __DIR__ . '/../view',
		)
	),
	'service_manager' => array(
		'invokables' => array(
			'People\Assertion\MemberOfOrganizationAssertion' => 'People\Assertion\MemberOfOrganizationAssertion',
		),
	),
	'doctrine' => array(
		'driver' => array(
			 __NAMESPACE__ . '_driver' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../src/'. __NAMESPACE__ . '/Entity')
			),
			'orm_default' => array(
				'drivers' => array(
					__NAMESPACE__ . '\Entity' =>  __NAMESPACE__ . '_driver'
				)
			)
		)
	),
	'listeners' => array(
		'People\OrganizationCommandsListener',
        OrganizationProjector::class,
        OrganizationMembershipProjector::class,
		'People\SendMailListener'
	),
	'default_members_limit' => 20
);
