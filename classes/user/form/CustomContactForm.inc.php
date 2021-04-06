<?php

/**
 * @file classes/services/SubmissionService.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionService
 * @ingroup services
 *
 * @brief Extends the base submission helper service class with app-specific
 *  requirements.
 */

import('lib.pkp.classes.user.form.ContactForm');

class CustomContactForm extends ContactForm {

	/**
	 * Initialize hooks for extending ContactForm
	 */
	public function __construct() {
		\HookRegistry::register('API::submissions::fetch::variables', array($this, 'addCustomVariables'));
	}

	/**
	 * @copydoc ContactForm::fetch()
	 */
	public function addCustomVariables($templateMgr) {
		$earth = new \MenaraSolutions\Geographer\Earth();
		$states = $earth->findOneByCode('BR')->getStates()->sortBy('name')->pluck('name');
		asort($states);
		$states[27] = 'Outro';

		$templateMgr->assign(array(
			'states' => $states,
		));

		return $templateMgr;
	}
}
