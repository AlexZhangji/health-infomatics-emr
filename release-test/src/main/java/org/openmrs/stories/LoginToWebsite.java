/**
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/. OpenMRS is also distributed under
 * the terms of the Healthcare Disclaimer located at http://openmrs.org/license.
 *
 * Copyright (C) OpenMRS Inc. OpenMRS is a registered trademark and the OpenMRS
 * graphic logo is a trademark of OpenMRS Inc.
 */
package org.openmrs.stories;

import org.openmrs.Steps;
import org.openmrs.Story;
import org.openmrs.steps.LoginSteps;

import java.util.List;

import static java.util.Arrays.asList;

public class LoginToWebsite extends Story {

    @Override
    public List<Steps> includeSteps() {
        return asList((Steps) new LoginSteps(driver));
    }
}
