
/*
 * Manages all that appertains to the custom popup box
 *
 * @category   Common
 * @package    CustomPopup
 * @author     Kihara Absolomon <soloincc@movert.co.ke>
 * @version		0.1
 */

var CustomMssgBox = {

   defaultWidth: 470,
   defaultHeight: 70,

   /**
    * Creates a pop up kind of an interface.
    *
    * settings    The settings that will be used to create the message box
    *
    * Example of use
    *
    * <code>
    *  CustomMssgBox.createMessageBox({
    *       message: 'message'         //A message or the HTML code to be displayed in the pop up
    *       callBack: doNothing, 		//A function that will be called on clicking either the true or false buttons
	 *       cancelButton: true,        //Specifies whether to have a false/cancel button on the interface
	 *       vars: {some variables},    //(Optional) These are variables that should be passed to the callBack fucntion
	 *       disableDrag: false,        //(Optional) Specifies whether to disable the dragging effect of the popup
    *       customTitle:"Custom Title"	//(Optional) A custom title that we want to appear on the dialog box
    *       okText:"Custom Yes"        //(Optional) A custom yes text that will be displayed
    *       canceltext:"Custom Cancel" //(Optional) A custom cancel text that will be displayed
    *  });
    * </code>
    */
   createMessageBox: function(settings){
      //variables are the variables that should be passed to the callback function. this will be held in an array
      var msg, curSettings={};
      curSettings.drag = (settings.disableDrag === true) ? false : true;
      if(settings.customTitle !== undefined) curSettings.title = settings.customTitle;
      else curSettings.title = Main.title;
      curSettings.text = settings.message;
      curSettings.width = settings.width;
      curSettings.skin = 'default';
      curSettings.ok = {value: true, text: settings.okText || 'Yes', onclick: eval(settings.callBack)};

      if(settings.cancelButton) curSettings.cancel = {value: false, text: settings.cancelText || 'No', onclick: eval(settings.callBack)};
      if(settings.vars != undefined) curSettings.variables = settings.vars;
      msg = new CustomMssgBox.DOMAlert(curSettings);
      msg.show();
   },

   DOMAlert: function(settings){
      var that, modalWindow, iframe, alertWindow, titleBar, title, ricon, licon, contentArea, buttonArea, okButton, cancelButton, defaultCallback, okCallback, cancelCallback;

      that = this;	//create version of ourself for use in closures
      this.settings = settings;	//Create our settings
      this.html = {};		//Create a namespace object to hold our html elements
      //ie6 test.  what a crappy browser
      this.isIE6 = (document.all && window.external && (typeof document.documentElement.style.maxHeight === 'undefined')) ? true : false;

      // use the Default skin if none was provided
      this.settings.skin = this.settings.skin ? this.settings.skin : 'default';

      // Set up a default for OK setting
      if(!this.settings.ok){
         defaultCallback = function (){
            that.close();
         };
         this.settings.ok = {text: 'Ok', value: true, onclick: defaultCallback};
      }

      //Create our modal background
      //var height=((document.documentElement.clientHeight > document.documentElement.scrollHeight) ? document.documentElement.clientHeight : document.documentElement.scrollHeight);
      var height=((self.innerHeight > document.documentElement.scrollHeight) ? self.innerHeight : document.documentElement.scrollHeight);
      modalWindow = document.createElement('div');
      modalWindow.style.height = height+'px';
      modalWindow.style.width = document.documentElement.scrollWidth + 'px';
      if (!this.isIE6){
         modalWindow.style.background = 'url(images/tp2.png)';  //transparent png with low opacity.  Provides a similar effect as opacy/filter settings, but without the memory leaks
      }
      modalWindow.style.position = 'absolute';
      modalWindow.setAttribute('id','messageBoxId');
      modalWindow.style.left = '0px';
      modalWindow.style.top = '0px';
      if($('#alertWindow').length) modalWindow.style.zIndex = $('#alertWindow')[0].style.zIndex+1;
      else modalWindow.style.zIndex = 2990;
      modalWindow.style.visibility = 'hidden';
      document.body.appendChild(modalWindow);
      this.html.modalWindow = modalWindow;

      //shoehorn a iframe to cover our select elemtns for ie6.  what a crappy browser....
      if(this.isIE6){
         iframe = document.createElement('iframe');
         iframe.style.position = 'absolute';
         iframe.style.visibility = 'hidden';
         if($('#alertWindow').length) iframe.style.zIndex = $('#alertWindow').style.zIndex;
         else iframe.style.zIndex = 2989;
         iframe.frameBorder = 0;
         iframe.style.position = 'absolute';
         document.body.appendChild(iframe);
         this.html.iframe = iframe;
         //also, need to add an alpha image loader for ie6 transparency affect.  again, style.filter has a huge memory leak
         modalWindow.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='images/tp2.png', sizingMethod='scale', enabled=true)";
      }

      //Create our alert window
      alertWindow = document.createElement('div');
      alertWindow.className = this.settings.skin + '_alertWindow';
      alertWindow.setAttribute('id', 'alertWindow');
      alertWindow.style.position = this.isIE6 ? 'absolute' : 'fixed';
      if($('#alertWindow').length) alertWindow.style.zIndex = $('#alertWindow')[0].style.zIndex+2;
      else alertWindow.style.zIndex = 2991;

      //alertWindow.style.zIndex = 999;
      alertWindow.style.width=(this.settings.width)?this.settings.width+'px':this.defaultWidth+'px';
      /**if(this.settings.width){
         alertWindow.style.width=(isNaN(this.settings.width))?'auto':this.settings.width+'px';
      }*/
      //else alertWindow.style.width='auto';
      document.body.appendChild(alertWindow);
      alertWindow.style.visibility = 'hidden';
      this.html.alertWindow = alertWindow;

      //Create out title bar
      titleBar = document.createElement('div');
      titleBar.className = this.settings.skin + '_titleBar';
      titleBar.setAttribute('id', 'mssgTitleBar');
      if(this.settings.drag==true){
         //titleBar.mouseover=$("#mssgTitleBar").css({backgroundColor: "#FFFFCC"});
         //titleBar.mouseout=$("#mssgTitleBar").css('background','#000066');
         //titleBar.style.backgroundColor="#FFFFCC";
         titleBar.style.cursor='move';
      }
      alertWindow.appendChild(titleBar);
      this.html.titleBar = titleBar;

      //Create our right Icon
      ricon = document.createElement('div');
      ricon.className = this.settings.skin + '_titleBarRightIcon';
      ricon.style.cssFloat = 'right';
      ricon.style.styleFloat = 'right';
      titleBar.appendChild(ricon);
      this.html.ricon = ricon;

      //Create our Left Icon
      licon = document.createElement('div');
      licon.className = this.settings.skin + '_titleBarLeftIcon';
      licon.style.cssFloat = 'left';
      licon.style.styleFloat = 'left';
      titleBar.appendChild(licon);
      this.html.licon = licon;

      //Create our span that goes in our title
      title = document.createElement('span');
      title.innerHTML = this.settings.title;
      title.setAttribute('id','headMssg');
      titleBar.appendChild(title);
      this.html.title = title;

      //Create our main content area
      contentArea = document.createElement('div');
      contentArea.className = this.settings.skin + '_contentArea';
      contentArea.setAttribute('id','contentArea');
      contentArea.innerHTML = this.settings.text;
      if(this.settings.height){
         contentArea.style.height=(isNaN(this.settings.height))?'auto':this.settings.height+'px';
      }
      else contentArea.style.height=this.defaultHeight+'px';
      contentArea.style.maxHeight=height-90+'px';
      contentArea.style.overflow='auto';
      alertWindow.appendChild(contentArea);
      this.html.contentArea = contentArea;

      //Create out button area
      buttonArea = document.createElement('div');
      buttonArea.className = this.settings.skin + '_buttonArea';
      alertWindow.appendChild(buttonArea);
      this.html.buttonArea = buttonArea;

      //Draw an OK button if present
      if(this.settings.ok){
         okButton = document.createElement('input');
         okButton.type = 'button';
         okButton.className = this.settings.skin + '_okButton';
         okButton.setAttribute('id','okButton');
         okButton.value = this.settings.ok.text;
         okCallback = function (){
            that.settings.ok.onclick(that, that.settings.ok.value, that.settings.variables);
         };
         okButton.onclick = okCallback;
         buttonArea.appendChild(okButton);
         this.html.okButton = okButton;
      }

      //Draw a cancel button, if present
      if(this.settings.cancel){
         cancelButton = document.createElement('input');
         cancelButton.type = 'button';
         cancelButton.className = this.settings.skin + '_okButton';
         cancelButton.setAttribute('id','cancelButton');
         cancelButton.value = this.settings.cancel.text || 'Cancel';
         cancelCallback = function (){
            that.settings.cancel.onclick(that, that.settings.cancel.value, that.settings.variables);
         };
         cancelButton.onclick = cancelCallback;
         buttonArea.appendChild(cancelButton);
         this.html.cancelButton = cancelButton;
      }

      //start the drag process
      if(this.settings.drag == true) DragDrop.initElement({object:titleBar, realObjId:'alertWindow', titleId:'mssgTitleBar'});
      //Center our alert box on the screen
      if(this.settings.position && this.settings.place==true) this.place(this.settings.position.posLeft, this.settings.position.posTop);
      else this.center();
   }
};

/**
 *
 */
CustomMssgBox.DOMAlert.prototype.show = function(titleText, contentText){
   if(contentText){
      this.html.title.innerHTML = titleText;
      this.html.contentArea.innerHTML = contentText;
   }
   if (titleText && !contentText){
      this.html.contentArea.innerHTML = titleText;
   }

   this.html.modalWindow.style.visibility = 'visible';
   this.html.alertWindow.style.visibility = 'visible';
   if (this.html.iframe){
      this.html.iframe.style.height = this.html.alertWindow.offsetHeight;
      this.html.iframe.style.width = this.html.alertWindow.offsetWidth;
      this.html.iframe.style.visibility = 'visible';
   }
}

/**
 * Hide the message box
 */
CustomMssgBox.DOMAlert.prototype.hide = function (){
	this.html.modalWindow.style.visibility = 'hidden';
	this.html.alertWindow.style.visibility = 'hidden';
	if (this.html.iframe)
	{
		this.html.iframe.style.visibility = 'hidden';
	}
};

/**
 * Close the message box
 */
CustomMssgBox.DOMAlert.prototype.close = function (){
	var obj, prop;

	//make sure our DOM objects are deleted and our onclick statements are nulled
	for (obj in this.html){
		if (this.html[obj].parentNode){
			if (this.html[obj].onclick){
				this.html[obj].onclick = null;
			}
			this.html[obj].parentNode.removeChild(this.html[obj]);
			delete this.html[obj];
		}
	}

	//remove object properties
	for (prop in this){
		if (this[prop]){
			this[prop] = null;
			delete this[prop];
		}
	}
};

/**
 * Center the message box on the page
 */
CustomMssgBox.DOMAlert.prototype.center = function (){
	var alertWindow, scrollT, scrollL, iframe;
	alertWindow = this.html.alertWindow;
	if (alertWindow.style.position === 'absolute'){
		scrollT = window.pageYOffset || document.documentElement.scrollTop;
		scrollL = window.pageXOffset || document.documentElement.scrollLeft;
		alertWindow.style.left = (self.innerWidth || (document.documentElement.clientWidth || document.body.clientWidth)) / 2 + scrollL - alertWindow.offsetWidth / 2 + 'px';
		alertWindow.style.top = (self.innerHeight || (document.documentElement.clientHeight || document.body.clientHeight)) / 2 + scrollT - alertWindow.offsetHeight / 2 + 'px';
		if (this.html.iframe){
			this.html.iframe.style.left = alertWindow.style.left;
			this.html.iframe.style.top = alertWindow.style.top;
		}
	}else{
		alertWindow.style.left = (self.innerWidth || (document.documentElement.clientWidth || document.body.clientWidth)) / 2 - alertWindow.offsetWidth / 2 + 'px';
		alertWindow.style.top = (self.innerHeight || (document.documentElement.clientHeight || document.body.clientHeight)) / 2 - alertWindow.offsetHeight / 2 + 'px';
	}
};

/**
 * Place the message box at a specific place on the screen
 */
CustomMssgBox.DOMAlert.prototype.place = function(left, top){
   var alertWindow=this.html.alertWindow;
   alertWindow.style.left = left+'px';
	alertWindow.style.top = top+'px';
};

/**
 * Drag and drop functions.
 *
 * @author www.quirksmode.org lovely site
 */
DragDrop = {
	keySpeed: 5, // pixels per keypress event
	initialMouseX: undefined,
	initialMouseY: undefined,
	startX: undefined,
	startY: undefined,
	dXKeys: undefined,
	dYKeys: undefined,
	draggedObject: undefined,
   draggedObjectId: undefined,   //saves the object being dragged to enable dragging multiple times
   titleBar: undefined,    //title bar of the element being moved

	initElement: function (data) {
      if (DragDrop.draggedObject) DragDrop.releaseElement();
      var element;
		if (typeof data.titleId == 'string') element = document.getElementById(data.titleId);
      else element=data.titleId
      if (typeof data.realObjId == 'string') DragDrop.draggedObject=document.getElementById(data.realObjId);
      else DragDrop.draggedObject=data.realObjId;
      DragDrop.draggedObjectId = DragDrop.draggedObject.id;   //backup option
		element.onmousedown = DragDrop.startDragMouse;
	},

   /**
    * Binds the mouse movements to the moving of the element
    */
	startDragMouse: function (e) {
      //since we are dragging an object other than the object being clicked, ensure the object is not deleted
      if(DragDrop.draggedObject==undefined) DragDrop.draggedObject=document.getElementById(DragDrop.draggedObjectId);
      DragDrop.startDrag();
		var evt = e || window.event;
		DragDrop.initialMouseX = evt.clientX;
		DragDrop.initialMouseY = evt.clientY;
		DragDrop.addEventSimple(document,'mousemove',DragDrop.dragMouse);
		DragDrop.addEventSimple(document,'mouseup',DragDrop.releaseElement);
		return false;
	},

   /**
    * Binds the arrow keys to the moving function
    */
	startDragKeys: function () {
		DragDrop.startDrag(this.relatedElement);
		DragDrop.dXKeys = DragDrop.dYKeys = 0;
		DragDrop.addEventSimple(document,'keydown',DragDrop.dragKeys);
		DragDrop.addEventSimple(document,'keypress',DragDrop.switchKeyEvents);
		this.blur();
		return false;
	},

   /**
    * Method which intiates the start of the drag
    * @param obj object    Object being moved
    */
	startDrag: function (obj) {
		//the obj is redundant, since the object being dragged should be set in DragDrop.draggedObject
      DragDrop.startX = DragDrop.draggedObject.offsetLeft;
		DragDrop.startY = DragDrop.draggedObject.offsetTop;
		DragDrop.draggedObject.className += ' dragged';
	},

   /**
    * Captures the mouse movements, calcuates the distance moved and calls moving method
    * @param e object the object calling triggering the moving event, ie the div we are moving
    */
	dragMouse: function (e) {
		var evt = e || window.event;
		var dX = evt.clientX - DragDrop.initialMouseX;
		var dY = evt.clientY - DragDrop.initialMouseY;
		DragDrop.setPosition(dX,dY);
		return false;
	},

   /**
    * Moving by keyboard
    */
	dragKeys: function(e) {
		var evt = e || window.event;
		var key = evt.keyCode;
		switch (key) {
			case 37:	// left
			case 63234:
				DragDrop.dXKeys -= DragDrop.keySpeed;
				break;
			case 38:	// up
			case 63232:
				DragDrop.dYKeys -= DragDrop.keySpeed;
				break;
			case 39:	// right
			case 63235:
				DragDrop.dXKeys += DragDrop.keySpeed;
				break;
			case 40:	// down
			case 63233:
				DragDrop.dYKeys += DragDrop.keySpeed;
				break;
			case 13: 	// enter
			case 27: 	// escape
				DragDrop.releaseElement();
				return false;
			default:
				return true;
		}
		DragDrop.setPosition(DragDrop.dXKeys,DragDrop.dYKeys);
		if (evt.preventDefault)
			evt.preventDefault();
		return false;
	},

   /**
    * Does the actual moving, ie repositioning of the element being moved
    */
	setPosition: function (dx,dy) {
		DragDrop.draggedObject.style.left = DragDrop.startX + dx + 'px';
		DragDrop.draggedObject.style.top = DragDrop.startY + dy + 'px';
	},

	switchKeyEvents: function () {
		// for Opera and Safari 1.3
		DragDrop.removeEventSimple(document,'keydown',DragDrop.dragKeys);
		DragDrop.removeEventSimple(document,'keypress',DragDrop.switchKeyEvents);
		DragDrop.addEventSimple(document,'keypress',DragDrop.dragKeys);
	},

   /**
    * End of dragging. Release all the handles initialized at the start
    */
	releaseElement: function() {
		DragDrop.removeEventSimple(document,'mousemove',DragDrop.dragMouse);
		DragDrop.removeEventSimple(document,'mouseup',DragDrop.releaseElement);
		DragDrop.removeEventSimple(document,'keypress',DragDrop.dragKeys);
		DragDrop.removeEventSimple(document,'keypress',DragDrop.switchKeyEvents);
		DragDrop.removeEventSimple(document,'keydown',DragDrop.dragKeys);
		DragDrop.draggedObject.className = DragDrop.draggedObject.className.replace(/dragged/,'');
		DragDrop.draggedObject = null;
	},

   addEventSimple: function(obj,evt,fn) {
      if (obj.addEventListener)
         obj.addEventListener(evt,fn,false);
      else if (obj.attachEvent)
         obj.attachEvent('on'+evt,fn);
   },

   removeEventSimple: function(obj,evt,fn) {
      if (obj.removeEventListener)
         obj.removeEventListener(evt,fn,false);
      else if (obj.detachEvent)
         obj.detachEvent('on'+evt,fn);
   }
}
