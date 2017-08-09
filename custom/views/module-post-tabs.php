<?php
$tabs = hocwp_theme_custom_post_tabs();
$tab  = get_query_var( 'tab' );
?>
<div id="tabs" class="post-tabs">
	<?php
	$count = 0;
	foreach ( $tabs as $key => $text ) {
		$class = 'tab-item';
		if ( ( ! array_key_exists( $tab, $tabs ) && 0 == $count ) || $tab == $key ) {
			$class .= ' active';
		}
		?>
		<a class="<?php echo $class; ?>" href="?tab=<?php echo $key; ?>" title=""
		   data-value="<?php echo $key; ?>"><?php echo $text; ?></a>
		<?php
		$count ++;
	}
	?>
</div>