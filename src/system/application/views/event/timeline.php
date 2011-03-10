<?php
menu_pagetitle($title);
?>

<h1 class="icon-event">
	<?php if(user_is_admin()){ ?>
	<span style="float:left">
	<?php } ?>
	<?php echo $title; ?><?php echo $subtitle; ?>
	<?php if(user_is_admin()){ ?>
	</span>
	<?php } ?>
	<?php if(user_is_admin()){ ?>
	<a class="btn" style="float:right" href="/event/add">Add new event</a>
	<div class="clear"></div>
    <?php } ?>
</h1>


<script type="text/javascript">
   $(function() {
       var eventSource = new Timeline.DefaultEventSource();
       var mostRecentEventDate = new Date(1);

       var fixed_colors = [ "#af0000","#00af00", "#0000af", "#af00af", "#00afaf", "#afaf00" ];
       var fixed_color_ptr = 0;
       var event_colors = [];

       var json_data = <?php echo $events; ?>;

       $.each(json_data, function(i, item) {
           if (typeof event_colors[item.ID] == 'undefined') {
               event_colors[item.ID] = fixed_colors[fixed_color_ptr];
               fixed_color_ptr++;
               if (fixed_color_ptr > fixed_colors.length) fixed_color_ptr = 0;
           }
           var dateEvent = new Date(item.event_start*1000-16200000);
           mostRecentEventDate = (dateEvent > mostRecentEventDate) ? dateEvent : mostRecentEventDate;
           var evt = new Timeline.DefaultEventSource.Event(
               item.ID,
               new Date(dateEvent), //start
               new Date(item.event_end*1000-16200000), //end
               null,
               null,
               false, //instant
               item.event_name, //text
               item.event_desc, //description
               "http://joind.in/inc/img/event_icons/"+item.event_icon,
               null,
               null,
               event_colors[item.ID],
               null);
           eventSource.add(evt);
       });

       var bandInfos = [
           Timeline.createBandInfo({
               layout : 'overview',
               trackHeight: 2.5,
               trackGap: 0.2,
               width: "20%",
               intervalUnit: Timeline.DateTime.MONTH,
               intervalPixels: 300,
               eventSource: eventSource,
               timeZone: new Date().getTimezoneOffset() / 60,
               date: new Date().toGMTString()
           }),
           Timeline.createBandInfo({
               showEventText: false,
               trackHeight: 2.5,
               trackGap: 0.2,
               width: "80%",
               intervalUnit: Timeline.DateTime.WEEK,
               intervalPixels: 300,
               eventSource: eventSource,
               timeZone: new Date().getTimezoneOffset() / 60,
               date: new Date().toGMTString()
           }),
       ];

       bandInfos[0].syncWith = 1;
       bandInfos[0].highlight = false;
       timeLine = Timeline.create($("#ji-timeline")[0], bandInfos);
   });

   var resizeTimerID = null;
   $('body').resize(function() {
      if (resizeTimerID == null) {
         resizeTimerID = window.setTimeout(function() {
            resizeTimerID = null;
            timeLine.layout();
         }, 500);
      }
   });

   </script>

<div id="ji-timeline" style="height: 300px;"></div>
    
<?php
//foreach($events as $k=>$v){
//	$this->load->view('event/_event-row', array('event'=>$v));
//}
?>

<p>
	Know of an event happening this month? <a href="/event/submit">Let us know!</a>
	We love to get the word out about events the community would be interested in and
	you can help us spread the word!
</p>
<p>
	<a href="/event/submit/" class="btn-big">Submit your event!</a>
</p>
