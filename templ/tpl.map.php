<?php include("tpl.header.php"); ?>
<script>
function si(ev, name, phone)
{
	document.getElementById('popup').style.display = 'block';
	document.getElementById('popup').style.left = (ev.pageX+10)  + "px";
	document.getElementById('popup').style.top = (ev.pageY+10)  + "px";
	document.getElementById('u_name').textContent = name;
	document.getElementById('u_phone').textContent = phone;
}

function mi(ev)
{
	var el = document.getElementById('popup');
	if(el)
	{
		el.style.left = (ev.pageX+10)  + "px";
		el.style.top = (ev.pageY+10)  + "px";
	}
}

function f_set_location(id, map, x, y)
{
	//alert("map: "+map+"    x: "+x+"    y: "+y);
	$.post("pb.php?action=setlocation&id="+id, {'map': map, 'x': x, 'y': y },
		function(data)
		{
			$.notify(data.message, "success");
		},
		'json'
	)
	.fail(
		function()
		{
			$.notify("Failed AJAX request", "error");
		}
	)
}

function sel(ev)
{
	ev.target.style.border="2px dashed black";
	var el = document.getElementById('map-image');
	el.onclick = function(id) {
		return function(event)
		{
			var box = document.getElementById('map-image').getBoundingClientRect()
			//alert('px: '+event.pageX+'  py: '+event.pageY+'   cx: '+(box.left)+'  py: '+(box.top));
			f_set_location(id, <?php eh($id);?>, event.pageX - box.left - window.scrollX, event.pageY - box.top - window.scrollY);
			document.getElementById('u'+id).style.border="0px dashed black";
			document.getElementById('u'+id).style.left = (event.pageX - box.left - window.scrollX - 16)+'px';
			document.getElementById('u'+id).style.top = (event.pageY - box.top - window.scrollY - 22)+'px';
			document.getElementById('map-image').onclick = null;
		}
	}(ev.target.dataset.id);
}

</script>
		<h3 align="center">Map <a href="?action=map&id=1">1</a>&nbsp;<a href="?action=map&id=2">2</a></h3>
		<div style="position: relative;">
				<img id="map-image" src="templ/map<?php eh($id);?>.png" style="left: 0px; top: 0px;"/>
		<?php $i = 0; if($db->data !== FALSE) foreach($db->data as $row) { $i++; ?>
				<img id="<?php eh('u'.$row[0]);?>" src="templ/marker-static.png" data-id="<?php eh($row[0]);?>" style="position: absolute; <?php eh('left: '.($row[13]-16).'px; top: '.($row[14]-22).'px');?>" onmouseenter="si(event, '<?php eh($row[2].' '.$row[3]); ?>', '<?php eh($row[7]); ?>')" onmouseleave="document.getElementById('popup').style.display='none'" onmousemove="mi(event);" onclick="sel(event);"/>
		<?php } ?>
		</div>
		<div id="popup" style="position: absolute; display: none; background: white; border: 1px solid black; border-radius: 5px; padding: 10px 10px;">
			<span id="u_name" style="font-weight: bold;"></span><br />
			tel.&nbsp;<span id="u_phone"></span>
		</div>
<?php include("tpl.footer.php"); ?>
