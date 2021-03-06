<?php
/**
 * ownCloud
 *
 * @author Jakob Sack
 * @copyright 2012 Jakob Sack owncloud@jakobsack.de
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Public interface of ownCloud for background jobs.
 */

// use OCP namespace for all classes that are considered public.
// This means that they should be used by apps instead of the internal ownCloud classes
namespace OCP;

use \OC\BackgroundJob\JobList;

/**
 * This class provides functions to register backgroundjobs in ownCloud
 *
 * To create a new backgroundjob create a new class that inharits from either \OC\BackgroundJob\Job,
 * \OC\BackgroundJob\QueuedJob or \OC\BackgroundJob\TimedJob and register it using
 * \OCP\BackgroundJob->registerJob($job, $argument), $argument will be passed to the run() function
 * of the job when the job is executed.
 *
 * A regular Job will be executed every time cron.php is run, a QueuedJob will only run once and a TimedJob
 * will only run at a specific interval which is to be specified in the constructor of the job by calling
 * $this->setInterval($interval) with $interval in seconds.
 */
class BackgroundJob {
	/**
	 * @brief get the execution type of background jobs
	 * @return string
	 *
	 * This method returns the type how background jobs are executed. If the user
	 * did not select something, the type is ajax.
	 */
	public static function getExecutionType() {
		return \OC_BackgroundJob::getExecutionType();
	}

	/**
	 * @brief sets the background jobs execution type
	 * @param string $type execution type
	 * @return boolean
	 *
	 * This method sets the execution type of the background jobs. Possible types
	 * are "none", "ajax", "webcron", "cron"
	 */
	public static function setExecutionType($type) {
		return \OC_BackgroundJob::setExecutionType($type);
	}

	/**
	 * @param \OC\BackgroundJob\Job|string $job
	 * @param mixed $argument
	 */
	public static function registerJob($job, $argument = null) {
		$jobList = new JobList();
		$jobList->add($job, $argument);
	}

	/**
	 * @deprecated
	 * @brief creates a regular task
	 * @param string $klass class name
	 * @param string $method method name
	 * @return true
	 */
	public static function addRegularTask($klass, $method) {
		self::registerJob('OC\BackgroundJob\Legacy\RegularJob', array($klass, $method));
		return true;
	}

	/**
	 * @deprecated
	 * @brief gets all regular tasks
	 * @return associative array
	 *
	 * key is string "$klass-$method", value is array( $klass, $method )
	 */
	static public function allRegularTasks() {
		$jobList = new JobList();
		$allJobs = $jobList->getAll();
		$regularJobs = array();
		foreach ($allJobs as $job) {
			if ($job instanceof RegularLegacyJob) {
				$key = implode('-', $job->getArgument());
				$regularJobs[$key] = $job->getArgument();
			}
		}
		return $regularJobs;
	}

	/**
	 * @deprecated
	 * @brief Gets one queued task
	 * @param int $id ID of the task
	 * @return associative array
	 */
	public static function findQueuedTask($id) {
		$jobList = new JobList();
		return $jobList->getById($id);
	}

	/**
	 * @deprecated
	 * @brief Gets all queued tasks
	 * @return array with associative arrays
	 */
	public static function allQueuedTasks() {
		$jobList = new JobList();
		$allJobs = $jobList->getAll();
		$queuedJobs = array();
		foreach ($allJobs as $job) {
			if ($job instanceof QueuedLegacyJob) {
				$queuedJob = $job->getArgument();
				$queuedJob['id'] = $job->getId();
				$queuedJobs[] = $queuedJob;
			}
		}
		return $queuedJobs;
	}

	/**
	 * @deprecated
	 * @brief Gets all queued tasks of a specific app
	 * @param string $app app name
	 * @return array with associative arrays
	 */
	public static function queuedTaskWhereAppIs($app) {
		$jobList = new JobList();
		$allJobs = $jobList->getAll();
		$queuedJobs = array();
		foreach ($allJobs as $job) {
			if ($job instanceof QueuedLegacyJob) {
				$queuedJob = $job->getArgument();
				$queuedJob['id'] = $job->getId();
				if ($queuedJob['app'] === $app) {
					$queuedJobs[] = $queuedJob;
				}
			}
		}
		return $queuedJobs;
	}

	/**
	 * @deprecated
	 * @brief queues a task
	 * @param string $app app name
	 * @param string $class class name
	 * @param string $method method name
	 * @param string $parameters all useful data as text
	 * @return int id of task
	 */
	public static function addQueuedTask($app, $class, $method, $parameters) {
		self::registerJob('OC\BackgroundJob\Legacy\QueuedJob', array('app' => $app, 'klass' => $class, 'method' => $method, 'parameters' => $parameters));
		return true;
	}

	/**
	 * @deprecated
	 * @brief deletes a queued task
	 * @param int $id id of task
	 * @return bool
	 *
	 * Deletes a report
	 */
	public static function deleteQueuedTask($id) {
		$jobList = new JobList();
		$job = $jobList->getById($id);
		if ($job) {
			$jobList->remove($job);
		}
	}
}
