<?php

namespace TaskManagement\Service;

use People\Entity\Organization;
use Rhumsaa\Uuid\Uuid;
use TaskManagement\Task;

/**
 * TODO: Rename in TaskRepository?
 */
interface TaskService
{
	/**
	 * 
	 * @param Task $task
	 * @return Task
	 */
	public function addTask(Task $task);
	/**
	 * 
	 * @param string|Uuid $id
	 * @return Task|null
	 */
	public function getTask($id);

	/**
	 * Get the list of all available tasks in the $offset - $limit interval
	 *
	 * @param Organization|ReadModelOrganization|String|Uuid $organization
	 * @param integer $offset
	 * @param integer $limit
	 * @param array $filters
	 * @return Task[]
	 */
	public function findTasks($organization, $offset, $limit, $filters, $sorting=null);

	/**
	 * @param string|Uuid $id
	 * @return Task|null
	 */
	public function findTask($id);

	/**
	 * Find accepted tasks with accepted date before $interval days from now
	 * @param \DateInterval $interval
	 * @return array
	 */
	public function findAcceptedTasksBefore(\DateInterval $interval);

	/**
	 * Find accepted tasks with accepted date before $before days from now
	 * and after $after days since now (in the past)
	 * @param \DateInterval $after
	 * @param \DateInterval $before
	 * @return array
	 */
	public function findIdeasCreatedBetween(\DateInterval $after, \DateInterval $before);

	/**
	 * Get the number of tasks of an $organization
	 * @param Organization $organization
	 * @param \DateTime $filters["startOn"]
	 * @param \DateTime $filters["endOn"]
	 * @param String $filters["memberId"]
	 * @param String $filters["memberEmail"]
	 * @return integer
	 */
	public function countOrganizationTasks(Organization $organization, $filters);

	/**
	 * Get tasks statistics for $memberId 
	 * @param Organization $org
	 * @param string $memberId
	 * @param \DateTime $filters["startOn"]
	 * @param \DateTime $filters["endOn"]
	 */
	public function findMemberStats(Organization $org, $memberId, $filters);
	
	/**
	 * Find items with creation date before $interval days from now
	 * @param \DateInterval $interval
	 * @param string $status
	 */
	public function findItemsBefore(\DateInterval $interval, $status);
	
	/**
	 * Count votes on item idea to open or archive it
	 * @param int item status from TaskInterface
	 * @param string|Uuid $id
	 */
	public function countVotesForItem($itemStatus, $id);
}