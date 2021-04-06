<?php

/**
 * @file classes/services/QueryBuilders/SubmissionQueryBuilder.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionQueryBuilder
 * @ingroup query_builders
 *
 * @brief Submission list Query builder
 */

namespace APP\Services\QueryBuilders;

use Illuminate\Database\Capsule\Manager as Capsule;

class SubmissionQueryBuilder extends \PKP\Services\QueryBuilders\PKPSubmissionQueryBuilder {

	/** @var int|array Issue ID(s) */
	protected $issueIds = null;

	/** @var int|array Section ID(s) */
	protected $sectionIds = null;

	protected $filterByUserState = false;
	protected $stateWhereDecl = null;
	protected $stateWhere = null;

	/**
	 * Set issue filter
	 *
	 * @param int|array $issueIds
	 *
	 * @return \APP\Services\QueryBuilders\SubmissionQueryBuilder
	 */
	public function filterByIssues($issueIds) {
		if (!is_null($issueIds) && !is_array($issueIds)) {
			$issueIds = [$issueIds];
		}
		$this->issueIds = $issueIds;
		return $this;
	}

	/**
	 * Set section filter
	 *
	 * @param int|array $sectionIds
	 *
	 * @return \APP\Services\QueryBuilders\SubmissionQueryBuilder
	 */
	public function filterBySections($sectionIds) {
		if (!is_null($sectionIds) && !is_array($sectionIds)) {
			$sectionIds = [$sectionIds];
		}
		$this->sectionIds = $sectionIds;
		return $this;
	}

	/**
	 * Execute additional actions for app-specific query objects
	 * @return object Query object
	 */
	public function appGet($q) {

		if (!empty($this->issueIds)) {
			$issueIds = $this->issueIds;
			$q->leftJoin('publications as issue_p', 'issue_p.submission_id', '=', 's.submission_id')
				->leftJoin('publication_settings as issue_ps','issue_p.publication_id','=','issue_ps.publication_id')
				->where(function($q) use ($issueIds) {
					$q->where('issue_ps.setting_name', '=', 'issueId');
					$q->whereIn('issue_ps.setting_value', $issueIds);
				});
		}

		if (!empty($this->sectionIds)) {
			$sectionIds = $this->sectionIds;
			$q->leftJoin('publications as section_p', 'section_p.submission_id', '=', 's.submission_id')
				->whereIn('section_p.section_id', $sectionIds);
		}

		$leftJoinAuthor = False;

		if ($this->filterByUserState) {
			if (!$leftJoinAuthor) $q->leftJoin('publications as p', 'p.submission_id', '=', 's.submission_id')
			->leftJoin('authors as au','p.publication_id','=','au.publication_id');

			$q->leftJoin('users as us','us.user_id','=','au.author_id')
				->leftJoin('user_settings as uss','uss.user_id','=','us.user_id')
				->where('uss.setting_name', '=', 'state');

				if ($this->stateWhereDecl == 'NOTIN') $q->whereNotIn('uss.setting_value', $this->stateWhere);
				else $q->whereIn('uss.setting_value', $this->stateWhere);
		}

		return $q;
	}

	/**
	 * Set state filter
	 *
	 * @param int|array|null $states
	 *
	 * @return \OMP\Services\QueryBuilders\SubmissionListQueryBuilder
	 */
	public function filterByStates($states, $clause = 'IN') {
		$this->filterByUserState = !is_null($states);
		if (!is_null($states) && !is_array($states)) {
			$states = array($states);
		}
		// error_log('TESTEEEEEE');
		$this->stateWhereDecl = (!is_null($clause) ? $clause : 'IN');
		$this->stateWhere = $states;
		return $this;
	}
}
