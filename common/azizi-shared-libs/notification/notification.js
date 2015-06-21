/* 
 * Manages all that appertains to notification messages displayed on the top right of the page in regards to various happenings in the system
 * This will be notoriously be used in AJAX calls.
 *
 * @category   Common
 * @package    Notofications
 * @author     Kihara Absolomon <soloincc@movert.co.ke>
 * @version		0.1
 */

var Notification = {
	show: function(settings){
		var mss;
		if(settings.create == true){
			if($('#notification_box').length != 0) Notification.hide();
			if(Main == undefined || Main.domObjects == undefined || Main.domObjects['notification_box'] == undefined){
				if(Main==undefined){
					Main = {};
					Main.domObjects = {};
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
		if($('#notification_box').length == 0) return;
		if(settings.updateText==true) $('#notification_box').html('<span>'+settings.text+'</span>');
		if(settings.error==true) $('#notification_box')[0].firstChild.style.backgroundColor='#FF5F5F';
		else $('#notification_box')[0].firstChild.style.backgroundColor='#CCFF99';
		if(settings.hide==true && settings.error==false) setTimeout(Notification.hide, 3000);	//always call this function last
		else if(settings.hide==true && settings.error==true) setTimeout(Notification.hide, 10000);
		else if(settings.hide=='now') setTimeout(Notification.hide, 100);
	},
	
	hide: function(){
		if($('#notification_box').length == 0) return;
		var temp = $('#notification_box')[0];
		if(temp.parentNode===undefined) return;
		temp=temp.parentNode.removeChild(temp);
		if(Main.domObjects==undefined) Main.domObjects=[];
		Main.domObjects['notification_box']=temp;
	},
   
   serverCommunicationError: function(){
      Notification.show({create:true, hide:true, updateText:false, text:'There was an error while communicating with the server', error:true});
   }
}