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
      else if(uzp_lab.module === 'step3') uzp.saveMcConkyPlate();
      else if(uzp_lab.module === 'step4') uzp.saveColonies();
      else if(uzp_lab.module === 'step5.1') uzp.saveColoniesPositions();
	}
};

/**
 * Given a sample barcode, automatically generate the regex that will be used to validate the expected samples
 *
 * @param   {string}    sampleBarcode
 * @returns {RegExp}    Returns the created regex
 */
Uzp.prototype.createSampleRegex = function(sampleBarcode){
   var prefix = sampleBarcode.match(/^([a-z]+)/i);
   var suffix = sampleBarcode.match(/([0-9]+)$/i);
   var regex_f = '^'+prefix[0]+'[0-9]{'+suffix[0].length+'}$';
   var newRegex = new RegExp(regex_f, 'i');

   return newRegex;
};

Uzp.prototype.validateScannedSamples = function(settings){
   // check which kind of sample combinations we have here
   if(uzp.prevSampleType === undefined || uzp.curSampleType === undefined){
      uzp.showNotification('One sample type is not defined, so get the next sample... Current association of: ' +uzp.prevSample+ ' & '+uzp.curSample, 'mail');
      $("[name=sample]").focus().val('');
      return 1;  // one of the sample type is not defined... so lets return
   }
   else if(uzp.prevSampleType === settings.secondSample && uzp.curSampleType === settings.secondSample){
      // discard this and start afresh
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      $("[name=sample]").focus().val('');
      return;
   }
   else if(uzp.prevSampleType === settings.firstSample && uzp.curSampleType === settings.secondSample){
      // save this association
      uzp.showNotification('Saving this association of parent ==> ' +uzp.prevSample+ ' and broth ==> '+uzp.curSample, 'success');
      return 0;
   }
   else{
      // we have some other permutation.. so keep going
      uzp.showNotification('Got the current association of parent ==> ' +uzp.prevSample+ ' and broth ==> '+uzp.curSample+', so continue scanning.', 'mail');
      $("[name=sample]").focus().val('');
      return 1;
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

   //lets validate the aliquot format
   var s_regex = uzp.createSampleRegex(sample_format);
   var b_regex = uzp.createSampleRegex(broth_format);

   // check whether we are dealing with the field or broth sample
   if(s_regex.test(sample) === true){ curSampleType = 'field_sample'; }          // we have a field sample
   else if(b_regex.test(sample) === true){ curSampleType = 'broth_sample'; }     // we have a broth sample
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

   var res = uzp.validateScannedSamples({firstSample: 'field_sample', secondSample: 'broth_sample'});
   if(res === 1){ return; }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step2&do=save", async: false, dataType:'json', data: {field_sample: uzp.prevSample, broth_sample: uzp.curSample, cur_user: cur_user},
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

Uzp.prototype.saveMcConkyPlate = function(){
   // check for the pre-requisites
   var plate_format = $('[name=plate_format]').val(), broth_format = $('[name=broth_format]').val();
   var sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined, media_used = $('#mediaId').val();

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(broth_format === '' || broth_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the broth. It should be something like \'BSR010959\'', 'error');
      $("[name=broth_format]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the plate barcode. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=plate_format]").focus();
      return;
   }
   if(media_used === '0'){
      uzp.showNotification('Please select the media being used.', 'error');
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   //lets validate the samples
   var p_regex = uzp.createSampleRegex(plate_format);
   var b_regex = uzp.createSampleRegex(broth_format);

   // check whether we are dealing with the field or broth sample
   if(p_regex.test(sample) === true){ curSampleType = 'plate'; }                  // we have a mcconky plate
   else if(b_regex.test(sample) === true){ curSampleType = 'broth_sample'; }     // we have a broth sample
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

   var res = uzp.validateScannedSamples({firstSample: 'broth_sample', secondSample: 'plate'});
   if(res === 1){ return; }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step3&do=save", async: false, dataType:'json', data: {broth_sample: uzp.prevSample, plate_barcode: uzp.curSample, cur_user: cur_user, media_used: media_used},
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

/**
 * Saves the current association of plates and colonies
 * @returns {undefined}    Returns nothing
 */
Uzp.prototype.saveColonies = function(){
   // check for the pre-requisites
   var colonies_format = $('[name=colonies_format]').val(), plate_format = $('[name=plate_format]').val();
   var sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the broth. It should be something like \'BSR010959\'', 'error');
      $("[name=broth_format]").focus();
      return;
   }
   if(colonies_format === '' || colonies_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the plate barcode. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=plate_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      $("[name=sample]").focus().val('');
      return;
   }

   //lets validate the samples
   var p_regex = uzp.createSampleRegex(plate_format);
   var c_regex = uzp.createSampleRegex(colonies_format);

   // check whether we are dealing with the field or broth sample
   if(p_regex.test(sample) === true){
      if(uzp.parentSample !== undefined && uzp.colonies.length !== 0){
         // lets save this association
         $.ajax({
            type:"POST", url: "mod_ajax.php?page=step4&do=save", async: false, dataType:'json', data: {plate: uzp.parentSample, colonies: uzp.colonies, cur_user: cur_user},
            success: function (data) {
               if(data.error === true){
                  uzp.showNotification(data.mssg, 'error');
                  $("[name=sample]").focus().val('');
                  $('#label_scanned_colonies').html('Scanned colonies');
                  uzp.parentSample = undefined; uzp.curSample = undefined; uzp.colonies = [];
                  $('#scanned_colonies').html('');
                  return;
               }
               else{
                  // we have saved the sample well...
                  $("[name=sample]").focus().val('');
                  var currentdate = new Date();
                  var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
                  $.each(uzp.colonies, function(i, that){
                     $('.received .saved').prepend(datetime +': '+ uzp.parentSample +'=>'+ that +"<br />");
                  });

                  // lets prepare for the next sample
                  uzp.parentSample = sample; uzp.curSample = undefined; uzp.colonies = [];
                  $('#scanned_colonies').html('');
                  $('#label_scanned_colonies').html('Scanned colonies for <b>'+ sample +'</b>');
                  return;
               }
           }
        });
      }
      else{
         uzp.parentSample = sample;
         $('#label_scanned_colonies').html('Scanned colonies for <b>'+ sample +'</b>');
         // we have a parent plate
         uzp.showNotification('Got a plate with barcode... ' +uzp.parentSample+ ' scan the colonies.', 'mail');
         $("[name=sample]").focus().val('');
         uzp.colonies = [];
         return;
      }
   }
   else if(c_regex.test(sample) === true){
      if(uzp.parentSample !== undefined){
         // add the sample to the list of colonies and to the div
         uzp.colonies[uzp.colonies.length] = sample;
         var currentdate = new Date();
         var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
         $('#scanned_colonies').prepend(datetime +': '+ sample +"<br />");

         // we have a colony
         uzp.showNotification('Colony with barcode... '+ sample +'....', 'mail');
         $("[name=sample]").focus().val('');
         return;
      }
      else{
         uzp.showNotification('You can\'t scan a colony without scanning the plate first. Current barcode'+ sample +'!', 'error');
         return;
      }
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.parentSample = undefined; uzp.curSample = undefined; uzp.colonies = [];
      $('#scanned_colonies').html('');
      return;
   }
};

Uzp.prototype.saveColoniesPositions = function(){
   // get the sample format and the received sample
   var colonies_format = $('[name=colonies_format]').val(), storage_box = $('[name=storage_box]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), cur_pos = $('[name=colony_pos]').val();

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(colonies_format === '' || colonies_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the colonies. It should be something like \'BSR010959\'', 'error');
      $("[name=colonies_format]").focus();
      return;
   }
   if(storage_box === '' || storage_box === undefined){
      uzp.showNotification('Please scan the barcode for the storage boxes. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=storage_box]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }
   if(cur_pos === ''){
      uzp.showNotification('Please enter the current position of the colony.', 'error');
      return;
   }

   //lets validate the aliquot format
   var c_regex = uzp.createSampleRegex(colonies_format);
   var b_regex = uzp.createSampleRegex(storage_box);

   // check whether we are dealing with the field or broth sample
   if(c_regex.test(sample) === true){
      // save the colony to the next slot of this box
      $.ajax({
         type:"POST", url: "mod_ajax.php?page=step5.1&do=save", async: false, dataType:'json', data: {colony: sample, box: storage_box, cur_user: cur_user, cur_pos: cur_pos},
         success: function (data) {
            if(data.error === true){
               uzp.showNotification(data.mssg, 'error');
               $("[name=sample]").focus().val('');
               return;
            }
            else{
               // we have saved the sample well... lets prepare for the next sample
               $("[name=sample]").focus().val('');
               var suffix = sample.match(/([0-9]+)$/i);
               $('#plate_layout .pos_'+cur_pos).html(suffix[0] +' ('+ cur_pos +')').css({'background-color': '#009D59'});
               $('[name=colony_pos]').val(parseInt(cur_pos)+1);
               uzp.showNotification(data.mssg, 'success');
            }
         }
      });
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      return;
   }
};