/**
 * This Source Code Form is subject to the terms of the Mozilla Public License,
 * v. 2.0. If a copy of the MPL was not distributed with this file, You can
 * obtain one at http://mozilla.org/MPL/2.0/. OpenMRS is also distributed under
 * the terms of the Healthcare Disclaimer located at http://openmrs.org/license.
 *
 * Copyright (C) OpenMRS Inc. OpenMRS is a registered trademark and the OpenMRS
 * graphic logo is a trademark of OpenMRS Inc.
 */
package org.openmrs;

import java.util.Date;

import org.codehaus.jackson.annotate.JsonIgnore;

/**
 * ConceptNameTag is a textual tag which can be applied to a ConceptName.
 */
public class ConceptNameTag extends BaseOpenmrsObject implements Auditable, Voidable, java.io.Serializable {
	
	public static final long serialVersionUID = 33226787L;
	
	// Fields
	private Integer conceptNameTagId;
	
	private String tag;
	
	private String description;
	
	private User creator;
	
	private Date dateCreated;
	
	private Boolean voided = false;
	
	private User voidedBy;
	
	private Date dateVoided;
	
	private String voidReason;
	
	// Constructors
	
	/**
	 * Default constructor.
	 */
	public ConceptNameTag() {
	}
	
	/**
	 * Public constructor. Use factory methods to obtain copies of the desired tags.
	 * 
	 * @param tag
	 * @param description
	 */
	public ConceptNameTag(String tag, String description) {
		setTag(tag);
		setDescription(description);
	}
	
	// Property accessors
	
	/**
	 * Returns the textual representation of this ConceptNameTag.
	 * 
	 * @return the textual representation of this ConceptNameTag.
	 */
	public String getTag() {
		return tag;
	}
	
	/**
	 * Sets the textual representation of this ConceptNametag.
	 * 
	 * @param tag the textual representation
	 */
	public void setTag(String tag) {
		this.tag = tag;
	}
	
	/**
	 * Returns the description of this tag.
	 * 
	 * @return the description of this tag
	 */
	public String getDescription() {
		return description;
	}
	
	/**
	 * Sets the description of this tag.
	 * 
	 * @param description
	 */
	public void setDescription(String description) {
		this.description = description;
	}
	
	/**
	 * @return Returns the creator.
	 */
	public User getCreator() {
		return creator;
	}
	
	/**
	 * @param creator The creator to set.
	 */
	public void setCreator(User creator) {
		this.creator = creator;
	}
	
	/**
	 * @return Returns the dateCreated.
	 */
	public Date getDateCreated() {
		return dateCreated;
	}
	
	/**
	 * @param dateCreated The dateCreated to set.
	 */
	public void setDateCreated(Date dateCreated) {
		this.dateCreated = dateCreated;
	}
	
	/**
	 * Returns whether the ConceptName has been voided.
	 * 
	 * @return true if the ConceptName has been voided, false otherwise.
	 * 
	 * @deprecated as of 2.0, use {@link #getVoided()}
	 */
	@Deprecated
	@JsonIgnore
	public Boolean isVoided() {
		return getVoided();
	}
	
	/**
	 * Returns whether the ConceptName has been voided.
	 * 
	 * @return true if the ConceptName has been voided, false otherwise.
	 */
	public Boolean getVoided() {
		return voided;
	}
	
	/**
	 * Sets the voided status of the ConceptName.
	 * 
	 * @param voided the voided status to set.
	 */
	public void setVoided(Boolean voided) {
		this.voided = voided;
	}
	
	/**
	 * Returns the User who voided this ConceptName.
	 * 
	 * @return the User who voided this ConceptName, or null if not set
	 */
	public User getVoidedBy() {
		return voidedBy;
	}
	
	/**
	 * Sets the User who voided this ConceptName.
	 * 
	 * @param voidedBy the user who voided this ConceptName.
	 */
	public void setVoidedBy(User voidedBy) {
		this.voidedBy = voidedBy;
	}
	
	/**
	 * Returns the Date this ConceptName was voided.
	 * 
	 * @return the Date this ConceptName was voided.
	 */
	public Date getDateVoided() {
		return dateVoided;
	}
	
	/**
	 * Sets the Data this ConceptName was voided.
	 * 
	 * @param dateVoided the date the ConceptName was voided.
	 */
	public void setDateVoided(Date dateVoided) {
		this.dateVoided = dateVoided;
	}
	
	/**
	 * Returns the reason this ConceptName was voided.
	 * 
	 * @return the reason this ConceptName was voided
	 */
	public String getVoidReason() {
		return voidReason;
	}
	
	/**
	 * Sets the reason this ConceptName was voided.
	 * 
	 * @param voidReason the reason this ConceptName was voided
	 */
	public void setVoidReason(String voidReason) {
		this.voidReason = voidReason;
	}
	
	public String toString() {
		return this.tag;
	}
	
	/**
	 * @return the conceptNameTagId
	 */
	public Integer getConceptNameTagId() {
		return conceptNameTagId;
	}
	
	/**
	 * @param conceptNameTagId the conceptNameTagId to set
	 */
	public void setConceptNameTagId(Integer conceptNameTagId) {
		this.conceptNameTagId = conceptNameTagId;
	}
	
	/**
	 * @since 1.5
	 * @see org.openmrs.OpenmrsObject#getId()
	 */
	public Integer getId() {
		return getConceptNameTagId();
	}
	
	/**
	 * @since 1.5
	 * @see org.openmrs.OpenmrsObject#setId(java.lang.Integer)
	 */
	public void setId(Integer id) {
		setConceptNameTagId(id);
	}
	
	/**
	 * Not currently used. Always returns null.
	 * 
	 * @see org.openmrs.Auditable#getChangedBy()
	 */
	public User getChangedBy() {
		return null;
	}
	
	/**
	 * Not currently used. Always returns null.
	 * 
	 * @see org.openmrs.Auditable#getDateChanged()
	 */
	public Date getDateChanged() {
		return null;
	}
	
	/**
	 * Not currently used.
	 * 
	 * @see org.openmrs.Auditable#setChangedBy(org.openmrs.User)
	 */
	public void setChangedBy(User changedBy) {
		
	}
	
	/**
	 * Not currently used.
	 * 
	 * @see org.openmrs.Auditable#setDateChanged(java.util.Date)
	 */
	public void setDateChanged(Date dateChanged) {
		
	}
}
