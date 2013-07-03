
<h1> Find Location - Settings</h1>
<?php print form::open(); ?>

	<?php if ($form_error) { ?>
	<!-- red-box -->
		<div class="red-box">
			<h3><?php echo Kohana::lang('ui_main.error');?></h3>
			<ul>
				<?php
				foreach ($errors as $error_item => $error_description)
				{
				// print "<li>" . $error_description . "</li>";
				print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
				}
				?>
			</ul>
			</div>
	<?php } ?>


	<?php  if ($form_saved) {?>
		<!-- green-box -->
		<div class="green-box">
		<h3><?php echo Kohana::lang('ui_main.configuration_saved');?></h3>
		</div>
	<?php } ?>
	
	<?php  if ($cache_cleared) {?>
		<!-- green-box -->
		<div class="green-box">
		<h3>Cache Cleared</h3>
		</div>
	<?php } ?>
	

<div class="report-form">
	<div class="row">
		
		<h4>Country code of the country to bound searches to?
			<span><br/>For example United States = 'US'. For a complete list of country codes see
				<a href="http://en.wikipedia.org/wiki/CcTLD">here</a>
				. Leave blank to search all countries.
			</span>
		</h4>
		<?php print form::input('region_code', $form['region_code'], ' class="text"'); ?>
	</div>
	<br/>
	<div class="row">
		<h4>What string do you want appended to the end of Google geocoder searches to help increase accuracy?
			<span><br/>For example, ", Mexico" to ensure that every search is seen by google as "something, mexico."</span>
		</h4>
		<?php print form::input('append_to_google', $form['append_to_google'], ' class="text"'); ?>
		
	</div>
	<br/>
	<div class="row">
		<h4>GeoNames Username
			<span><br/>The username this site uses to query GeoNames. You can sign up for a GeoNames username
				<a href="http://www.geonames.org/login">here</a>
			</span>
		</h4>
		<?php print form::input('geonames_username', $form['geonames_username'], ' class="text"'); ?>
	</div>
	<br/>
	<!-- HT: Added code for fuzzy search enable/disable -->
	<div class="row">
		<h4>Fuzzy Search
			<span><br/>Enabling fuzzy search will make result filter by similar GeoNames (not require to put adjact GeoName).</span>
		</h4>
		<?php print form::checkbox('fuzzy', 'fuzzy', $form['fuzzy']); ?>
	</div>
	<br/>
	<!-- HT: End of code for fuzzy search enable/disable -->
	<div class="row">
		<h4>Bounding box
			<span><br/>Use this to set the geographic area that search results should come from. Put 0 for all the values if you want
				the searching algorithms to search the whole planent. Specify the North West and South East corners of the bounding box.
				Note that the bounding box will loose it's square shape as you approach the poles.
			</span>
		</h4>
		North West Latitude: <?php print form::input('n_w_lat', $form['n_w_lat'], ' class="text" style="float:none;" '); ?>
		North West Longitude: <?php print form::input('n_w_lon', $form['n_w_lon'], ' class="text" style="float:none;" '); ?>
	</div>
	<div class="row">
		South East Latitude: <?php print form::input('s_e_lat', $form['s_e_lat'], ' class="text" style="float:none;" '); ?>
		South East Longitude: <?php print form::input('s_e_lon', $form['s_e_lon'], ' class="text" style="float:none;" '); ?>
	</div>
	
	<br/>
	<div class="row">
		<h4>Clear Cache
			<span><br/>This deletes all cached location entries on this site. Clearing the cache can decrease performance and increase accuracy if locations are likely to have changed recently.
			</span>
		</h4>
		<?php print form::checkbox("clearcache", "clearcache"); ?>
	</div>
	
	
	
	
</div>
<br/>

<input type="image" src="<?php echo url::base() ?>media/img/admin/btn-save-settings.gif" class="save-rep-btn" style="margin-left: 0px;" />

<?php print form::close(); ?>

