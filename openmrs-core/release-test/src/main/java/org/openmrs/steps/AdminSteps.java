/**
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/. OpenMRS is also distributed under
 * the terms of the Healthcare Disclaimer located at http://openmrs.org/license.
 *
 * Copyright (C) OpenMRS Inc. OpenMRS is a registered trademark and the OpenMRS
 * graphic logo is a trademark of OpenMRS Inc.
 */
package org.openmrs.steps;

import org.jbehave.core.annotations.Given;
import org.jbehave.core.annotations.Then;
import org.openmrs.Steps;
import org.openqa.selenium.WebDriver;

import static org.hamcrest.Matchers.equalTo;
import static org.openqa.selenium.lift.Finders.title;
import static org.openqa.selenium.lift.Matchers.text;

public class AdminSteps extends Steps {

	public AdminSteps(WebDriver driver) {
		super(driver);
	}

	@Given("I am on $title screen")
	public void onHomePage(String title) {
		assertPresenceOf(title().with(text(equalTo("OpenMRS - " + title))));
	}

	@Then("take me to $title page")
	public void verifyAdminPage(String title) throws InterruptedException {
        Thread.sleep(5000);
        assertPresenceOf(title().with(text(equalTo("OpenMRS - " + title))));
	}

}
