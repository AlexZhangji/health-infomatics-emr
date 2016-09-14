/**
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/. OpenMRS is also distributed under
 * the terms of the Healthcare Disclaimer located at http://openmrs.org/license.
 *
 * Copyright (C) OpenMRS Inc. OpenMRS is a registered trademark and the OpenMRS
 * graphic logo is a trademark of OpenMRS Inc.
 */
package org.openmrs.web.filter.update;

import java.util.List;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;
import org.openmrs.util.DatabaseUpdater;
import org.openmrs.util.DatabaseUpdater.OpenMRSChangeSet;
import org.openmrs.util.OpenmrsConstants;
import org.openmrs.util.RoleConstants;
import org.openmrs.web.WebConstants;
import org.openmrs.web.filter.StartupFilter;

/**
 * The {@link UpdateFilter} uses this model object to hold all properties that are edited by the
 * user in the wizard. All attributes on this model object are added to all templates rendered by
 * the {@link StartupFilter}.
 */
public class UpdateFilterModel {
	
	// automatically given to the .vm files and used there
	public String headerTemplate = "org/openmrs/web/filter/update/header.vm";
	
	// automatically given to the .vm files and used there
	public String footerTemplate = "org/openmrs/web/filter/update/footer.vm";
	
	public List<OpenMRSChangeSet> changes = null;
	
	public String superuserrole = RoleConstants.SUPERUSER;
	
	public String setupPageUrl = WebConstants.SETUP_PAGE_URL;
	
	public static final String OPENMRS_VERSION = OpenmrsConstants.OPENMRS_VERSION_SHORT;
	
	public Boolean updateRequired = false;
	
	/**
	 * Default constructor that sets up some of the properties
	 */
	public UpdateFilterModel() {
		updateChanges();
		
		try {
			if (changes != null && changes.size() > 0) {
				updateRequired = true;
			} else {
				updateRequired = DatabaseUpdater.updatesRequired();
			}
		}
		catch (Exception e) {
			// do nothing
		}
	}
	
	/**
	 * Convenience method that reads from liquibase again to get the most recent list of changesets
	 * that still need to be run.
	 */
	public void updateChanges() {
		Log log = LogFactory.getLog(getClass());
		
		try {
			changes = DatabaseUpdater.getUnrunDatabaseChanges();
			
			// not sure why this is necessary...
			if (changes == null && DatabaseUpdater.isLocked()) {
				changes = DatabaseUpdater.getUnrunDatabaseChanges();
			}
		}
		catch (Exception e) {
			log.error("Unable to get the database changes", e);
		}
	}
	
}
