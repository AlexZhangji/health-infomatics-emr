/*
	Copyright (c) 2006, The OpenMRS Cooperative
	All Rights Reserved.
*/

dojo.provide("dojo.widget.openmrs.OpenmrsPopup");

dojo.require("dojo.widget.*");

dojo.widget.tags.addParseTreeHandler("dojo:OpenmrsPopup");

dojo.widget.defineWidget(
	"dojo.widget.openmrs.OpenmrsPopup",
	dojo.widget.HtmlWidget,
	{
		isContainer: true,

		displayNode: null,
		descriptionDisplayNode: null,

		hiddenInputNode: null,
		hiddenInputName: "",
		hiddenInputId: "",
		
		otherNode: null,
		otherInputNode: null,
		otherInputName: "",
		otherInputId: "",
		otherValue: "",
		showOther: "",
		
		showChangeButton: true,
		changeButton: null,
		changeButtonValue: "",

		searchWidget: "",
		searchTitle: "",
		
		allowSearch: true,
		setPositionTop: true,
		
		form: null,
		
		eventNames: {},
		eventNamesDefault: {
			select : "select"
		},
	
		initializer: function(){
			dojo.debug("initializing OpenmrsPopup");
			
			for(name in this.eventNamesDefault) {
				if (dojo.lang.isUndefined(this.eventNames[name])) {
					this.eventNames[name] = this.widgetId+"/"+this.eventNamesDefault[name];
				}
			}
		},
		
		fillInTemplate: function(args, frag){
			dojo.event.connect(this.changeButton, "onmouseup", this, "onChangeButtonClick");
			dojo.event.connect(this.changeButton, "onkeyup", this, "onChangeButtonKeyup");

			if (!this.allowSearch)
				this.changeButton.style.display = "none";
		},
		
		templateString: '<span id="$' + '{this.widgetId}"><span style="white-space: nowrap"><span dojoAttachPoint="displayNode"></span> <input type="hidden" value="" dojoAttachPoint="hiddenInputNode" /><input type="hidden" value="" dojoAttachPoint="hiddenCodedDatatype" id="hiddenCodedDatatype"/><input type="button" value="' + omsgs.select + '" dojoAttachPoint="changeButton" class="smallButton" /> </span><span dojoAttachPoint="otherNode"><input type="text" value="" dojoAttachPoint="otherInputNode" /></span><div class="description" dojoAttachPoint="descriptionDisplayNode"></div> </span>',
		templateCssPath: "",
		
		postCreate: function(createdObject) {
			var widg = dojo.widget.manager.getWidgetById(this.searchWidget);
			
			if (widg) {
				dojo.debug("Adding searchWidget: " + this.searchWidget);
				this.searchWidget = widg;
				this.addChild(this.searchWidget);
				this.searchWidget.domNode.className = "popupSearchForm";
				this.searchWidget.toggleShowing();
				
				if (!this.searchWidget.tableHeight)
					this.searchWidget.tableHeight = 332;
				
				if (this.searchTitle) {
					var title = document.createElement("h4");
					title.innerHTML = this.searchTitle;
					this.searchWidget.domNode.insertBefore(title, this.searchWidget.domNode.firstChild);
					this.searchWidget.tableHeight = 310;
				}
				
				var closeButton = document.createElement("input");
				closeButton.type = "button";
				closeButton.className="closeButton";
				closeButton.value="X";
				this.searchWidget.domNode.insertBefore(closeButton, this.searchWidget.domNode.firstChild);
				dojo.event.connect(closeButton, "onmouseup", this, "closeSearch");
				
				dojo.event.connect(this.searchWidget, "select", this, "doSelect"); 
				dojo.event.connect(this.searchWidget, "doObjectsFound", this, "doObjectsFound"); 
				dojo.event.connect(this.searchWidget, "select", this, "setChangeButtonValue"); 
				
				this.searchWidget.inputNode.style.width="190px";
				
			}
			else {
				/* If the widget is not found, it might just not be loaded yet (like maybe
					 theres a user and patient search widget on the page(, try a few times */
				var attempts = createdObject["attemptNum"];
				if (attempts == null)
					attempts = 0;
				/* try again in 100 milliseconds 5 times*/
				if (attempts < 5) {
					createdObject["attemptNum"] = attempts++;
					var callback = function(ths, attempts) { return function() {ths.postCreate(createdObject)}};
					setTimeout(callback(this, attempts), 100);
					return; /* stop processing until we find the widget */
				}
				else {
					alert("searchWidget not found: '" + this.searchWidget + "'");
				}
				
			}
			
			if (this.hiddenInputName)
				this.hiddenInputNode.name = this.hiddenInputName;	
			
			if (this.hiddenInputId)
				this.hiddenInputNode.id = this.hiddenInputId;	
			
			if (this.hiddenInputNode.name) {
				this.otherInputNode.name = this.hiddenInputNode.name + "_other";
			}
			
			if (this.hiddenInputNode.id) {
				this.otherInputNode.id = this.hiddenInputNode.id + "_other";
			}
	
			if ( this.otherInputNode ) {
				this.otherInputNode.value = this.otherValue;
			}

			if (this.changeButtonValue != '') {
				this.changeButton.value = this.changeButtonValue;
			}
			
			if (!this.showChangeButton)
				this.changeButton.style.display = "none";
			
			this.otherNode.style.display = "none";
			
		},
		
		onChangeButtonClick: function() {
			dojo.debug("Change button clicked");
			
			this.searchWidget.clearSearch();
				
			this.searchWidget.toggleShowing();
			
			var left = dojo.style.totalOffsetLeft(this.changeButton, false) + dojo.style.getBorderBoxWidth(this.changeButton) + 10;
			if (left + dojo.style.getBorderBoxWidth(this.searchWidget.domNode) > dojo.html.getViewportWidth())
				left = dojo.html.getViewportWidth() - dojo.style.getBorderBoxWidth(this.searchWidget.domNode) - 10 + dojo.html.getScrollLeft();
			
			if (this.setPositionTop) {
				var top = dojo.style.totalOffsetTop(this.changeButton, true);
				var top2 = dojo.style.totalOffsetTop(this.changeButton, true);
				var scrollTop = dojo.html.getScrollTop();
				var boxHeight = dojo.style.getBorderBoxHeight(this.searchWidget.domNode);
				var viewportHeight = dojo.html.getViewportHeight();
				if ((top + boxHeight - scrollTop) > viewportHeight - 5)
					top = viewportHeight - boxHeight + scrollTop - 10;
			
				dojo.style.setPositivePixelValue(this.searchWidget.domNode, "top", top);
			}
			
			dojo.style.setPositivePixelValue(this.searchWidget.domNode, "left", left);
			
			this.searchWidget.inputNode.select();
			
		},
		
		onChangeButtonKeyup: function(evt) {
			if (evt.keyCode == dojo.event.browser.keys.KEY_SPACE || 
				evt.keyCode == dojo.event.browser.keys.KEY_ENTER)
					this.onChangeButtonClick();
		},
		
		showIfHiding: false,
		doObjectsFound: function(objs) {
			if (!this.searchWidget.isShowing() && this.showIfHiding)
				this.searchWidget.toggleShowing();
		},
		
		doSelect: function(objs, skipFocus) {
			this.closeSearch(skipFocus);
			dojo.event.topic.publish(this.eventNames.select, {"objs":objs});
		},
		
		closeSearch: function(skipFocus) {
			this.searchWidget.hide();
			if (!(skipFocus))
				this.changeButton.focus();
		},
		
		setChangeButtonValue: function(objs) {
			if ( this.changeButtonValue == '' ) {
				//if ( this.hiddenInputNode.value == "" ) this.changeButton.value = omsgs.select;
				//else this.changeButton.value=omsgs.change;
				this.changeButton.value=omsgs.change;
			}
		},
		
		showOtherInputNode: function(cId, showOther) {
			if ( cId == showOther ) {
				this.otherNode.style.display = "";
			} else {
				this.otherNode.style.display = "none";
			}
		}
	},
	"html"
);