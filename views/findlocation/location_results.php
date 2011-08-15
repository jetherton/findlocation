<br/><h4><?php echo Kohana::lang("ui_main.search_results"); ?>:</h4>
<ul>
<?php foreach($places as $place) { ?>

	<li> 
		<a href="#" onclick="placeLocation(<?php echo $place['lat'].", ". $place['lon']. ", '". $place['name']."'"; ?>); return false;">
			<?php echo $place['name']; ?>
		</a>
	</li>

<?php } ?>
</ul>
