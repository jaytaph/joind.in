<script type="text/javascript">
   $(function() {
       var eventSource = new Timeline.DefaultEventSource();
       var eventSource2 = new Timeline.DefaultEventSource();
       var mostRecentEventDate = new Date(999999999999999);

       var json_data = <?php echo json_encode($talks); ?>;

       $.each(json_data, function(i, item) {
           var dateEventStart = new Date(item.date_given*1000-16200000);
           var dateEventEnd = new Date(item.date_given*1000-16200000+600000);
           mostRecentEventDate = (dateEventStart < mostRecentEventDate) ? dateEventStart : mostRecentEventDate;
           var evt = new Timeline.DefaultEventSource.Event(
               item.ID,
               dateEventStart, //start
               null,
               null,
               null,
               true, //instant
               item.talk_title, //text
               item.talk_desc  //description
           );
           eventSource.add(evt);

           var evt = new Timeline.DefaultEventSource.Event(
               item.ID,
               dateEventStart, //start
               dateEventEnd,
               null,
               null,
               false, //instant
               item.talk_title, //text
               item.talk_desc  //description
           );
           eventSource2.add(evt);
       });

       var bandInfos = [
           Timeline.createBandInfo({
               layout : 'overview',
               trackHeight: 2.5,
               trackGap: 0.2,
               width: "20%",
               intervalUnit: Timeline.DateTime.DAY,
               intervalPixels: 400,
               eventSource: eventSource2,
               timeZone: new Date().getTimezoneOffset() / 60,
               date: mostRecentEventDate.toGMTString()
           }),
           Timeline.createBandInfo({
               trackHeight: 2.5,
               trackGap: 0.2,
               width: "80%",
               intervalUnit: Timeline.DateTime.HOUR,
               intervalPixels: 300,
               eventSource: eventSource,
               timeZone: new Date().getTimezoneOffset() / 60,
               date: mostRecentEventDate.toGMTString()
           }),
       ];

       bandInfos[0].syncWith = 1;
       bandInfos[0].highlight = true;
       timeLine = Timeline.create($("#ji-timeline")[0], bandInfos);
   });
</script>

<div id="timeline">
    <div id="ji-timeline" style="height: 300px;"></div>
</div>