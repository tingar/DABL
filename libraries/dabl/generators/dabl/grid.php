<table class="object-grid <?php echo $single ?>-grid">
	<thead>
		<tr>
<?php
		foreach($columns as $key => $column){
			$column_name = $column->getName();
?>
			<th class="ui-state-default ui-th-column ui-th-ltr <?php if($key==0)echo 'ui-corner-tl' ?>"><?php echo $column_name ?></th>
<?php
		}
		$key = 1;
		foreach($actions as $action){
?>
			<th class="ui-state-default ui-th-column ui-th-ltr grid-action-column <?php if($key == count($actions))echo 'ui-corner-tr' ?>">&nbsp;</th>
<?php
			++$key;
		}
?>
		</tr>
	</thead>
	<tbody>
<?php echo '<?php foreach($'.$plural.' as $key => $'.$single.'): ?>' ?>

		<tr class="<?php echo '<?php echo' ?> ($key & 1) ? 'even ui-priority-secondary' : 'odd' <?php echo '?>' ?> ui-widget-content ui-row-ltr">
<?php
		foreach($columns as $column){
			$column_name = $column->getName();
			switch($column->getType()){
				case PropelTypes::TIMESTAMP:
					$format = 'VIEW_TIMESTAMP_FORMAT';
					break;
				case PropelTypes::DATE:
					$format = 'VIEW_DATE_FORMAT';
					break;
				default:
					$format = null;
					break;
			}
			$output = '<?php echo htmlentities($'.$single.'->'."get$column_name".'('.$format.')) ?>';
?>
			<td><?php echo $output ?>&nbsp;</td>
<?php
		}
		foreach($actions as $action_label => $action_url){
			if($action_label == 'Index') continue;
?>
			<td>
				<a<?php if(in_array($action_label, self::$standard_actions)) : ?> class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" title="<?php echo $action_label . " " . ucfirst($single) ?>"<?php endif ?> href="<?php echo $action_url ?>"<?php if(strtolower($action_label) == 'delete') echo ' onclick="return confirm(\'Are you sure?\');"' ?>>
					<?php if(array_key_exists($action_label, self::$action_icons)): ?><span class="ui-icon ui-icon-<?php echo self::$action_icons[$action_label] ?>"><?php endif ?><?php echo $action_label ?><?php if(array_key_exists($action_label, self::$action_icons)): ?></span><?php endif ?>

				</a>
			</td>
<?php
		}
?>
		</tr>
<?php echo '<?php endforeach ?>' ?>

	</tbody>
</table>