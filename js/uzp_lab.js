/**
 * The constructor of the Uzp functionality
 *
 * @returns {Uzp}
 */
function Uzp() {
   window.uzp_lab = this;

   // initialize the main variables
   window.uzp_lab.sub_module = Common.getVariable('do', document.location.search.substring(1));
   window.uzp_lab.module = Common.getVariable('page', document.location.search.substring(1));

   this.serverURL = "./modules/mod_uzp_lab_general.php";
   this.procFormOnServerURL = "mod_ajax.php";

   // create the notification place
   $("#notification_box").jqxNotification({ width: 250, position: "top-right", opacity: 0.9, autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info", showCloseButton: false });
   this.prevNotificationClass = 'info';
};

/**
 * Show a notification on the page
 *
 * @param   message     The message to be shown
 * @param   type        The type of message
 */
Uzp.prototype.showNotification = function(message, type){
   if(type === undefined) { type = 'error'; }

   $('#notification_box #msg').html(message);

   $('#notification_box').removeClass('jqx-notification-'+uzp.prevNotificationClass);
   $('#notification_box').addClass('jqx-notification-'+type);

   $('table td:first').removeClass('jqx-notification-icon-'+uzp.prevNotificationClass);
   $('table td:first').addClass('jqx-notification-icon-'+type);

   $('#notification_box').jqxNotification('open');
   uzp.prevNotificationClass = type;
};

Uzp.prototype.saveReceivedSample = function(){
   // get the sample format and the received sample
   var format = $('[name=sample_format]').val(), sample = $('[name=sample]').val();
   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
};

Uzp.prototype.receiveSampleKeypress = function(event){
   var keycode = (event.keyCode ? event.keyCode : event.which);
	if(keycode === 13){
		// an enter was pressed, lets save this particular sample
      uzp.saveReceivedSample();
	}
};