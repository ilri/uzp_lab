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
   var format = $('[name=sample_format]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val();
   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(format === '' || format === undefined){
      uzp.showNotification('Please a sample of the field sample to expect. Expecting a barcode like AVAQ63847', 'error');
      $("[name=sample_format]").focus();
      return;
   }
   if(uzp.fieldSampleRegex === undefined){
      //lets create the sample regex format
      var prefix = format.match(/^([a-z]+)/i);
      var suffix = format.match(/([0-9]+)$/i);
      uzp.fieldSampleRegex = '^'+prefix[0]+'[0-9]{'+suffix[0].length+'}$';
   }
   var regex = new RegExp(uzp.fieldSampleRegex, 'i');

   // lets ensure that our sample is in the right format
   if(regex.test(sample) === false){
      uzp.showNotification('Error! Unknown sample type!', 'error');
      $('[name=sample]').focus().val('');
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step1&do=save", async: false, dataType:'json', data: {format: format, sample: sample, cur_user: cur_user},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus('').focus();
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus();
            $("[name=sample]").val('');
            var currentdate = new Date();
            var datetime = " @ " + currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('#receive_samples .saved').prepend(sample +' - '+ datetime+ "<br />");
         }
     }
  });
};

Uzp.prototype.receiveSampleKeypress = function(event){
   var keycode = (event.keyCode ? event.keyCode : event.which);
	if(keycode === 13){
		// an enter was pressed, lets save this particular sample
      if(uzp_lab.module === 'step1') uzp.saveReceivedSample();
      else if(uzp_lab.module === 'step2') uzp.saveBrothSample();
	}
};

Uzp.prototype.saveBrothSample = function(){
   // get the sample format and the received sample
   var sample_format = $('[name=sample_format]').val(), broth_format = $('[name=broth_format]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(broth_format === '' || broth_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the broth. It should be something like \'BSR010959\'', 'error');
      $("[name=sample_format]").focus();
      return;
   }
   if(sample_format === '' || sample_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the field sample. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=sample_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   // create the regex that will be used for validating the samples
   var prefix = sample_format.match(/^([a-z]+)/i);
   var suffix = sample_format.match(/([0-9]+)$/i);
   var s_regex_f = '^'+prefix[0]+'[0-9]{'+suffix[0].length+'}$';
   prefix = broth_format.match(/^([a-z]+)/i);
   suffix = broth_format.match(/([0-9]+)$/i);
   var b_regex_f = '^'+prefix[0]+'[0-9]{'+suffix[0].length+'}$';

   //lets validate the aliquot format
   var s_regex = new RegExp(s_regex_f, 'i');
   var b_regex = new RegExp(b_regex_f, 'i');

   // check whether we are dealing with the field or broth sample
   if(s_regex.test(sample) === true){
      // we have a field sample
      curSampleType = 'field_sample';
   }
   else if(b_regex.test(sample) === true){
      // we have a broth sample
      curSampleType = 'broth_sample';
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      return;
   }

   uzp.prevSampleType = uzp.curSampleType;
   uzp.curSampleType = curSampleType;
   uzp.prevSample = uzp.curSample;  // move the previous current sample to the previous sample
   uzp.curSample = sample;

   // ok, now lets see what we have here
   if(uzp.prevSampleType === undefined || uzp.curSampleType === undefined){
      uzp.showNotification('One sample type is not defined, so get the next sample... Current association of: ' +uzp.prevSample+ ' & '+uzp.curSample, 'mail');
      $("[name=sample]").focus().val('');
      return;  // one of the sample type is not defined... so lets return
   }
   else if(uzp.prevSampleType === 'broth_sample' && uzp.curSampleType === 'broth_sample'){
      // discard this and start afresh
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      $("[name=sample]").focus().val('');
      return;
   }
   else if(uzp.prevSampleType === 'field_sample' && uzp.curSampleType === 'broth_sample'){
      // save this association
      uzp.showNotification('Saving this association of parent ==> ' +uzp.prevSample+ ' and broth ==> '+uzp.curSample, 'success');
   }
   else{
      // we have some other permutation.. so keep going
      uzp.showNotification('Got the current association of parent ==> ' +uzp.prevSample+ ' and broth ==> '+uzp.curSample+', so continue scanning.', 'mail');
      $("[name=sample]").focus().val('');
      return;
   }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step2&do=save", async: false, dataType:'json', data: {broth_format: b_regex_f, sample_format: s_regex_f, field_sample: uzp.prevSample, broth_sample: uzp.curSample, cur_user: cur_user},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus().val('');
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ uzp.prevSample +'=>'+uzp.curSample +"<br />");
         }
     }
  });
};