/**
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/. OpenMRS is also distributed under
 * the terms of the Healthcare Disclaimer located at http://openmrs.org/license.
 *
 * Copyright (C) OpenMRS Inc. OpenMRS is a registered trademark and the OpenMRS
 * graphic logo is a trademark of OpenMRS Inc.
 */
package org.openmrs.api.impl;

import java.io.File;
import java.util.ArrayList;
import java.util.Date;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Vector;

import org.openmrs.Concept;
import org.openmrs.ConceptName;
import org.openmrs.Encounter;
import org.openmrs.Location;
import org.openmrs.Obs;
import org.openmrs.Patient;
import org.openmrs.Person;
import org.openmrs.aop.RequiredDataAdvice;
import org.openmrs.api.APIException;
import org.openmrs.api.EncounterService;
import org.openmrs.api.ObsService;
import org.openmrs.api.PatientService;
import org.openmrs.api.context.Context;
import org.openmrs.api.db.ObsDAO;
import org.openmrs.api.handler.SaveHandler;
import org.openmrs.obs.ComplexObsHandler;
import org.openmrs.obs.handler.AbstractHandler;
import org.openmrs.util.OpenmrsClassLoader;
import org.openmrs.util.OpenmrsConstants.PERSON_TYPE;
import org.openmrs.util.OpenmrsUtil;
import org.openmrs.util.PrivilegeConstants;
import org.springframework.transaction.annotation.Transactional;

/**
 * Default implementation of the Observation Service
 * 
 * @see org.openmrs.api.ObsService
 */
@Transactional
public class ObsServiceImpl extends BaseOpenmrsService implements ObsService {
	
	/**
	 * The data access object for the obs service
	 */
	protected ObsDAO dao;
	
	/**
	 * Report handlers that have been registered. This is filled via {@link #setHandlers(Map)} and
	 * spring's applicationContext-service.xml object
	 */
	private static Map<String, ComplexObsHandler> handlers = null;
	
	/**
	 * Default empty constructor for this obs service
	 */
	public ObsServiceImpl() {
	}
	
	/**
	 * @see org.openmrs.api.ObsService#setObsDAO(org.openmrs.api.db.ObsDAO)
	 */
	public void setObsDAO(ObsDAO dao) {
		this.dao = dao;
	}
	
	/**
	 * Clean up after this class. Set the static var to null so that the classloader can reclaim the
	 * space.
	 * 
	 * @see org.openmrs.api.impl.BaseOpenmrsService#onShutdown()
	 */
	@Override
	public void onShutdown() {
		setHandlers(null);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#saveObs(org.openmrs.Obs, String)
	 */
	public Obs saveObs(Obs obs, String changeMessage) throws APIException {
		if (null != obs && null != obs.getConcept() && obs.getConcept().isComplex()
		        && null != obs.getComplexData().getData()) {
			// save or update complexData object on this obs
			// this is done before the database save so that the obs.valueComplex
			// can be filled in by the handler.
			ComplexObsHandler handler = getHandler(obs);
			if (null != handler) {
				handler.saveObs(obs);
			} else {
				throw new APIException("unknown.handler", new Object[] { obs.getConcept() });
			}
		}
		
		//Should allow updating a voided Obs, it seems to be pointless to restrict it,
		//otherwise operations like merge patients won't be possible when to moving voided obs
		if (obs != null && (obs.getObsId() == null || obs.getVoided())) {
			if (obs.getObsId() == null) {
				Context.requirePrivilege(PrivilegeConstants.ADD_OBS);
			} else {
				Context.requirePrivilege(PrivilegeConstants.EDIT_OBS);
			}
			Obs ret = dao.saveObs(obs);
			if (obs.isObsGrouping()) {
				for (Obs o : obs.getGroupMembers(true)) {
					Context.getObsService().saveObs(o, changeMessage);
				}
			}
			return ret;
		} else {
			Context.requirePrivilege(PrivilegeConstants.EDIT_OBS);
			
			if (changeMessage == null) {
				throw new APIException("Obs.error.ChangeMessage.required", (Object[]) null);
			}
			
			Encounter encounter = obs.getEncounter();
			if (encounter != null) {
				obs.setPerson(encounter.getPatient());
			}
			
			if (!obs.isDirty()) {
				if (obs.isObsGrouping()) {
					ObsService os = Context.getObsService();
					List<Obs> toRemove = new ArrayList<>();
					List<Obs> toAdd = new ArrayList<>();
					for (Obs o : obs.getGroupMembers(true)) {
						if (o.getId() == null) {
							os.saveObs(o, null);
						} else {
							Obs replacement = os.saveObs(o, changeMessage);
							//The logic in saveObs evicts the old obs instance, so we need to update
							//the collection with the newly loaded and voided instance
							toRemove.add(o);
							toAdd.add(os.getObs(o.getId()));
							toAdd.add(replacement);
						}
					}
					
					for (Obs o : toRemove) {
						obs.removeGroupMember(o);
					}
					for (Obs o : toAdd) {
						obs.addGroupMember(o);
					}
				}
				return obs;
			}
			
			// get a copy of the passed in obs and save it to the
			// database. This allows us to create a new row and new obs_id
			// this method doesn't copy the obs_id
			Obs newObs = Obs.newInstance(obs);
			
			// unset any voided properties on the new obs
			newObs.setVoided(false);
			newObs.setVoidReason(null);
			newObs.setDateVoided(null);
			newObs.setVoidedBy(null);
			// unset the creation stats
			newObs.setCreator(null);
			newObs.setDateCreated(null);
			newObs.setPreviousVersion(obs);
			
			RequiredDataAdvice.recursivelyHandle(SaveHandler.class, newObs, changeMessage);
			
			// save the new row to the database with the changes that
			// have been made to it
			dao.saveObs(newObs);
			
			if (newObs.isObsGrouping()) {
				for (Obs o : newObs.getGroupMembers(true)) {
					Context.getObsService().saveObs(o, null);
				}
			}
			
			// void out the original observation to keep it around for
			// historical purposes
			try {
				Context.addProxyPrivilege(PrivilegeConstants.DELETE_OBS);
				
				// fetch a clean copy of this obs from the database so that
				// we don't write the changes to the database when we save
				// the fact that the obs is now voided
				Context.evictFromSession(obs);
				obs = Context.getObsService().getObs(obs.getObsId());
				//delete the previous file from the appdata/complex_obs folder
				if (newObs.hasPreviousVersion() && newObs.getPreviousVersion().isComplex()) {
					File previousFile = new AbstractHandler().getComplexDataFile(obs);
					previousFile.delete();
				}
				// calling this via the service so that AOP hooks are called
				Context.getObsService().voidObs(obs, changeMessage);
				
			}
			finally {
				Context.removeProxyPrivilege(PrivilegeConstants.DELETE_OBS);
			}
			
			return newObs;
		}
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getObs(java.lang.Integer)
	 */
	@Transactional(readOnly = true)
	public Obs getObs(Integer obsId) throws APIException {
		return dao.getObs(obsId);
	}
	
	/**
	 * Voids an Obs If the Obs argument is an obsGroup, all group members will be voided.
	 * 
	 * @see org.openmrs.api.ObsService#voidObs(org.openmrs.Obs, java.lang.String)
	 * @param obs the Obs to void
	 * @param reason the void reason
	 * @throws APIException
	 */
	public Obs voidObs(Obs obs, String reason) throws APIException {
		return dao.saveObs(obs);
	}
	
	/**
	 * Unvoids an Obs
	 * <p>
	 * If the Obs argument is an obsGroup, all group members with the same dateVoided will also be
	 * unvoided.
	 * 
	 * @see org.openmrs.api.ObsService#unvoidObs(org.openmrs.Obs)
	 * @param obs the Obs to unvoid
	 * @return the unvoided Obs
	 * @throws APIException
	 */
	public Obs unvoidObs(Obs obs) throws APIException {
		return dao.saveObs(obs);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#purgeObs(org.openmrs.Obs, boolean)
	 */
	public void purgeObs(Obs obs, boolean cascade) throws APIException {
		if (!purgeComplexData(obs)) {
			throw new APIException("Obs.error.unable.purge.complex.data", new Object[] { obs });
		}
		
		if (cascade) {
			throw new APIException("Obs.error.cascading.purge.not.implemented", (Object[]) null);
			// TODO delete any related objects here before deleting the obs
			// obsGroups objects?
			// orders?
		}
		
		dao.deleteObs(obs);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#purgeObs(org.openmrs.Obs)
	 */
	public void purgeObs(Obs obs) throws APIException {
		Context.getObsService().purgeObs(obs, false);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getObservations(java.util.List, java.util.List,
	 *      java.util.List, java.util.List, List, List, java.util.List, java.lang.Integer,
	 *      java.lang.Integer, java.util.Date, java.util.Date, boolean)
	 */
	@Transactional(readOnly = true)
	public List<Obs> getObservations(List<Person> whom, List<Encounter> encounters, List<Concept> questions,
	                                 List<Concept> answers, List<PERSON_TYPE> personTypes, List<Location> locations,
	                                 List<String> sort, Integer mostRecentN, Integer obsGroupId, Date fromDate, Date toDate,
	                                 boolean includeVoidedObs) throws APIException {
		
		if (sort == null) {
			sort = new Vector<String>();
		}
		if (sort.isEmpty()) {
			sort.add("obsDatetime");
		}
		
		return dao.getObservations(whom, encounters, questions, answers, personTypes, locations, sort, mostRecentN,
		    obsGroupId, fromDate, toDate, includeVoidedObs, null);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getObservations(java.util.List, java.util.List,
	 *      java.util.List, java.util.List, List, List, java.util.List, java.lang.Integer,
	 *      java.lang.Integer, java.util.Date, java.util.Date, boolean, java.lang.String)
	 */
	@Transactional(readOnly = true)
	public List<Obs> getObservations(List<Person> whom, List<Encounter> encounters, List<Concept> questions,
	                                 List<Concept> answers, List<PERSON_TYPE> personTypes, List<Location> locations,
	                                 List<String> sort, Integer mostRecentN, Integer obsGroupId, Date fromDate, Date toDate,
	                                 boolean includeVoidedObs, String accessionNumber) throws APIException {
		
		if (sort == null) {
			sort = new Vector<String>();
		}
		if (sort.isEmpty()) {
			sort.add("obsDatetime");
		}
		
		return dao.getObservations(whom, encounters, questions, answers, personTypes, locations, sort, mostRecentN,
		    obsGroupId, fromDate, toDate, includeVoidedObs, accessionNumber);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getObservationCount(java.util.List, java.util.List,
	 *      java.util.List, java.util.List, java.util.List, java.util.List, java.lang.Integer,
	 *      java.util.Date, java.util.Date, boolean)
	 */
	@Transactional(readOnly = true)
	public Integer getObservationCount(List<Person> whom, List<Encounter> encounters, List<Concept> questions,
	                                   List<Concept> answers, List<PERSON_TYPE> personTypes, List<Location> locations,
	                                   Integer obsGroupId, Date fromDate, Date toDate, boolean includeVoidedObs)
	    throws APIException {
		return OpenmrsUtil.convertToInteger(dao.getObservationCount(whom, encounters, questions, answers, personTypes,
		    locations, obsGroupId, fromDate, toDate, null, includeVoidedObs, null));
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getObservationCount(java.util.List, java.util.List,
	 *      java.util.List, java.util.List, java.util.List, java.util.List, java.lang.Integer,
	 *      java.util.Date, java.util.Date, boolean, java.lang.String)
	 */
	@Transactional(readOnly = true)
	public Integer getObservationCount(List<Person> whom, List<Encounter> encounters, List<Concept> questions,
	                                   List<Concept> answers, List<PERSON_TYPE> personTypes, List<Location> locations,
	                                   Integer obsGroupId, Date fromDate, Date toDate, boolean includeVoidedObs,
	                                   String accessionNumber) throws APIException {
		return OpenmrsUtil.convertToInteger(dao.getObservationCount(whom, encounters, questions, answers, personTypes,
		    locations, obsGroupId, fromDate, toDate, null, includeVoidedObs, accessionNumber));
	}
	
	/**
	 * This implementation queries the obs table comparing the given <code>searchString</code> with
	 * the patient's identifier, encounterId, and obsId
	 * 
	 * @see org.openmrs.api.ObsService#getObservations(java.lang.String)
	 */
	@Transactional(readOnly = true)
	public List<Obs> getObservations(String searchString) {
		
		// search on patient identifier
		PatientService ps = Context.getPatientService();
		List<Patient> patients = ps.getPatients(searchString);
		List<Person> persons = new Vector<Person>();
		persons.addAll(patients);
		
		// try to search on encounterId
		EncounterService es = Context.getEncounterService();
		List<Encounter> encounters = new Vector<Encounter>();
		try {
			Encounter e = es.getEncounter(Integer.valueOf(searchString));
			if (e != null) {
				encounters.add(e);
			}
		}
		catch (NumberFormatException e) {
			// pass
		}
		
		List<Obs> returnList = new Vector<Obs>();
		
		if (encounters.size() > 0 || persons.size() > 0) {
			returnList = Context.getObsService().getObservations(persons, encounters, null, null, null, null, null, null,
			    null, null, null, false);
		}
		
		// try to search on obsId
		try {
			Obs o = getObs(Integer.valueOf(searchString));
			if (o != null) {
				returnList.add(o);
			}
		}
		catch (NumberFormatException e) {
			// pass
		}
		
		return returnList;
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getObservationsByPerson(org.openmrs.Person)
	 */
	@Transactional(readOnly = true)
	public List<Obs> getObservationsByPerson(Person who) {
		List<Person> whom = new Vector<Person>();
		whom.add(who);
		return Context.getObsService().getObservations(whom, null, null, null, null, null, null, null, null, null, null,
		    false);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getObservationsByPersonAndConcept(org.openmrs.Person,
	 *      org.openmrs.Concept)
	 */
	@Transactional(readOnly = true)
	public List<Obs> getObservationsByPersonAndConcept(Person who, Concept question) throws APIException {
		List<Person> whom = new Vector<Person>();
		if (who != null && who.getPersonId() != null) {
			whom.add(who);
		}
		List<Concept> questions = new Vector<Concept>();
		questions.add(question);
		
		return Context.getObsService().getObservations(whom, null, questions, null, null, null, null, null, null, null,
		    null, false);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getObsByUuid(java.lang.String)
	 */
	@Transactional(readOnly = true)
	public Obs getObsByUuid(String uuid) throws APIException {
		return dao.getObsByUuid(uuid);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getComplexObs(Integer, String)
	 */
	@Transactional(readOnly = true)
	public Obs getComplexObs(Integer obsId, String view) throws APIException {
		Obs obs = dao.getObs(obsId);
		
		if (obs != null && obs.isComplex()) {
			return getHandler(obs).getObs(obs, view);
		}
		
		return obs;
	}
	
	/**
	 * Internal method to remove ComplexData when an Obs is purged.
	 */
	protected boolean purgeComplexData(Obs obs) throws APIException {
		if (obs.isComplex()) {
			ComplexObsHandler handler = getHandler(obs);
			if (null != handler) {
				return handler.purgeComplexData(obs);
			}
		}
		
		return true;
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getHandler(org.openmrs.Obs)
	 */
	@Transactional(readOnly = true)
	public ComplexObsHandler getHandler(Obs obs) throws APIException {
		if (obs.getConcept().isComplex()) {
			// Get the ConceptComplex from the ConceptService then return its
			// handler.
			if (obs.getConcept() == null) {
				throw new APIException("Obs.error.unable.get.handler", new Object[] { obs });
			}
			
			String handlerString = Context.getConceptService().getConceptComplex(obs.getConcept().getConceptId())
			        .getHandler();
			
			if (handlerString == null) {
				throw new APIException("Obs.error.unable.get.handler.and.concept", new Object[] { obs, obs.getConcept() });
			}
			
			return this.getHandler(handlerString);
		}
		
		return null;
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getHandler(java.lang.String)
	 */
	public ComplexObsHandler getHandler(String key) {
		return handlers.get(key);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#setHandlers(Map)
	 * @see #registerHandler(String, ComplexObsHandler)
	 */
	public void setHandlers(Map<String, ComplexObsHandler> newHandlers) throws APIException {
		if (newHandlers == null) {
			ObsServiceImpl.setStaticHandlers(null);
			return;
		}
		for (Map.Entry<String, ComplexObsHandler> entry : newHandlers.entrySet()) {
			registerHandler(entry.getKey(), entry.getValue());
		}
	}
	
	/**
	 * Sets handlers using static method
	 *
	 * @param currentHandlers
	 */
	private static void setStaticHandlers(Map<String, ComplexObsHandler> currentHandlers) {
		ObsServiceImpl.handlers = currentHandlers;
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getHandlers()
	 */
	@Transactional(readOnly = true)
	public Map<String, ComplexObsHandler> getHandlers() throws APIException {
		if (handlers == null) {
			handlers = new LinkedHashMap<String, ComplexObsHandler>();
		}
		
		return handlers;
	}
	
	/**
	 * @see org.openmrs.api.ObsService#registerHandler(String, ComplexObsHandler)
	 */
	public void registerHandler(String key, ComplexObsHandler handler) throws APIException {
		getHandlers().put(key, handler);
	}
	
	/**
	 * @see org.openmrs.api.ObsService#registerHandler(String, String)
	 */
	public void registerHandler(String key, String handlerClass) throws APIException {
		try {
			Class<?> loadedClass = OpenmrsClassLoader.getInstance().loadClass(handlerClass);
			registerHandler(key, (ComplexObsHandler) loadedClass.newInstance());
			
		}
		catch (Exception e) {
			throw new APIException("unable.load.and.instantiate.handler", null, e);
		}
	}
	
	/**
	 * @see org.openmrs.api.ObsService#getObservationCount(java.util.List, boolean)
	 */
	@Override
	@Transactional(readOnly = true)
	public Integer getObservationCount(List<ConceptName> conceptNames, boolean includeVoided) {
		return OpenmrsUtil.convertToInteger(dao.getObservationCount(null, null, null, null, null, null, null, null, null,
		    conceptNames, true, null));
	}
	
	/**
	 * @see org.openmrs.api.ObsService#removeHandler(java.lang.String)
	 */
	public void removeHandler(String key) {
		handlers.remove(key);
	}
	
}
