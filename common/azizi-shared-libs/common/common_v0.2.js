/* 
 * Contains all the custom function that I always need the most
 *
 * @category	Common
 * @package 	common_js
 * @author     Kihara Absolomon <soloincc@movert.co.ke>
 * @version    0.2
 */

var Common = {

	/**
	 * Generates a combo box based on the settings passed to the function.
	 * 
	 * Example of use:
	 * <code>
	 *	var settings = {
	 *		name: 'ulevel',				               		//The name of the drop down that will be created
	 *		id: 'ulevelId',				               		//The Id of the drop down that will be created
	 *		data: {{id:0,name:'val1'},{id:1,name:'val2'},.},//The data to create the drop down with!
	 *		initValue: 'Select One',	               		//The initial value that will be displayed when nothing is selected
	 *		selected: 1,					               		//(Optional) The value that will be selected. Defualts to 0
	 *		matchByName: true,				               	//(Optional) Whether to check match the selected value by names instead of ids
	 *		enabled: false,				               		//(Optional) Whether to enable the resultant drop down or not
	 *		onChange: 'func2callWenChanged("vars to pass")',//(Optional)	The callback function when the drop down selection changes
	 *		type: 'multiple',					            		//(Optional) The drop down to create. Defaults to a single(kawaida) drop down
	 *		width: 5,							            		//(Optional) The width of the resultant drop down
	 *		size: 10								            		//(Optional) The size of the resultant drop down
	 *	}
	 * </code>
	 */
	generateCombo: function(settings){
		var sel='', multiple, valId;
		var changing = (settings.onChange == undefined)?'':"onChange='"+settings.onChange+"'";
		var enabled = (settings.enabled == undefined || settings.enabled == true) ? '' : 'disabled';
		settings.matchByName = (settings.matchByName == undefined || settings.matchByName == false) ? false : true;
		var width=(settings.width == undefined)?'':"width='"+settings.width+"px;'";
		multiple = (settings.type == 'multiple') ? multiple = "multiple='multiple'" : '';
		var size = (settings.size == undefined) ? '' : "size='" + settings.size + "'";
		var content = "<select name='" + settings.name + "' id='" + settings.id + "' " + enabled + " " + changing + " style=\"" + width + "\" " + size + " " + multiple + ">";
		var selected;
       if(settings.selected == undefined) selected = 0;
       else selected = (!isNaN(settings.selected)) ? parseInt(settings.selected) : settings.selected;

       content += (selected == 0) ? "<option value='0' selected>" : "<option value='0'>";
       content += (settings.initValue != undefined) ? settings.initValue : 'Select One';
       if(settings.data == undefined) return content+"</select>";
       
		$.each(settings.data, function(i, tmp){
          valId = (!isNaN(tmp.id)) ? parseInt(tmp.id) : tmp.id;
          if(settings.matchByName) sel = (selected == tmp.name) ? 'selected' : '';
          else sel = (selected == valId) ? 'selected' : '';
			if(valId == 0 && settings.initValue != undefined) content += "<option value='0' "+sel+">"+settings.initValue;
          else content += "<option value="+valId+" "+sel+">"+tmp.name;
		});
		content += "</select>";
		return content;
	},
	

   /**
    * Check whether the passed input passes the defined validation
    * 
    * @param   input mixed    The data to be validated
    * @param   type  integer  The validation option.
    * @return  bool  Returns true if the validation passes, else it returns false
    * @since   v0.2
    */
 
   validate: function(input, type){
      var reg=undefined;
       
      switch(type){
         case 'no_spaces':
            reg=/^\S/g
         case 'login':
            reg=/[^\w\.\-\_0-9]/gi
         case 'names':
            reg=/[0-9\/#\$\|=~!@%\^\*\+\{\}\[\]\:\;\\]+/gi
      }
       
      if(reg.test(input)) return false;
      else return true; //no match is found
       
      //no spaces allowed
      if(type==1) 
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
      //a value must be there in the get element
      if(type==12) reg=/=.+&/g;
      //names, titles
      if(type==13){
      		
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
   },
   
   /**
    * Updates the user interface based on the data received from the server. Incase there is an error, it updates the notification message
    */
   updateUserInterface: function(data){
      var message, err = true;
      if(data.substr(0,2) == '-1') message = data.substring(2,data.length);
      else{
         if(Main.ajaxParams.successMssg != undefined) message = Main.ajaxParams.successMssg;
         else message = 'The changes have been successfully saved.';
         err = false;
         if(Main.ajaxParams.div2Update != undefined) $('#'+Main.ajaxParams.div2Update).html(data);
      }
      
      if($('#notification_box') != undefined){
         Notification.show({create:false, hide:true, updateText:true, text:message, error:err});
      }
   },
    
   /**
	  * Closes the custom message box. It does nothing else
	  */
   closeMessageBox: function(sender, value){
      sender.close();
      if(!value) return;
   },

   /**
     * Gets the selected value from a radio button group!!
     */
   radioGroupValue: function(htmlId, htmlName){
       var valSearch = undefined, selValue;
       if(htmlId != undefined) valSearch = '#'+htmlId;
       else if(htmlName != undefined) valSearch = '[name='+htmlName+']';
       $.each($(valSearch), function(){
          if(this.checked){
             selValue = this.value;
          }
       });
       return selValue
    },
    
    /**
     * Given an array with the fields, it validates each value based on the regex supplied also
     */
   fieldsValidation: function(fields){
      var errors = new Array(), reg, curMssg, ignoreUndefs;
      $.each(fields, function(){
         curMssg = this.message;
         ignoreUndefs = this.ignoreUndefined;
         if(this.regex == undefined){
            if(this.value == undefined) errors[errors.length] = curMssg;
         }
         else{
            reg = this.regex;
            if($.isArray(this.value)){
               $.each(this.value, function(i, val){
                  if(reg.test(val) == false){
                     if(!(val == undefined && ignoreUndefs)) errors[errors.length] = curMssg;
                  }
               });
            }
            else{
               if(reg.test(this.value) == false) errors[errors.length] = curMssg;
            }
         }
      });
      return errors;
   },

   /**
    * gets a string as the variables passed in the location and returns a variable by the specific name
    */
   getVariable: function(name,queryStr){
      queryStr=unescape(queryStr)		//make it a proper string
      queryStr=queryStr.replace("+"," ").replace("+"," ")	//remove the +'s
       if (queryStr.length != 0) {
         splitArray = queryStr.split("&")	//convert it to an array
         for (i=0; i<splitArray.length; i++) {
         var splits=splitArray[i].split("=");
         if(splits[0]==name) return splits[1];
         }
       }
      return undefined;
   },
   
   /**
    * Creates an array that can be passed to generateCombo function so that a drop down can be created out of it
    * 
    * @since   v0.2
    */
   formatComboData: function(ids, names){
      var data = [];
      $.each(ids, function(i, value){
         data[data.length] = {id: value, name: names[i]};
      });
      return data;
   }
}

/**
 * A variable to show the current module
 */
var paged = Common.getVariable('page', document.location.search.substring(1));

/**
 * A variable to show the current sub module
 */
var sub_module = Common.getVariable('do', document.location.search.substring(1));

/**
 * Add a disable function as an extension to jquery.
 * 
 * Disables all elements
 */
$.fn.disable = function() {
    return this.each(function() {
        if (typeof this.disabled != "undefined") this.disabled = true;
    });
}

/**
 * The cryptonite to the .disable() function
 */
$.fn.enable = function() {
    return this.each(function() {
        if (typeof this.disabled != "undefined") this.disabled = false;
    });
}
