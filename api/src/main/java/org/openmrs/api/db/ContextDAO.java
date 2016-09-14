/**
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/. OpenMRS is also distributed under
 * the terms of the Healthcare Disclaimer located at http://openmrs.org/license.
 *
 * Copyright (C) OpenMRS Inc. OpenMRS is a registered trademark and the OpenMRS
 * graphic logo is a trademark of OpenMRS Inc.
 */
package org.openmrs.api.db;

import java.util.Properties;
import java.util.concurrent.Future;

import org.openmrs.User;
import org.openmrs.api.context.Context;
import org.openmrs.api.context.ContextAuthenticationException;
import org.openmrs.util.OpenmrsConstants;

/**
 * Defines the functions that the Context needs to access the database
 */
public interface ContextDAO {
	
	/**
	 * Authenticate user with the given username and password.
	 * 
	 * @param username user's username or systemId
	 * @param password user's password
	 * @return a valid user if authentication succeeds
	 * @throws ContextAuthenticationException
	 * @should authenticate given username and password
	 * @should authenticate given systemId and password
	 * @should authenticate given systemId without hyphen and password
	 * @should not authenticate given username and incorrect password
	 * @should not authenticate given systemId and incorrect password
	 * @should not authenticate given incorrect username
	 * @should not authenticate given incorrect systemId
	 * @should not authenticate given null login
	 * @should not authenticate given empty login
	 * @should not authenticate given null password when password in database is null
	 * @should not authenticate given non null password when password in database is null
	 * @should not authenticate when password in database is empty
	 * @should give identical error messages between username and password mismatch
	 * @should lockout user after eight failed attempts
	 * @should authenticateWithCorrectHashedPassword
	 * @should authenticateWithIncorrectHashedPassword
	 * @should set uuid on user property when authentication fails with valid user
	 * @should pass regression test for 1580
	 * @should throw a ContextAuthenticationException if username is an empty string
	 * @should throw a ContextAuthenticationException if username is white space
	 */
	public User authenticate(String username, String password) throws ContextAuthenticationException;
	
	/**
	 * Gets a user given the uuid. Privilege checks are not done here because this is used by the
	 * {@link Context#getAuthenticatedUser()} method.
	 * 
	 * @param uuid uuid of user to fetch
	 * @return the User from the database
	 * @throws ContextAuthenticationException
	 */
	public User getUserByUuid(String uuid) throws ContextAuthenticationException;
	
	/**
	 * Open session.
	 */
	public void openSession();
	
	/**
	 * Close session.
	 */
	public void closeSession();
	
	/**
	 * @see org.openmrs.api.context.Context#clearSession()
	 */
	public void clearSession();
	
	/**
	 * @see org.openmrs.api.context.Context#flushSession()
	 */
	public void flushSession();
	
	/**
	 * Used to clear a cached object out of a session in the middle of a unit of work. Future
	 * updates to this object will not be saved. Future gets of this object will not fetch this
	 * cached copy
	 * 
	 * @param obj The object to evict/remove from the session
	 * @see org.openmrs.api.context.Context#evictFromSession(Object)
	 */
	public void evictFromSession(Object obj);
	
	/**
	 * Starts the OpenMRS System
	 * <p>
	 * Should be called prior to any kind of activity
	 * 
	 * @param props Properties
	 */
	public void startup(Properties props);
	
	/**
	 * Stops the OpenMRS System Should be called after all activity has ended and application is
	 * closing
	 */
	public void shutdown();
	
	/**
	 * Merge in the default properties defined for this database connection
	 * 
	 * @param runtimeProperties The current user specific runtime properties
	 */
	public void mergeDefaultRuntimeProperties(Properties runtimeProperties);
	
	/**
	 * Updates the search index if necessary.
	 * <p>
	 * The update is triggered if {@link OpenmrsConstants#GP_SEARCH_INDEX_VERSION} is blank
	 * or the value does not match {@link OpenmrsConstants#SEARCH_INDEX_VERSION}.
	 */
	public void setupSearchIndex();
	
	/**
	 * @see Context#updateSearchIndex()
	 */
	public void updateSearchIndex();

	/**
	 * @see Context#updateSearchIndexAsync()
	 */
	public Future<?> updateSearchIndexAsync();
	
	/**
	 * @see Context#updateSearchIndexForObject(Object)
	 */
	public void updateSearchIndexForObject(Object object);
	
	/**
	 * @see Context#updateSearchIndexForType(Class)
	 */
	public void updateSearchIndexForType(Class<?> type);
}
