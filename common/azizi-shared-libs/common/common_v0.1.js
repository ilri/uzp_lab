var paged=getVariable('page',document.location.search.substring(1));
var typed=getVariable('type',document.location.search.substring(1));
var selId=getVariable('id',document.location.search.substring(1));
var packaging=(typed=='package' && selId==undefined)?true:false;
var classPackage=undefined;
var drag={isDragging:undefined};  //used for dragging divs

//if the user is using IE download the firebug for IE. This will be used mainly for development purposes
//if(navigator!=undefined){
//   if(navigator.appName=='Microsoft Internet Explorer'){
//      var firebug=document.createElement('script');
//      firebug.setAttribute('src','../../common/firebug_lite_cp.js');
//      document.body.appendChild(firebug);(function(){
//         if(window.firebug.version){
//            firebug.init();
//         }
//         else{
//            setTimeout(arguments.callee);
//         }
//      })();
//      void(firebug);
//   }
//}

//object detection
function getObject(elementId){
	if (document.all) return document.all(elementId);
	else if (document.getElementById) return document.getElementById(elementId);
	else if (document.layers) return document.layers[elementId];
	else return undefined;
}

function myGetCookie(labelName) {
	var labelLen = labelName.length
	// read cookie property only once for speed
	var cookieData = document.cookie
	var cLen = cookieData.length
	var i = 0
	var cEnd
	while (i < cLen) {
		var j = i + labelLen
		if (cookieData.substring(i,j) == labelName) {
			cEnd = cookieData.indexOf(";",j)
			if (cEnd == -1) {
				cEnd = cookieData.length
			}
			return unescape(cookieData.substring(j+1, cEnd))
		}
	i++
	}
	return null
}

function getVariable(name,queryStr){
//it gets a string as the variables passed in the location and returns a variable by the specific name
	queryStr=unescape(queryStr)		//make it a proper string
	queryStr=queryStr.replace("+"," ").replace("+"," ")	//remove the +'s
    if (queryStr.length != 0) {
      splitArray = queryStr.split("&")	//convert it to an array
      for (i=0; i<splitArray.length; i++) {
		var splits=splitArray[i].split("=");
		if(splits[0]==name) return splits[1];
		//eval(splitArray[i])		//evaluate the expression //take it literally. we will be havin somethin like var1=val1
      }
    }
	return undefined;
}

function serializeData(form){
var params='';
var formElements=[];
	//get all the text areas
	var temp=form.getElementsByTagName('textarea');
	for(var i=0;i<temp.length;i++) formElements[formElements.length] = temp[i];
	//get all the selects
	temp=form.getElementsByTagName('select');
	for(i=0;i<temp.length;i++) formElements[formElements.length] = temp[i];
	//get all the input fields
	temp=form.getElementsByTagName('input');
	for(i=0;i<temp.length;i++){
		tempType=temp[i].getAttribute('type');
		if(tempType==null || tempType=='text' || tempType=='hidden' || (typeof temp[i].checked!='undefined' && temp[i].checked==true))
			formElements[formElements.length] = temp[i];
	}
	//now serialize and encode all data
	for(i=0;i<formElements.length;i++){
		var elementName=formElements[i].getAttribute("name");
		if(elementName!=null && elementName!='') params+='&'+elementName+'='+encodeURIComponent(formElements[i].value);
	}
return params;
}

function makeRequest(type){
	if(window.XMLHttpRequest){ // Mozilla, Safari, ...
		httpRequest = new XMLHttpRequest();
		if (httpRequest.overrideMimeType) {
			httpRequest.overrideMimeType(type);
			// See note below about this line
		}
    }
    else if(window.ActiveXObject){ // IE
		try{httpRequest = new ActiveXObject("Msxml2.XMLHTTP");}
        catch(e){
			try{httpRequest = new ActiveXObject("Microsoft.XMLHTTP");}
			catch(e){}
        }
	}

    if (!httpRequest) {
		alert('Giving up :( Cannot create an XMLHTTP instance');
        return false;
	}
	return httpRequest;
}

/**
 * Hides or shows a part of the page
 *
 * @param <string> objectId   The id of the part of page to hide/show
 * @param <string> action     Can be hide/show If specified, it specifies wat to do with the div else this function toggles the
 * visibility of the div
 * @return <mixed>   Returns undefined if the div aint found, else returns 0
 */
function showHide(objectId, action){
	var target=getObject(objectId);
   if(target==undefined) return undefined;

   if(action!=undefined){
      if(action=='hide'){
         if(hasClass(target,'expanded')){
            removeClass(target,'expanded');
            addClass(target,'collapsed');
         }
         else if(hasClass(target,'collapsed')){}   //the class is there whic is good
         else addClass(target,'collapsed')
      }
      else if(action=='show'){
         if(hasClass(target,'collapsed')){
            removeClass(target,'collapsed');
            addClass(target,'expanded');
         }
      }
   }
   else if(hasClass(target,'expanded')){
      removeClass(target,'expanded');
      addClass(target,'collapsed');
   }
   else if(hasClass(target,'collapsed')){
      removeClass(target,'collapsed');
      addClass(target,'expanded');
   }
   return 0;
}
  
function removeClass(target, theClass){
	var pattern = new RegExp("(^| )" + theClass + "( |$)");
	target.className = target.className.replace(pattern, "$1");
	target.className = target.className.replace(/ $/, "");
}

function addClass(target, theClass){
	if (!hasClass(target, theClass)){
		if (target.className == "") target.className = theClass;
		else target.className += " " + theClass;
	}
}

function hasClass(target, theClass){
  var pattern = new RegExp("(^| )" + theClass + "( |$)");
  if (pattern.test(target.className)) return true;
  return false;
}

/**Utility Function -- creates a modal background over a part of the page
 *@param objectId string(HTML id) of the object we have to cover
 */
function blank(objectId, imageURL){
	//Create a  modal background
   if($('#modalWindowId').length!=0 && $('#modalWindowId')[0].style.backgroundImage=='url('+imageURL+')'){
      getObject('modalWindowId').style.visibility = 'visible';
      return;
   }
	var modalWindow = document.createElement('div');
	modalWindow.setAttribute('id','modalWindowId');
	var objectToCover=getObject(objectId);
	modalWindow.style.height = objectToCover.clientHeight +'px';
	modalWindow.style.width = objectToCover.clientWidth +'px';
	if (!this.isIE6){
		modalWindow.style.background = 'url('+imageURL+')';  //transparent png with low opacity.  Provides a similar effect as opacy/filter settings, but without the memory leaks
	}
	modalWindow.style.position = 'relative';
	modalWindow.style.left = '0px';
      modalWindow.style.top = '-'+objectToCover.clientHeight+'px';
	modalWindow.style.zIndex = 998+$('#modalWindowId').length;
	//modalWindow.style.visibility = 'hidden';
	objectToCover.appendChild(modalWindow);
	//document.html.modalWindow = modalWindow;
	modalWindow.style.visibility = 'visible';
}

/**
 *Utility Function -- Removes the modal background created by the function blank
 */
function unblank(){
	getObject('modalWindowId').style.visibility = 'hidden';
}

/**
 *Utility Function -- Validation script
 *@param input mixed    The data to be validated
 *@param type integer   The option to use for validation. Can be the reg exp itself
 *@return true bool     If it passes validation
 *@return false bool    If it fails validation
 */
function validate(input, type){
//takes the input and the type of input we expecting. we try and match it with our regexp.
//return true if ok n false if not
var reg=undefined;
   if(isNaN(type)){
      reg = type;
      return reg.test(input);
   }
	if(type==0){
		if(input==null || input=="") return false;
		else return true;
	}
	//no spaces allowed
	if(type==1) reg=/\S+/g;
	//positive integer
	if(type==2){
      if(input==0) return false;
		reg=/[0-9]+/
		return reg.test(input);
	}
	//positive or 0 integer
	if(type==3) reg=/[^0-9]+/
	//integer
	if(type==4) reg=/[^0-9\-]/g;
	//decimal
	if(type==5) reg=/[^0-9\.]/g;
	//email
	if(type==6) reg=/^[\w\.\-]+@([\w\-\.]+\.)+([a-zA-Z]+)|([a-zA-Z]+\.[a-zA-Z]+)$/g;
	//no integers
	if(type==7) reg=/[0-9]/g;
	//illegal characters
	if(type==8) reg=/\/#\$\|=~/g;
	//tone words only
	if(type==9) reg=/[^hls]/gi;
	//phone numbers
	if(type==10) reg=/^\+?\d/g;
	//social sites nicks
	if(type==11) reg=/[^\w\.\-\_]/gi;
	//a value must be there in the get element
	if(type==12) reg=/=.+&/g;
	//names, titles
	if(type==13){
		reg=/[0-9\/#\$\|=~!@%\^\*\+\{\}\[\]\:\;\\]+/gi;		//\/#\$\|=~!@%\^\*\+\{\}\[\]\:\;
		if(reg.test(input)) return false;
		return true;
	}
	//text only with integers
	//date with a format of dd-mmm-yyyy eg 28-jan-2009
	if(type==15){
      reg=/^(\d{2})\-(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\-(\d{4})$/i
		if(reg.test(input)) return true;
		else return false;
	}
	//alpha numeric
	if(type==16) reg=/[^0-9a-zA-Z_\s-,.]+/
	//date with dd/mm/yyyy
	if(type==17){
		reg=/^\d{2}\/\d{2}\/\d{4}$/
		if(reg.test(input)) return true;
		else return false;
	}
   //date time separators
   if(type==18) reg=/[^:,.]/g;
   //Only alphabetic characters and space allowed and a period
   if(type==19){
      reg=/[A-Za-z\\s\.]/g
      return reg.test(input);
   }
   //comments
   if(type==20) reg=/[^0-9a-zA-Z_\s-,.()]+/i
   //material names
	if(type==21) reg=/[^0-9a-zA-Z_\s-.\/]+/
   //time formats
   if(type==22) reg=/[^0-9.:,]/g
	
	if(reg.test(input)) return false;
	else return true; //no match is found
}

/**
 * Validates the dates.
 * @param dates string                  The dates to validate. Expected format-->dd/mm/yyyy.
 * @param showMessage bool              Show error message or not
 * @return array(start_date, end_date)  If the dates pass validation
 * @return -1                           If the validation fails
 */
function validateDates(dates, showMessage){
   var allDates, temp, sDate, eDate, t;
   allDates=dates.split(',');
   if(allDates.length==0) return -1;
   for(var i=0; i<allDates.length; i++){
      temp=trim(allDates[i]);
      t=temp.indexOf('-');
      if(t==-1){
         if(validate(temp, 17)==false) return -1;
         if(sDate==undefined){sDate=temp;eDate=temp;}
         else{
            if(compareDate(pTodaysDate, temp)==2){createMessageBox('Error! Please select only future dates for the orders.', doNothing, false);return -1;}
            if(compareDate(sDate, temp)!=1) sDate=temp;
            if(compareDate(eDate, temp)!=2) eDate=temp;
         }
      }
      else{	//we have a range
         temp=temp.split('-');
         if(temp.length!=2) return -1;
         if(compareDate(pTodaysDate, temp[0])==2 || compareDate(pTodaysDate, temp[1])==2){
            createMessageBox('Error! Please select only future dates for the orders.', doNothing, false);return -1;
         }
         if(validate(temp[0], 17)==false) return -1;
         if(validate(temp[1], 17)==false) return -1;
         if(compareDate(temp[0], temp[1])!=1){
            if(showMessage==undefined){createMessageBox('Check the date range. The end dates should be greater than the start dates',doNothing, false);return -1;}
            else if(showMessage==false) return -1;
         }
         if(sDate==undefined){sDate=temp[0];eDate=temp[1];}
         else{
            if(compareDate(sDate, temp[0])!=1) sDate=temp[0];
            if(compareDate(eDate, temp[1])!=2) eDate=temp[1];
         }
      }
   }
   return new Array(sDate, eDate)
}

/**
 *Utility Function -- Validates the time as entered by the user.
 *@param senderId string(HTML id)   The field to pick the time for validation
 *@exception errorMessage           In case the time validation fails
 */
function validateTime(senderId){
   var temp1=getObject(senderId), temp;
   var t1, t2, reg;
   temp=temp1.value;
   if(temp=='') return;
   var message='Error! Check the time entered. Expecting a time in 24 hour format in the formats: 07:00, 7:00 or 700 denoting 7am with either (.),(,),(:) as the separator.';
   if(validate(temp, 22)==false){   //allow only desired chars ie 0-9,.:
      createMessageBox(message, doNothing, false);
      temp1.focus();temp1.value='';
      return;
   }
   else if(temp.length>5){
      createMessageBox(message, doNothing, false);
      temp1.focus();temp1.value='';
      return;
   }

   if(temp.search(/\.{1}/g)!=-1) t1=temp.search(/\./g);
   else if(temp.search(/,{1}/g)!=-1) t1=temp.search(/,/g);
   else if(temp.search(/:{1}/g)!=-1) t1=temp.search(/:/g);
   else t1=-1;

   if(t1!=-1){  //the user specified a : or . or , in the specified time which must be either in the 2nd or 3rd position
      if(t1!=1 && t1!=2){
         createMessageBox(message+t1, doNothing, false);
         temp1.focus();return;
      }
      if(temp.search(/\.{2,}/g)!=-1 || temp.search(/,{2,}/g)!=-1 || temp.search(/:{2,}/g)!=-1){
         createMessageBox(message+t1, doNothing, false);
         temp1.focus();return;
      }
      else if(/\d{2}/.test(temp.slice(t1))==false){   //the last 2 characters must be digits
         createMessageBox(message, doNothing, false);
         temp1.focus();return;
      }
      if(/\d{2}:\d{2}/.test(temp)) return;   //the needed format
      //replace any other item with the semi-colon
      temp=temp.replace(/(\.|,)/g, ":");
      if(t1==1) temp='0'+temp;
      temp1.value=temp;
   }
   else{    //the user didnt specify a separator
      if(temp.length==1) temp='00:0'+temp;
      else if(temp.length==2) temp='00:'+temp;
      else if(temp.length==3) temp='0'+temp.substring(0,1)+':'+temp.substr(1);
      else if(temp.length==4) temp=temp.substring(0,2)+':'+temp.substr(2);
      temp1.value=temp;
   }
}

/**
 * Creates a pop up kind of an interface
 *
 * @param string message      A message or the HTML code to be displayed in the 'pop up'
 * @param string callBack     A function that will be called on clicking either the true or false buttons
 * @param bool cancelling     Specifies whether to have a false/cancel button on the interface
 * @param mixed vars          (Optional) These are variables that should be passed to the callBack function
 * @param bool disableDrag    (Optional)Specifies whether to disable the dragging effect of the 'popup'
 * @param string customTitle  (Optional)A custom title that we want to appear on the dialog box
 */
function createMessageBox(message, callBack, cancelling, vars, disableDrag, customTitle){
   //variables are the variables that should be passed to the callback function. this will be held in an array
   var msg, settings={};
   settings.drag=(disableDrag===true)?false:true;
   if(customTitle!==undefined) settings.title=customTitle;
   else settings.title=(Main.title!==undefined)?Main.title:title;
   settings.text=message;
   settings.skin='default';
      
   if(cancelling){
      settings.ok={value: true, text: 'Yes', onclick: eval(callBack)};
      settings.cancel={value: false, text: 'No', onclick: eval(callBack)};
      settings.variables=vars;
   }
   else{
      settings.ok={value: true, text: 'Ok', onclick: eval(callBack)}
   }
   msg=new DOMAlert(settings);
   msg.show();
}

//used by the message box when we just want to doNothing
function doNothing(sender){sender.close();}

//gets a hidden div and places it right below the sender while visible
function showHideBelowSender(id, sender){
   //Create our alert window
	/**var login;
   this.html = {};
   login = document.createElement('div');
	login.className = 'expanded';
	login.setAttribute('id', 'loginWindow');
	login.style.position = this.isIE6 ? 'absolute' : 'fixed';
	login.style.zIndex = 999;

	login.style.width='200px';
	document.body.appendChild(login);
	login.style.visibility = 'visible';
	this.html.login = login;*/

	var temp=getObject(id), newX, newY;
	if(temp.className=='collapsed'){		//expand it
		newX = sender.style.left;
		newY = sender.style.top + sender.style.height + 5;
		temp.style.left = newX;temp.style.top = newY;
	}
	showHide(id);
}

function ajaxRequest(url, processor, type, params){
	var httpRequest=makeRequest(type);

	httpRequest.onreadystatechange = function(){eval(processor)};
	httpRequest.open("POST", url, true);
	httpRequest.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
   httpRequest.send(params);
}

//process an ajax request and calls a callbackfunction to process data from the server.
//it anticipates a successful request to be completed and expects that the calback function will receive the 2nd part as the input.
//incase of an error, it outputs the message via the createMssgBox
function processAjaxRequest(data, callBackFunction){
	if(data.readyState==4){
		if(data.status==200 || data.status==304){
			var resp=data.responseText.split('$$');
			if(resp[0]=='error'){
				if(getObject('notification_box')!=undefined){
					notificationMessage({create:false, hide:true, updateText:true, text:'Error while saving the changes.', error:true});
				}
				createMessageBox(resp[1], doNothing, false);
			}
			else eval(callBackFunction+"(\""+resp[1]+"\")");
		}
	}
}

function processEvaluateAjaxRequest(data){
	if(data.readyState==4){
		if(data.status==200 || data.status==304){
			var resp=data.responseText.split('$$');
			if(resp[0]=='error'){
				if(getObject('notification_box')!=undefined){
					notificationMessage({create:false, hide:true, updateText:true, text:'Error while saving the changes.', error:true});
				}
				createMessageBox(resp[1], doNothing, false);
			}
         else eval(resp[1]);
		}
	}
}

/**
 * Processes data received from the server after an ajax request has completed
 * @param <string> data    The data received from the server
 */
function ajaxUpdateInterface(data){
   var message, err=true;
   var resp=data.split('$$');
   if(Main.ajaxParams.successMssg!=undefined) message=Main.ajaxParams.successMssg;
   else message='The changes have been successfully saved.';
   if(resp[0]=='error') message=resp[1];
   else if(resp[0]=='no_error'){
      if(Main.ajaxParams.div2Update) $('#'+Main.ajaxParams.div2Update).html(resp[1]);
      if(resp[2]!=undefined) $('#addinfoId').html(resp[2]);
      err=false;
   }
   else if(resp[0].substr(0,2)=='-1') message=resp[0].substring(2,resp[0].length);
   else if(resp[0]=='new'){    //we have no error
      if(Main.ajaxParams.div2Update!=undefined) $('#'+Main.ajaxParams.div2UpdateNew).html(resp[1]);
      if(resp[2]!=undefined) $('#addinfoId').html(resp[2]);
      err=false;
   }
   else{    //we have no error
      if(Main.ajaxParams.div2Update!=undefined) $('#'+Main.ajaxParams.div2Update).html(resp[0]);
      if(resp[2]!=undefined) $('#addinfoId').html(resp[2]);
      err=false;
   }
   if(getObject('notification_box')!=undefined){
      notificationMessage({create:false, hide:true, updateText:true, text:message, error:err});
   }
   Main.ajaxParams.result=(resp[0]=='error')?'error':'no_error';
//   if(Main.initSorting===true) initSorting();
   if(Main.ajaxParams.callFunction) eval(Main.ajaxParams.callFunction);
}

function ajaxEvaluateScript(data){
   var message, err=true;
   var resp=data.split('$$');
   if(resp[0]=='error') message=resp[1];
   else if(resp[0]=='no_error'){
      message='Data successfully updated.';eval(resp[1]);err=false;
   }
   else if(resp[0].substr(0,2)=='-1') message=resp[0].substring(2,resp[0].length);
   else{message='Data successfully updated.';eval(resp[0]);err=false;}

   if(getObject('notification_box')!=undefined){
      notificationMessage({create:false, hide:true, updateText:true, text:message, error:err});
   }
   Main.ajaxParams.result=(err===true)?'error':'no_error';
}

//creates a message informing the user to wait while the changes are being updated
function waitMessage(callBack, message, cancelButton){
	var temp;
	if(message==undefined) message='Please wait while the changes are being updated...';
	if(cancelButton!=undefined && cancelButton==true){
		createMessageBox(message,callBack,true,undefined,30,undefined);
		temp=getObject('cancelButton');removeClass(temp,'default_okButton');addClass(temp,'collapsed');
	}
	else createMessageBox(message, callBack, false, undefined, 30, undefined);
	temp=getObject('okButton');removeClass(temp,'default_okButton');addClass(temp,'collapsed');	//hide the ok button
}

//evaluates a given script, mostly used by an ajax request
function evaluateScript(script){eval(script);}

//it process an ajax response and assumes that the wait message box is showing, so it updates it depending on the results of the ajax
//request and also updates the interface as depicted by fieldId
function updateInterface(data, fieldId, updateMssg){
	var message;
	if(data.readyState==4){
		if(data.status==200 || data.status==304){
			var resp=data.responseText.split('$$');
			if(resp[0]=='error'){
            if(getObject('notification_box')!=undefined){
					notificationMessage({create:false, hide:true, updateText:true, text:'Error while saving the changes.', error:true});
				}
				message=resp[1];updateMessageBox(message, undefined, 'error');
			}
			else{
				if(fieldId) $('#'+fieldId).attr({innerHTML:''}).attr({innerHTML:resp[1]});
            if(resp[2]!=undefined) $('#addinfoId').html(resp[2]);
				//message="The page has been successfully refreshed.";
				if(updateMssg) updateMessageBox(updateMssg);
				//else updateMessageBox(message);
            if(getObject('notification_box')!=undefined){
					notificationMessage({create:false, hide:true, updateText:true, text:'The changes have been successfully saved.'});
				}
			}
		}
	}
}

function updateInterfacePlain(data, fieldId){
	if(data.readyState==4){
		if(data.status==200 || data.status==304){
			if(data.responseText==-1){
            if(getObject('notification_box')!=undefined){
					notificationMessage({create:false, hide:true, updateText:true, text:'Error while saving the changes.', error:true});
				}
			}
			else{
				$('#'+fieldId).html(data.responseText);
            if(getObject('notification_box')!=undefined){
					notificationMessage({create:false, hide:true, updateText:true, text:'The changes have been successfully saved.'});
				}
			}
		}
	}
}

function updateMessageBox(message, cancelBttn, onError){
	if(!getObject('contentArea')){
		if(onError=='error') createMessageBox(message, doNothing, false);
		return;	//if we have no message box, jst return
	}
	if(message) $('#contentArea').html(message);
	var temp=getObject('okButton');removeClass(temp,'collapsed');addClass(temp,'default_okButton');
	if(cancelBttn!=undefined && cancelBttn==true){
		temp=getObject('cancelButton');removeClass(temp,'collapsed');addClass(temp,'default_okButton');
	}
}

var defaultWidth=400, defaultHeight=50;

function DOMAlert(settings){
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
	if(getObject('alertWindow')) modalWindow.style.zIndex=getObject('alertWindow').style.zIndex+1;
	else modalWindow.style.zIndex = 990;
	modalWindow.style.visibility = 'hidden';
	document.body.appendChild(modalWindow);
	this.html.modalWindow = modalWindow;

	//shoehorn a iframe to cover our select elemtns for ie6.  what a crappy browser....
	if(this.isIE6){
		iframe = document.createElement('iframe');
		iframe.style.position = 'absolute';
		iframe.style.visibility = 'hidden';
		if(getObject('alertWindow')) iframe.style.zIndex = getObject('alertWindow').style.zIndex;
		else iframe.style.zIndex = 989;
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
	if(getObject('alertWindow')) alertWindow.style.zIndex = getObject('alertWindow').style.zIndex+2;
	else alertWindow.style.zIndex = 991;

	//alertWindow.style.zIndex = 999;
	alertWindow.style.width=(this.settings.width)?this.settings.width+'px':defaultWidth+'px';
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
      titleBar.mouseover=$("#mssgTitleBar").css({backgroundColor: "#FFFFCC"});
      titleBar.mouseout=$("#mssgTitleBar").css('background','#000066');
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
   else contentArea.style.height=defaultHeight+'px';
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
   if(this.settings.drag==true) dragSetup({object:titleBar, realObjId:'alertWindow', titleId:'mssgTitleBar'});
	//Center our alert box on the screen
	if(this.settings.position && this.settings.place==true) this.place(this.settings.position.posLeft, this.settings.position.posTop);
   else this.center();
}

DOMAlert.prototype.show = function (titleText, contentText){
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
};

DOMAlert.prototype.hide = function (){
	this.html.modalWindow.style.visibility = 'hidden';
	this.html.alertWindow.style.visibility = 'hidden';
	if (this.html.iframe)
	{
		this.html.iframe.style.visibility = 'hidden';
	}
};

DOMAlert.prototype.close = function (){
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

DOMAlert.prototype.center = function (){
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

//we wantt to place the messagebox in a specific place on the screen
DOMAlert.prototype.place = function(left, top){
   var alertWindow=this.html.alertWindow;
   alertWindow.style.left = left+'px';
	alertWindow.style.top = top+'px';
};

/**
 * Generates a combo box based on the settings passed to the function. Expecting these fields:
 *  name: 'prod', id: 'prodId', dispValues: '', hidValues: '', initValue: 'Select', selected: 0,
 *  enabled: true, onChange: undefined, type: 'multiple', width: undefined, size: 5
 */
function generateCombo(settings){
   var sel='', multiple;
   var changing=(settings.onChange==undefined)?'':"onChange='"+settings.onChange+"'";
   var enabled=(settings.enabled==true)?'':'disabled';
   var width=(settings.width==undefined)?'':"width='"+settings.width+"px;'";
   if(settings.type=='multiple') multiple="multiple='multiple'";
   else multiple="";
   var size=(settings.size==undefined)?'':"size='"+settings.size+"'";
   var content="<select name='"+settings.name+"' id='"+settings.id+"' "+enabled+" "+changing+" style=\""+width+"\" "+size+" "+multiple+">";

   if(settings.hidValues==undefined){
      for(var i=0;i<settings.dispValues.length+1;i++){
         sel=(parseInt(settings.selected)==i)?'selected':'';
         if(i==0){
            if(settings.initValue!=undefined) content+="<option value='0' "+sel+">"+settings.initValue;
         }
         else content+="<option value="+i+" "+sel+">"+settings.dispValues[i-1];
      }
   }
   else{
      for(var j=0;j<settings.dispValues.length+1;j++){
         sel=(parseInt(settings.selected)==parseInt(settings.hidValues[j-1]))?'selected':'';
         if(j==0){
            if(settings.initValue!=undefined) content+="<option value='0' "+sel+">"+settings.initValue;
         }
         else content+="<option value="+settings.hidValues[j-1]+" "+sel+">"+settings.dispValues[j-1];
      }
   }
   content+="</select>";
   return content;
}

//populates a combo with the various settings passed. gets the hidden values and display values from data.id and data.name respectively
function populateCombo(data, show){
   var id=[], name=[];
   for(var j=0; j<data.data.length; j++){
      if(data.data[j].id===undefined || data.data[j].name===undefined) continue;
      id[id.length]=data.data[j].id;
      name[name.length]=data.data[j].name;
   }

   if(data.selId===undefined) data.selId=0;
   var tsSettings={
      name:data.comboName, id:data.comboId, dispValues:name, hidValues:id, initValue:data.initValue,
      selected:data.selected, enabled:data.enabled, onChange:data.onChange, type:data.type, width:data.width, size:data.size
   };
   if(show==true || show==undefined) $('#'+data.spanId).html(generateCombo(tsSettings));
   else return generateCombo(tsSettings);
}

function trim(input){
	if(input==undefined || input=='') return '';
	var i, t, reg=/\s/, st, end;
	//removes all the leading and trailing spaces
	for(i=0;i<input.length;i++){
		t=input[i];
		if(reg.test(t) || t==" " || t=="\b" || t=="\t" || t=="\n" || t=="\r" || t=="\f") continue;
		else {st=i;break;}
	}
	if(i==input.length) return '';	//its empty
	for(i=input.length; i>-1; i--){
		t=input[i];
		if(t==" " || t=="\b" || t=="\t" || t=="\n" || t=="\r" || t=="\f" || t==undefined) continue;
		else {end=i;break;}
	}
	return input.substring(st, end+1);
}

//since the multiple combo can have more than 1 field selected, get all selected fields and return em as comma separated values
function getMultipleComboData(id){
	var temp=getObject(id), selected='';
	for(var i=0; i<temp.options.length; i++){
		if(temp.options[i].selected==true) selected+=(selected=='')?temp.options[i].value:', '+temp.options[i].value;
	}
	return selected;
}

//checks the occurence of a needle in a haystack, returns true if its there
function inArray(needle, haystack){
	for(var i=0; i<haystack.length;i++) if(needle==haystack[i]) return true;
	return false;
}

//just like inArray, but here, the array contains objects
function inArrayObjects(needle, haystack, index){
	for(var i=0; i<haystack.length;i++) if(needle==haystack[i].index) return true;
	return false;
}

//converts a time to and from the 24hr to 12hr system
//time--time to be converted with/out seconds, system the format of the time
function convertTime(time, system){
var t, t1, t2, t3, len, suffix;
   time=trim(time);len=time.length;
   if(system=='24HR'){
      if(len!=5 && len!=8) return -1;
      t=parseFloat(time.substr(0,2));t1=time.substr(3,2);t2=time.substr(6,2);
      suffix=(t<12)?'am':'pm';
      if(t!=12) t=(t<12)?t:t-12;
      if(t2=='') return t+'.'+t1+suffix;  //no seconds
      else return t+'.'+t1+'.'+t2+suffix; //wit seconds
   }
   else if(system=='12HR'){
      if(len!=6 && len!=7 && len!=10) return -1;
      if(len==6){t=parseInt(time.substr(0,1));t1=time.substr(2,2);t2='';t3=time.substr(4,2);}
      else if(len==7 || len==10){
         t=parseInt(time.substr(0,2));t1=time.substr(3,2);
         if(len==7){t2='';t3=time.substr(5,2);}
         else if(len==10){t2=time.substr(6,2);t3=time.substr(8,2);}
      }
      if(t3=='pm' && t!=12) t+=12;
      else t=(t.length==1)?'0'+t:t;
      if(t2=='') t2='00';
      return t+':'+t1+':'+t2;
   }
   else return -1;
}

//, displays it for 5 secs and then it disappears
/**
 * Creates a notification message on the right upper corner, the color, duration and message of the box is determined by the settings
 * @param <object> settings   Settings that are passed by the user
 */
function notificationMessage(settings){
var mss;
   if(settings.create==true){
      if(getObject('notification_box')!=undefined) closeObject('notification_box');
      if(Main==undefined || Main.domObjects==undefined || Main.domObjects['notification_box']==undefined){
         if(Main==undefined){
            Main={};
            Main.domObjects={};
         }
         else if(Main.domObjects==undefined) Main.domObjects={};
         mss = document.createElement('div');
         mss.innerHTML = '<span>'+settings.text+'</span>';
         mss.setAttribute('id', 'notification_box');
      }
      else{
         mss=Main.domObjects['notification_box'];
         mss.innerHTML='<span>'+settings.text+'</span>';
      }
      document.body.appendChild(mss);
   }
   if(getObject('notification_box')==undefined) return;
   if(settings.updateText==true) $('#notification_box').html('<span>'+settings.text+'</span>');
   if(settings.error==true) getObject('notification_box').firstChild.style.backgroundColor='#FF5F5F';
   else getObject('notification_box').firstChild.style.backgroundColor='#CCFF99';
   if(settings.hide==true && settings.error==false) setTimeout(clearNotification, 3000);	//always call this function last
   else if(settings.hide==true && settings.error==true) setTimeout(clearNotification, 10000);
   else if(settings.hide=='now') setTimeout(clearNotification, 100);
}

function clearNotification(){
	closeObject('notification_box');
}

/**
 * Closes/Removes an object from the interface
 */
function closeObject(objectId){
   var temp=getObject(objectId);
   if(temp===undefined) return;
   if(temp.parentNode===undefined) return;
   temp=temp.parentNode.removeChild(temp);
   if(Main.domObjects==undefined) Main.domObjects=[];
   Main.domObjects[objectId]=temp;
}
//=================================================Dragging divs around=====================================================
/**
 * Sets up a div or an object for dragging.
 * @param settings object parameters and object for dragging
 * @depracated partially on finding the perfect drag and drop script, I have decided to rest this function
 */
function dragSetup(settings){
//   settings.object.onmousedown=startDrag;
//   settings.object.onmouseup=stopDrag;
//   drag.objectId=settings.id;
//   drag.realObj=getObject(settings.realObjId);
     dragDrop.initElement(settings);
}

/**
 * Initializes the dragging paramaters and starts the dragging process
 * @param e object   The object being dragged
 * @depracated
*/
function startDrag(e){
   if(this.id!=drag.objectId) return;
   // calculate event X,Y coordinates
   drag.offsetX=e.clientX;
   drag.offsetY=e.clientY;
   // assign default values for top and left properties
   if(!drag.realObj.style.left) drag.realObj.style.left='0px';
   if(!drag.realObj.style.top) drag.realObj.style.top='0px';
   // calculate integer values for top and left properties
   drag.coordX=parseInt(drag.realObj.style.left);
   drag.coordY=parseInt(drag.realObj.style.top);
   drag.isDragging=true;
   this.onmousemove=dragDiv; // move div element
   //document.onmousemove=dragDiv;
}

/**
 * Computes the new coordinates of a div when dragging it
 * @param e object   The object being dragged
 * @depracated
 */
function dragDiv(e){
   if(drag.isDragging==false) return;
   if(!e) return;
   // move div element
   drag.realObj.style.left=drag.coordX + e.clientX - drag.offsetX +'px';
   drag.realObj.style.top=drag.coordY + e.clientY - drag.offsetY +'px';
}

/**
 * Stops dragging a div
 * @depracated
 */
function stopDrag(){
 drag.isDragging=false;
 document.onmousemove=undefined;
}

/**
 * Drag and drop functions.
 * @author www.quirksmode.org lovely site
 */
dragDrop = {
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
      if (dragDrop.draggedObject) dragDrop.releaseElement();
      var element;
		if (typeof data.titleId == 'string') element = document.getElementById(data.titleId);
      else element=data.titleId
      if (typeof data.realObjId == 'string') dragDrop.draggedObject=document.getElementById(data.realObjId);
      else dragDrop.draggedObject=data.realObjId;
      dragDrop.draggedObjectId=dragDrop.draggedObject.id;   //backup option
		element.onmousedown = dragDrop.startDragMouse;
	},

   /**
    * Binds the mouse movements to the moving of the element
    */
	startDragMouse: function (e) {
      //since we are dragging an object other than the object being clicked, ensure the object is not deleted
      if(dragDrop.draggedObject==undefined) dragDrop.draggedObject=document.getElementById(dragDrop.draggedObjectId);
      dragDrop.startDrag();
		var evt = e || window.event;
		dragDrop.initialMouseX = evt.clientX;
		dragDrop.initialMouseY = evt.clientY;
		addEventSimple(document,'mousemove',dragDrop.dragMouse);
		addEventSimple(document,'mouseup',dragDrop.releaseElement);
		return false;
	},

   /**
    * Binds the arrow keys to the moving function
    */
	startDragKeys: function () {
		dragDrop.startDrag(this.relatedElement);
		dragDrop.dXKeys = dragDrop.dYKeys = 0;
		addEventSimple(document,'keydown',dragDrop.dragKeys);
		addEventSimple(document,'keypress',dragDrop.switchKeyEvents);
		this.blur();
		return false;
	},

   /**
    * Method which intiates the start of the drag
    * @param obj object    Object being moved
    */
	startDrag: function (obj) {
		//the obj is redundant, since the object being dragged should be set in dragDrop.draggedObject
      dragDrop.startX = dragDrop.draggedObject.offsetLeft;
		dragDrop.startY = dragDrop.draggedObject.offsetTop;
		dragDrop.draggedObject.className += ' dragged';
	},

   /**
    * Captures the mouse movements, calcuates the distance moved and calls moving method
    * @param e object the object calling triggering the moving event, ie the div we are moving
    */
	dragMouse: function (e) {
		var evt = e || window.event;
		var dX = evt.clientX - dragDrop.initialMouseX;
		var dY = evt.clientY - dragDrop.initialMouseY;
		dragDrop.setPosition(dX,dY);
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
				dragDrop.dXKeys -= dragDrop.keySpeed;
				break;
			case 38:	// up
			case 63232:
				dragDrop.dYKeys -= dragDrop.keySpeed;
				break;
			case 39:	// right
			case 63235:
				dragDrop.dXKeys += dragDrop.keySpeed;
				break;
			case 40:	// down
			case 63233:
				dragDrop.dYKeys += dragDrop.keySpeed;
				break;
			case 13: 	// enter
			case 27: 	// escape
				dragDrop.releaseElement();
				return false;
			default:
				return true;
		}
		dragDrop.setPosition(dragDrop.dXKeys,dragDrop.dYKeys);
		if (evt.preventDefault)
			evt.preventDefault();
		return false;
	},

   /**
    * Does the actual moving, ie repositioning of the element being moved
    */
	setPosition: function (dx,dy) {
		dragDrop.draggedObject.style.left = dragDrop.startX + dx + 'px';
		dragDrop.draggedObject.style.top = dragDrop.startY + dy + 'px';
	},

	switchKeyEvents: function () {
		// for Opera and Safari 1.3
		removeEventSimple(document,'keydown',dragDrop.dragKeys);
		removeEventSimple(document,'keypress',dragDrop.switchKeyEvents);
		addEventSimple(document,'keypress',dragDrop.dragKeys);
	},

   /**
    * End of dragging. Release all the handles initialized at the start
    */
	releaseElement: function() {
		removeEventSimple(document,'mousemove',dragDrop.dragMouse);
		removeEventSimple(document,'mouseup',dragDrop.releaseElement);
		removeEventSimple(document,'keypress',dragDrop.dragKeys);
		removeEventSimple(document,'keypress',dragDrop.switchKeyEvents);
		removeEventSimple(document,'keydown',dragDrop.dragKeys);
		dragDrop.draggedObject.className = dragDrop.draggedObject.className.replace(/dragged/,'');
		dragDrop.draggedObject = null;
	}
}

function addEventSimple(obj,evt,fn) {
	if (obj.addEventListener)
		obj.addEventListener(evt,fn,false);
	else if (obj.attachEvent)
		obj.attachEvent('on'+evt,fn);
}

function removeEventSimple(obj,evt,fn) {
	if (obj.removeEventListener)
		obj.removeEventListener(evt,fn,false);
	else if (obj.detachEvent)
		obj.detachEvent('on'+evt,fn);
}

//=================================================End of Dragging=============================================================

function changeColor(object, color){getObject(object).style.backgroundColor=color;}

//=================================================JQUERY Extensions=============================================================
jQuery.fn.extend({ 
  check: function() { 
    return this.each(function() {this.checked = true;}); 
  }, 
  uncheck: function() { 
    return this.each(function() {this.checked = false;}); 
  } 
});


/**
* Since the sorting function must be called on windows load, this function takes care of the need to explicitly initiate the sorting
* The code is copy pasted from sort_tables file
*/
function initSorting(){
// Dean Edwards/Matthias Miller/John Resig

/* for Mozilla/Opera9 */
if (document.addEventListener) {
    document.addEventListener("DOMContentLoaded", sorttable.init, false);
}

/* for Safari */
if (/WebKit/i.test(navigator.userAgent)) { // sniff
    var _timer = setInterval(function() {
        if (/loaded|complete/.test(document.readyState)) {
            sorttable.init(); // call the onload handler
        }
    }, 10);
}

/* for Internet Explorer */
/*@cc_on @*/
/*@if (@_win32)
    document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
    var script = document.getElementById("__ie_onload");
    script.onreadystatechange = function() {
        if (this.readyState == "complete") {
            sorttable.init(); // call the onload handler
        }
    };
/*@end @*/

/* for other browsers */
window.onload = sorttable.init;

sorttable.init();
}

function exit (status) {
    // !No description available for exit. @php.js developers: Please update the function summary text file.
    //
    // version: 1006.1915
    // discuss at: http://phpjs.org/functions/exit
    // +   original by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Paul
    // +   bugfixed by: Hyam Singer (http://www.impact-computing.com/)
    // +   improved by: Philip Peterson
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // %        note 1: Should be considered experimental. Please comment on this function.
    // *     example 1: exit();
    // *     returns 1: null
    var i, that = this,
        _addEvent = function (el, type, handler, capturing) {
            if (el.addEventListener) { /* W3C */
                el.addEventListener(type, handler, !!capturing);
            }
            else if (el.attachEvent) { /* IE */
                el.attachEvent('on'+type, handler);
            }
            else { /* OLDER BROWSERS (DOM0) */
                el['on'+type] = handler;
            }
        },
        _stopEvent = function(e) {
            if (e.stopPropagation) { /* W3C */
                e.stopPropagation();
                e.preventDefault();
            }
            else {
                that.window.event.cancelBubble = true;
                that.window.event.returnValue = false;
            }
        };

    if (typeof status === 'string') {
        alert(status);
    }

    _addEvent(this.window, 'error', function (e) {_stopEvent(e);}, false);

    var handlers = [
        'copy', 'cut', 'paste',
        'beforeunload', 'blur', 'change', 'click', 'contextmenu', 'dblclick', 'focus', 'keydown', 'keypress', 'keyup', 'mousedown', 'mousemove', 'mouseout', 'mouseover', 'mouseup', 'resize', 'scroll',
        'DOMNodeInserted', 'DOMNodeRemoved', 'DOMNodeRemovedFromDocument', 'DOMNodeInsertedIntoDocument', 'DOMAttrModified', 'DOMCharacterDataModified', 'DOMElementNameChanged', 'DOMAttributeNameChanged', 'DOMActivate', 'DOMFocusIn', 'DOMFocusOut', 'online', 'offline', 'textInput',
        'abort', 'close', 'dragdrop', 'load', 'paint', 'reset', 'select', 'submit', 'unload'
    ];

    for (i=0; i < handlers.length; i++) {
        _addEvent(this.window, handlers[i], function (e) {_stopEvent(e);}, true);
    }

    if (this.window.stop) {
        this.window.stop();
    }

    throw '';
}

CustomFunctions={
   get_html_translation_table: function(table, quote_style) {
       // http://kevin.vanzonneveld.net
       // +   original by: Philip Peterson
       // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       // +   bugfixed by: noname
       // +   bugfixed by: Alex
       // +   bugfixed by: Marco
       // +   bugfixed by: madipta
       // +   improved by: KELAN
       // +   improved by: Brett Zamir (http://brett-zamir.me)
       // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
       // +      input by: Frank Forte
       // +   bugfixed by: T.Wild
       // +      input by: Ratheous
       // %          note: It has been decided that we're not going to add global
       // %          note: dependencies to php.js, meaning the constants are not
       // %          note: real constants, but strings instead. Integers are also supported if someone
       // %          note: chooses to create the constants themselves.
       // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
       // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

       var entities = {}, hash_map = {}, decimal = 0, symbol = '';
       var constMappingTable = {}, constMappingQuoteStyle = {};
       var useTable = {}, useQuoteStyle = {};

       // Translate arguments
       constMappingTable[0]      = 'HTML_SPECIALCHARS';
       constMappingTable[1]      = 'HTML_ENTITIES';
       constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
       constMappingQuoteStyle[2] = 'ENT_COMPAT';
       constMappingQuoteStyle[3] = 'ENT_QUOTES';

       useTable       = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
       useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';

       if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
           throw new Error("Table: "+useTable+' not supported');
           // return false;
       }

       entities['38'] = '&amp;';
       if (useTable === 'HTML_ENTITIES') {
           entities['160'] = '&nbsp;';
           entities['161'] = '&iexcl;';
           entities['162'] = '&cent;';
           entities['163'] = '&pound;';
           entities['164'] = '&curren;';
           entities['165'] = '&yen;';
           entities['166'] = '&brvbar;';
           entities['167'] = '&sect;';
           entities['168'] = '&uml;';
           entities['169'] = '&copy;';
           entities['170'] = '&ordf;';
           entities['171'] = '&laquo;';
           entities['172'] = '&not;';
           entities['173'] = '&shy;';
           entities['174'] = '&reg;';
           entities['175'] = '&macr;';
           entities['176'] = '&deg;';
           entities['177'] = '&plusmn;';
           entities['178'] = '&sup2;';
           entities['179'] = '&sup3;';
           entities['180'] = '&acute;';
           entities['181'] = '&micro;';
           entities['182'] = '&para;';
           entities['183'] = '&middot;';
           entities['184'] = '&cedil;';
           entities['185'] = '&sup1;';
           entities['186'] = '&ordm;';
           entities['187'] = '&raquo;';
           entities['188'] = '&frac14;';
           entities['189'] = '&frac12;';
           entities['190'] = '&frac34;';
           entities['191'] = '&iquest;';
           entities['192'] = '&Agrave;';
           entities['193'] = '&Aacute;';
           entities['194'] = '&Acirc;';
           entities['195'] = '&Atilde;';
           entities['196'] = '&Auml;';
           entities['197'] = '&Aring;';
           entities['198'] = '&AElig;';
           entities['199'] = '&Ccedil;';
           entities['200'] = '&Egrave;';
           entities['201'] = '&Eacute;';
           entities['202'] = '&Ecirc;';
           entities['203'] = '&Euml;';
           entities['204'] = '&Igrave;';
           entities['205'] = '&Iacute;';
           entities['206'] = '&Icirc;';
           entities['207'] = '&Iuml;';
           entities['208'] = '&ETH;';
           entities['209'] = '&Ntilde;';
           entities['210'] = '&Ograve;';
           entities['211'] = '&Oacute;';
           entities['212'] = '&Ocirc;';
           entities['213'] = '&Otilde;';
           entities['214'] = '&Ouml;';
           entities['215'] = '&times;';
           entities['216'] = '&Oslash;';
           entities['217'] = '&Ugrave;';
           entities['218'] = '&Uacute;';
           entities['219'] = '&Ucirc;';
           entities['220'] = '&Uuml;';
           entities['221'] = '&Yacute;';
           entities['222'] = '&THORN;';
           entities['223'] = '&szlig;';
           entities['224'] = '&agrave;';
           entities['225'] = '&aacute;';
           entities['226'] = '&acirc;';
           entities['227'] = '&atilde;';
           entities['228'] = '&auml;';
           entities['229'] = '&aring;';
           entities['230'] = '&aelig;';
           entities['231'] = '&ccedil;';
           entities['232'] = '&egrave;';
           entities['233'] = '&eacute;';
           entities['234'] = '&ecirc;';
           entities['235'] = '&euml;';
           entities['236'] = '&igrave;';
           entities['237'] = '&iacute;';
           entities['238'] = '&icirc;';
           entities['239'] = '&iuml;';
           entities['240'] = '&eth;';
           entities['241'] = '&ntilde;';
           entities['242'] = '&ograve;';
           entities['243'] = '&oacute;';
           entities['244'] = '&ocirc;';
           entities['245'] = '&otilde;';
           entities['246'] = '&ouml;';
           entities['247'] = '&divide;';
           entities['248'] = '&oslash;';
           entities['249'] = '&ugrave;';
           entities['250'] = '&uacute;';
           entities['251'] = '&ucirc;';
           entities['252'] = '&uuml;';
           entities['253'] = '&yacute;';
           entities['254'] = '&thorn;';
           entities['255'] = '&yuml;';
       }

       if (useQuoteStyle !== 'ENT_NOQUOTES') {
           entities['34'] = '&quot;';
       }
       if (useQuoteStyle === 'ENT_QUOTES') {
           entities['39'] = '&#39;';
       }
       entities['60'] = '&lt;';
       entities['62'] = '&gt;';


       // ascii decimals to real symbols
       for (decimal in entities) {
           symbol = String.fromCharCode(decimal);
           hash_map[symbol] = entities[decimal];
       }

       return hash_map;
   },

   htmlentities: function (string, quote_style) {
       // http://kevin.vanzonneveld.net
       // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       // +   improved by: nobbler
       // +    tweaked by: Jack
       // +   bugfixed by: Onno Marsman
       // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
       // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
       // +      input by: Ratheous
       // -    depends on: get_html_translation_table
       // *     example 1: htmlentities('Kevin & van Zonneveld');
       // *     returns 1: 'Kevin &amp; van Zonneveld'
       // *     example 2: htmlentities("foo'bar","ENT_QUOTES");
       // *     returns 2: 'foo&#039;bar'

       var hash_map = {}, symbol = '', tmp_str = '', entity = '';
       tmp_str = string.toString();

       if (false === (hash_map = this.get_html_translation_table('HTML_ENTITIES', quote_style))) {
           return false;
       }
       hash_map["'"] = '&#039;';
       for (symbol in hash_map) {
           entity = hash_map[symbol];
           tmp_str = tmp_str.split(symbol).join(entity);
       }

       return tmp_str;
   }
}

jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ?
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}