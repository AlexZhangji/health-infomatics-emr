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

import java.util.ArrayList;
import java.util.Collection;
import java.util.Date;
import java.util.List;
import java.util.Set;

import org.apache.commons.logging.Log;
import org.apache.commons.logging.LogFactory;
import org.openmrs.Cohort;
import org.openmrs.Concept;
import org.openmrs.ConceptAnswer;
import org.openmrs.ConceptStateConversion;
import org.openmrs.Patient;
import org.openmrs.PatientProgram;
import org.openmrs.PatientState;
import org.openmrs.Program;
import org.openmrs.ProgramWorkflow;
import org.openmrs.ProgramWorkflowState;
import org.openmrs.api.APIException;
import org.openmrs.api.ProgramNameDuplicatedException;
import org.openmrs.api.ProgramWorkflowService;
import org.openmrs.api.context.Context;
import org.openmrs.api.db.ProgramWorkflowDAO;
import org.springframework.transaction.annotation.Transactional;

/**
 * Default implementation of the ProgramWorkflow-related services class. This method should not be
 * invoked by itself. Spring injection is used to inject this implementation into the
 * ServiceContext. Which implementation is injected is determined by the spring application context
 * file: /metadata/api/spring/applicationContext.xml
 *
 * @see org.openmrs.api.ProgramWorkflowService
 */
@Transactional
public class ProgramWorkflowServiceImpl extends BaseOpenmrsService implements ProgramWorkflowService {
	
	protected final Log log = LogFactory.getLog(this.getClass());
	
	protected ProgramWorkflowDAO dao;
	
	public ProgramWorkflowServiceImpl() {
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#setProgramWorkflowDAO(org.openmrs.api.db.ProgramWorkflowDAO)
	 */
	public void setProgramWorkflowDAO(ProgramWorkflowDAO dao) {
		this.dao = dao;
	}
	
	// **************************
	// PROGRAM
	// **************************
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#saveProgram(org.openmrs.Program)
	 */
	public Program saveProgram(Program program) throws APIException {
		// Program
		if (program.getConcept() == null) {
			throw new APIException("Program.concept.required", (Object[]) null);
		}
		
		// ProgramWorkflow
		for (ProgramWorkflow workflow : program.getAllWorkflows()) {
			
			if (workflow.getConcept() == null) {
				throw new APIException("ProgramWorkflow.concept.required", (Object[]) null);
			}
			if (workflow.getProgram() == null) {
				workflow.setProgram(program);
			} else if (!workflow.getProgram().equals(program)) {
				throw new APIException("Program.error.contains.ProgramWorkflow", new Object[] { workflow.getProgram() });
			}
			
			// ProgramWorkflowState
			for (ProgramWorkflowState state : workflow.getStates()) {
				
				if (state.getConcept() == null || state.getInitial() == null || state.getTerminal() == null) {
					throw new APIException("ProgramWorkflowState.requires", (Object[]) null);
				}
				if (state.getProgramWorkflow() == null) {
					state.setProgramWorkflow(workflow);
				} else if (!state.getProgramWorkflow().equals(workflow)) {
					throw new APIException("ProgramWorkflow.error.contains.state", new Object[] { workflow.getProgram() });
				}
			}
		}
		return dao.saveProgram(program);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getProgram(java.lang.Integer)
	 */
	@Transactional(readOnly = true)
	public Program getProgram(Integer id) {
		return dao.getProgram(id);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getProgram(java.lang.String)
	 */
	@Transactional(readOnly = true)
	public Program getProgram(String name) {
		return Context.getProgramWorkflowService().getProgramByName(name);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getProgram(java.lang.String)
	 */
	@Transactional(readOnly = true)
	public Program getProgramByName(String name) throws APIException {
		List<Program> programs = dao.getProgramsByName(name, false);
		
		if (programs.isEmpty()) {
			programs = dao.getProgramsByName(name, true);
		}
		
		//Must be unique not retired or unique retired
		if (programs.size() > 1) {
			throw new ProgramNameDuplicatedException(name);
		}
		return programs.isEmpty() ? null : programs.get(0);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getAllPrograms()
	 */
	@Transactional(readOnly = true)
	public List<Program> getAllPrograms() throws APIException {
		return Context.getProgramWorkflowService().getAllPrograms(true);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getAllPrograms(boolean)
	 */
	@Transactional(readOnly = true)
	public List<Program> getAllPrograms(boolean includeRetired) throws APIException {
		return dao.getAllPrograms(includeRetired);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getPrograms(String)
	 */
	@Transactional(readOnly = true)
	public List<Program> getPrograms(String nameFragment) throws APIException {
		return dao.findPrograms(nameFragment);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#purgeProgram(org.openmrs.Program)
	 */
	public void purgeProgram(Program program) throws APIException {
		Context.getProgramWorkflowService().purgeProgram(program, false);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#purgeProgram(org.openmrs.Program, boolean)
	 */
	public void purgeProgram(Program program, boolean cascade) throws APIException {
		if (cascade && !program.getAllWorkflows().isEmpty()) {
			throw new APIException("Program.cascade.purging.not.implemented", (Object[]) null);
		}
		for (PatientProgram patientProgram : Context.getProgramWorkflowService().getPatientPrograms(null, program, null,
		    null, null, null, true)) {
			purgePatientProgram(patientProgram);
		}
		dao.deleteProgram(program);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#retireProgram(org.openmrs.Program)
	 */
	public Program retireProgram(Program program, String reason) throws APIException {
		//program.setRetired(true); - Note the BaseRetireHandler aspect is already setting the retired flag and reason
		for (ProgramWorkflow workflow : program.getWorkflows()) {
			workflow.setRetired(true);
			for (ProgramWorkflowState state : workflow.getStates()) {
				state.setRetired(true);
			}
		}
		return saveProgram(program);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#retireProgram(org.openmrs.Program)
	 */
	public Program unretireProgram(Program program) throws APIException {
		Date lastModifiedDate = program.getDateChanged();
		program.setRetired(false);
		for (ProgramWorkflow workflow : program.getAllWorkflows()) {
			if (lastModifiedDate != null && lastModifiedDate.equals(workflow.getDateChanged())) {
				workflow.setRetired(false);
				for (ProgramWorkflowState state : workflow.getStates()) {
					if (lastModifiedDate.equals(state.getDateChanged())) {
						state.setRetired(false);
					}
				}
			}
		}
		return saveProgram(program);
	}
	
	// **************************
	// PATIENT PROGRAM 
	// **************************
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#savePatientProgram(org.openmrs.PatientProgram)
	 */
	public PatientProgram savePatientProgram(PatientProgram patientProgram) throws APIException {
		
		if (patientProgram.getPatient() == null || patientProgram.getProgram() == null) {
			throw new APIException("PatientProgram.requires", (Object[]) null);
		}
		
		// Patient State
		for (PatientState state : patientProgram.getStates()) {
			if (state.getState() == null) {
				throw new APIException("PatientState.requires", (Object[]) null);
			}
			if (state.getPatientProgram() == null) {
				state.setPatientProgram(patientProgram);
			} else if (!state.getPatientProgram().equals(patientProgram)) {
				throw new APIException("PatientProgram.already.assigned", new Object[] { state.getPatientProgram() });
			}
			if (patientProgram.getVoided() || state.getVoided()) {
				state.setVoided(true);
				if (state.getVoidReason() == null && patientProgram.getVoidReason() != null) {
					state.setVoidReason(patientProgram.getVoidReason());
				}
			}
		}
		
		return dao.savePatientProgram(patientProgram);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getPatientProgram(java.lang.Integer)
	 */
	@Transactional(readOnly = true)
	public PatientProgram getPatientProgram(Integer patientProgramId) {
		return dao.getPatientProgram(patientProgramId);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getPatientPrograms(Patient, Program, Date, Date,
	 *      Date, Date, boolean)
	 */
	@Transactional(readOnly = true)
	public List<PatientProgram> getPatientPrograms(Patient patient, Program program, Date minEnrollmentDate,
	        Date maxEnrollmentDate, Date minCompletionDate, Date maxCompletionDate, boolean includeVoided)
	        throws APIException {
		return dao.getPatientPrograms(patient, program, minEnrollmentDate, maxEnrollmentDate, minCompletionDate,
		    maxCompletionDate, includeVoided);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getPatientPrograms(Cohort, Collection)
	 */
	@Transactional(readOnly = true)
	public List<PatientProgram> getPatientPrograms(Cohort cohort, Collection<Program> programs) {
		if (cohort.getMemberIds().size() < 1) {
			return dao.getPatientPrograms(null, programs);
		} else {
			return dao.getPatientPrograms(cohort, programs);
		}
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#purgePatientProgram(org.openmrs.PatientProgram)
	 */
	public void purgePatientProgram(PatientProgram patientProgram) throws APIException {
		Context.getProgramWorkflowService().purgePatientProgram(patientProgram, false);
		
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#purgePatientProgram(org.openmrs.PatientProgram,
	 *      boolean)
	 */
	public void purgePatientProgram(PatientProgram patientProgram, boolean cascade) throws APIException {
		if (cascade && !patientProgram.getStates().isEmpty()) {
			throw new APIException("PatientProgram.cascade.purging.not.implemented", (Object[]) null);
		}
		dao.deletePatientProgram(patientProgram);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#voidPatientProgram(org.openmrs.PatientProgram,
	 *      java.lang.String)
	 */
	public PatientProgram voidPatientProgram(PatientProgram patientProgram, String reason) {
		patientProgram.setVoided(true);
		patientProgram.setVoidReason(reason);
		return Context.getProgramWorkflowService().savePatientProgram(patientProgram); // The savePatientProgram method handles all of the voiding defaults and cascades
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#voidPatientProgram(org.openmrs.PatientProgram,
	 *      java.lang.String)
	 */
	public PatientProgram unvoidPatientProgram(PatientProgram patientProgram) {
		Date voidDate = patientProgram.getDateVoided();
		patientProgram.setVoided(false);
		for (PatientState state : patientProgram.getStates()) {
			if (voidDate != null && voidDate.equals(state.getDateVoided())) {
				state.setVoided(false);
				state.setVoidedBy(null);
				state.setDateVoided(null);
				state.setVoidReason(null);
			}
		}
		return Context.getProgramWorkflowService().savePatientProgram(patientProgram); // The savePatientProgram method handles all of the unvoiding defaults
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getPossibleOutcomes(Integer)
	 */
	@Override
	@Transactional(readOnly = true)
	public List<Concept> getPossibleOutcomes(Integer programId) {
		List<Concept> possibleOutcomes = new ArrayList<Concept>();
		Program program = Context.getProgramWorkflowService().getProgram(programId);
		if (program == null) {
			return possibleOutcomes;
		}
		Concept outcomesConcept = program.getOutcomesConcept();
		if (outcomesConcept == null) {
			return possibleOutcomes;
		}
		if (!outcomesConcept.getAnswers().isEmpty()) {
			for (ConceptAnswer conceptAnswer : outcomesConcept.getAnswers()) {
				possibleOutcomes.add(conceptAnswer.getAnswerConcept());
			}
			return possibleOutcomes;
		}
		if (!outcomesConcept.getSetMembers().isEmpty()) {
			return outcomesConcept.getSetMembers();
		}
		return possibleOutcomes;
	}
	
	// **************************
	// CONCEPT STATE CONVERSION 
	// **************************
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#saveConceptStateConversion(org.openmrs.ConceptStateConversion)
	 */
	public ConceptStateConversion saveConceptStateConversion(ConceptStateConversion csc) throws APIException {
		if (csc.getConcept() == null || csc.getProgramWorkflow() == null || csc.getProgramWorkflowState() == null) {
			throw new APIException("ConceptStateConversion.requires", (Object[]) null);
		}
		return dao.saveConceptStateConversion(csc);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getConceptStateConversion(java.lang.Integer)
	 */
	@Transactional(readOnly = true)
	public ConceptStateConversion getConceptStateConversion(Integer id) {
		return dao.getConceptStateConversion(id);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getAllConceptStateConversions()
	 */
	@Transactional(readOnly = true)
	public List<ConceptStateConversion> getAllConceptStateConversions() throws APIException {
		return dao.getAllConceptStateConversions();
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#purgeConceptStateConversion(org.openmrs.ConceptStateConversion)
	 */
	public void purgeConceptStateConversion(ConceptStateConversion conceptStateConversion) throws APIException {
		Context.getProgramWorkflowService().purgeConceptStateConversion(conceptStateConversion, false);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#purgeConceptStateConversion(org.openmrs.ConceptStateConversion,
	 *      boolean)
	 */
	public void purgeConceptStateConversion(ConceptStateConversion conceptStateConversion, boolean cascade)
	        throws APIException {
		dao.deleteConceptStateConversion(conceptStateConversion);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#triggerStateConversion(org.openmrs.Patient,
	 *      org.openmrs.Concept, java.util.Date)
	 */
	public void triggerStateConversion(Patient patient, Concept trigger, Date dateConverted) {
		
		// Check input parameters
		if (patient == null) {
			throw new APIException("convert.state.invalid.patient", (Object[]) null);
		}
		if (trigger == null) {
			throw new APIException("convert.state.patient.without.valid.trigger", (Object[]) null);
		}
		if (dateConverted == null) {
			throw new APIException("convert.state.invalid.date", (Object[]) null);
		}
		
		for (PatientProgram patientProgram : getPatientPrograms(patient, null, null, null, null, null, false)) {
			//skip past patient programs that already completed
			if (patientProgram.getDateCompleted() == null) {
				Set<ProgramWorkflow> workflows = patientProgram.getProgram().getWorkflows();
				for (ProgramWorkflow workflow : workflows) {
					// (getWorkflows() is only returning over nonretired workflows)
					PatientState patientState = patientProgram.getCurrentState(workflow);
					
					// #1080 cannot exit patient from care  
					// Should allow a transition from a null state to a terminal state
					// Or we should require a user to ALWAYS add an initial workflow/state when a patient is added to a program
					ProgramWorkflowState currentState = (patientState != null) ? patientState.getState() : null;
					ProgramWorkflowState transitionState = workflow.getState(trigger);
					
					log.debug("Transitioning from current state [" + currentState + "]");
					log.debug("|---> Transitioning to final state [" + transitionState + "]");
					
					if (transitionState != null && workflow.isLegalTransition(currentState, transitionState)) {
						patientProgram.transitionToState(transitionState, dateConverted);
						log.debug("State Conversion Triggered: patientProgram=" + patientProgram + " transition from "
						        + currentState + " to " + transitionState + " on " + dateConverted);
					}
				}
				
				// #1068 - Exiting a patient from care causes "not-null property references
				// a null or transient value: org.openmrs.PatientState.dateCreated". Explicitly
				// calling the savePatientProgram() method will populate the metadata properties.
				// 
				// #1067 - We should explicitly save the patient program rather than let 
				// Hibernate do so when it flushes the session.
				Context.getProgramWorkflowService().savePatientProgram(patientProgram);
			}
		}
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getConceptStateConversion(org.openmrs.ProgramWorkflow,
	 *      org.openmrs.Concept)
	 */
	@Transactional(readOnly = true)
	public ConceptStateConversion getConceptStateConversion(ProgramWorkflow workflow, Concept trigger) {
		return dao.getConceptStateConversion(workflow, trigger);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getProgramsByConcept(org.openmrs.Concept)
	 */
	@Override
	@Transactional(readOnly = true)
	public List<Program> getProgramsByConcept(Concept concept) {
		return dao.getProgramsByConcept(concept);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getProgramWorkflowsByConcept(org.openmrs.Concept)
	 */
	@Override
	@Transactional(readOnly = true)
	public List<ProgramWorkflow> getProgramWorkflowsByConcept(Concept concept) {
		return dao.getProgramWorkflowsByConcept(concept);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getProgramWorkflowStatesByConcept(org.openmrs.Concept)
	 */
	@Override
	@Transactional(readOnly = true)
	public List<ProgramWorkflowState> getProgramWorkflowStatesByConcept(Concept concept) {
		return dao.getProgramWorkflowStatesByConcept(concept);
	}
		
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getConceptStateConversionByUuid(java.lang.String)
	 */
	@Transactional(readOnly = true)
	public ConceptStateConversion getConceptStateConversionByUuid(String uuid) {
		return dao.getConceptStateConversionByUuid(uuid);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getPatientProgramByUuid(java.lang.String)
	 */
	@Transactional(readOnly = true)
	public PatientProgram getPatientProgramByUuid(String uuid) {
		return dao.getPatientProgramByUuid(uuid);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getProgramByUuid(java.lang.String)
	 */
	@Transactional(readOnly = true)
	public Program getProgramByUuid(String uuid) {
		return dao.getProgramByUuid(uuid);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getStateByUuid(java.lang.String)
	 */
	@Transactional(readOnly = true)
	public ProgramWorkflowState getStateByUuid(String uuid) {
		return dao.getStateByUuid(uuid);
	}
	
	@Transactional(readOnly = true)
	public PatientState getPatientStateByUuid(String uuid) {
		return dao.getPatientStateByUuid(uuid);
	}
	
	/**
	 * @see org.openmrs.api.ProgramWorkflowService#getWorkflowByUuid(java.lang.String)
	 */
	@Transactional(readOnly = true)
	public ProgramWorkflow getWorkflowByUuid(String uuid) {
		return dao.getWorkflowByUuid(uuid);
	}
	
}
