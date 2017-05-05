<?php include("tpl.header.php"); ?>
<script>
function si(ev)
{
	document.getElementById('popup').style.display = 'block';
	document.getElementById('popup').style.left = (ev.pageX+10)  + "px";
	document.getElementById('popup').style.top = (ev.pageY+10)  + "px";
	if(parseInt(ev.target.dataset.photo, 10))
	{
		document.getElementById('u_photo').src = 'photos/t'+ev.target.dataset.id+'.jpg';
	}
	else
	{
		document.getElementById('u_photo').src = 'templ/nophoto.png';
	}
	document.getElementById('u_name').textContent = ev.target.dataset.name;
	document.getElementById('u_position').textContent = ev.target.dataset.position;
	document.getElementById('u_phone').textContent = ev.target.dataset.phone;
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
			$.notify(data.message, data.code?"error":"success");
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

/* old function click-click-move
function f_click(ev)
{
	ev.target.style.border="1px dashed red";
	ev.target.style.borderRadius = "5px";
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
*/

function f_drag(ev)
{
	ev.target.style.border="1px dashed red";
	ev.target.style.borderRadius = "5px";

	var box = document.getElementById('map-image').getBoundingClientRect();
	var sx = (window.pageXOffset !== undefined)? window.pageXOffset: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
	var sy = (window.pageYOffset !== undefined)? window.pageYOffset: (document.documentElement || document.body.parentNode || document.body).scrollTop;
	ev.target.style.left = Math.round(ev.pageX - box.left - sx - 16)+'px';
	ev.target.style.top = Math.round(ev.pageY - box.top - sy - 22)+'px';

	document.onmousemove = function(id)
	{
		return function(ev)
		{
			var box = document.getElementById('map-image').getBoundingClientRect();
			var sx = (window.pageXOffset !== undefined)? window.pageXOffset: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
			var sy = (window.pageYOffset !== undefined)? window.pageYOffset: (document.documentElement || document.body.parentNode || document.body).scrollTop;
			var x = Math.round(ev.pageX - box.left - sx);
			var y = Math.round(ev.pageY - box.top - sy);
			if(x < 0) x = 0;
			if(y < 0) y = 0;
			if(x > box.right - box.left) x = box.right - box.left;
			if(y > box.bottom - box.top) y = box.bottom - box.top;
			//console.log("onmousemove "+id+"    sx "+sy+"     sY "+window.scrollY);
			document.getElementById('u'+id).style.left = (x - 16)+'px';
			document.getElementById('u'+id).style.top = (y - 22)+'px';
		}
	}(ev.target.dataset.id);

	ev.target.onmouseup = function(ev) { f_drop(ev) };
}

function f_drop(ev)
{
	document.onmousemove = null;
	var box = document.getElementById('map-image').getBoundingClientRect();
	//alert('px: '+ev.pageX+'  py: '+ev.pageY+'   cx: '+(box.left)+'  py: '+(box.top));
	var sx = (window.pageXOffset !== undefined)? window.pageXOffset: (document.documentElement || document.body.parentNode || document.body).scrollLeft;
	var sy = (window.pageYOffset !== undefined)? window.pageYOffset: (document.documentElement || document.body.parentNode || document.body).scrollTop;
	var x = Math.round(ev.pageX - box.left - sx);
	var y = Math.round(ev.pageY - box.top - sy);
	if(x < 0) x = 0;
	if(y < 0) y = 0;
	if(x > box.right - box.left) x = box.right - box.left;
	if(y > box.bottom - box.top) y = box.bottom - box.top;
	ev.target.style.left = (x - 16)+'px';
	ev.target.style.top = (y - 22)+'px';
	f_set_location(ev.target.dataset.id, <?php eh($id);?>, x, y);
	ev.target.style.border="0px dashed black";
	ev.target.onmouseup = null;
}

</script>
		<h3 align="center">Map<?php for($i = 1; $i <= PB_MAPS_COUNT; $i++) { ?>&nbsp;<a href="?action=map&amp;id=<?php eh($i);?>"><?php eh($i);?></a><?php } ?></h3>
		<div style="position: relative;">
				<img id="map-image" src="templ/map<?php eh($id);?>.png" style="left: 0px; top: 0px;"/>
		<?php $i = 0; foreach($db->data as $row) { $i++; ?>
				<img id="<?php eh('u'.$row[0]);?>" src="templ/marker-static.png" data-id=<?php eh($row[0]);?> data-name="<?php eh($row[2].' '.$row[3]); ?>" data-position="<?php eh($row[6]); ?>" data-phone="<?php eh($row[7]); ?>" data-photo=<?php eh($row[10]); ?> style="position: absolute; <?php eh('left: '.($row[12]-16).'px; top: '.($row[13]-22).'px');?>" onmouseenter="si(event)" onmouseleave="document.getElementById('popup').style.display='none'" onmousemove="mi(event);" onmousedown="f_drag(event);" ondragstart="return false;"/>
		<?php } ?>
		</div>
		<div id="popup" class="tooltip-user" style="display: none;">
			<img id="u_photo"/>
			<span id="u_name" class="boldtext"></span><br />
			<span id="u_position"></span><br />
			tel.&nbsp;<span id="u_phone"></span>
		</div>
<?php include("tpl.footer.php"); ?>
