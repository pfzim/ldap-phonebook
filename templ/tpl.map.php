<?php include("tpl.header.php"); ?>
<script>

function si(ev, img)
{
	if(img)
	{
		var el = document.getElementById('userphoto');
		el.src = img;
		el = document.getElementById('imgblock');
		imgblock.style.display = 'block';
		imgblock.style.left = (ev.clientX+10)  + "px";
		imgblock.style.top = (ev.clientY+10)  + "px";
	}
}

function mi(ev)
{
	var el = document.getElementById('imgblock');
	if(el)
	{
		el.style.left = (ev.clientX+10)  + "px";
		el.style.top = (ev.clientY+10)  + "px";
	}
}

function sm(id, x, y)
{
	var el = document.getElementById('map-container');
	el.style.display = 'block';
	el.onclick = function() {document.getElementById('map-container').style.display = 'none';};
	var map = document.getElementById('map-image');
	map.onload = function()
	{
		var el = document.getElementById('map-marker');
		if(el)
		{
			el.onclick = null;
			el.style.display = 'block';
			el.style.left = (this.offsetLeft + x - el.width/2)  + "px";
			el.style.top = (this.offsetTop + y - el.height/2)  + "px";
		}
	};
	map.src = 'templ/map' + id + '.png';
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

function hide(img)
{
	var el = document.getElementById('imgblock');
	imgblock.style.display = 'none';
}

</script>
		<h3 align="center">Map</h3>
		<div id="map-container" class="map-container2">
				<img id="map-image" class="map-image2" src="templ/map<?php eh($id);?>.png"/>
		<?php $i = 0; if($db->data !== FALSE) foreach($db->data as $row) { $i++; ?>
				<img id="map-marker" class="map-marker2" src="templ/marker.gif" data-id="position: relative; <?php eh($row[0]);?>" style="<?php eh('left: '.$row[13].'; top: '.$row[14]);?>" title="<?php eh($row[2].' '.$row[3]); ?>"/>
		<?php } ?>
		</div>

		<script>
			var i;
			var tags;
			tags = document.getElementsByClassName('cmd_hide');
			for(i = 0; i < tags.length; i++)
			{
				tags[i].onclick = function()
				{
					var id = this.parentNode.parentNode.dataset.id;
					this.textContent = 'Show';
					$.get("pb.php", {'action': 'hide', 'id': id },
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
				};
			}
			
			tags = document.getElementsByClassName('cmd_show');
			for(i = 0; i < tags.length; i++)
			{
				tags[i].onclick = function()
				{
					var id = this.parentNode.parentNode.dataset.id;
					this.textContent = 'Hide';
					$.get("pb.php", {'action': 'show', 'id': id },
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
				};
			}

			for(i = 1; i <= 5; i++)
			{
				var j;
				tags = document.getElementsByClassName('cmd_loc_'+i);
				for(j = 0; j < tags.length; j++)
				{
					tags[j].onclick = function(i)
					{
						return function()
						{
							var id = this.parentNode.parentNode.dataset.id;
							document.getElementById('map-container').onclick = null;
							document.getElementById('map-image').onload = null;
							document.getElementById('map-image').src = 'templ/map'+i+'.png';
							document.getElementById('map-container').style.display='block';
							document.getElementById('map-marker').style.display='none';
							document.getElementById('map-image').onclick = function(event)
							{
								document.getElementById('map-marker').style.display='block';
								document.getElementById('map-marker').style.left = (event.clientX - document.getElementById('map-marker').width/2)  + "px";
								document.getElementById('map-marker').style.top = (event.clientY - document.getElementById('map-marker').height/2)  + "px";
								document.getElementById('map-marker').onclick = function()
								{
									f_set_location(id, i, event.pageX - document.getElementById('map-image').offsetLeft, event.pageY - document.getElementById('map-image').offsetTop);
									document.getElementById('map-container').style.display='none';
									document.getElementById('map-image').onclick = null;
								};
							};
						};
					} (i);
				}
			}
		</script>
<?php include("tpl.footer.php"); ?>
