<?php
  // Compatible with sf_escaping_strategy: true
  $download = isset($download) ? $sf_data->getRaw('download') : null;
  $item = isset($item) ? $sf_data->getRaw('item') : null;
  $options = isset($options) ? $sf_data->getRaw('options') : null;
  $uniqueID = isset($uniqueID) ? $sf_data->getRaw('uniqueID') : null;
  $width = isset($width) ? $sf_data->getRaw('width') : null;
?>
<?php extract($options) ?>

<?php use_helper('a') ?>

<div class="a-ui a-audio-player-container" id="a-audio-player-container-<?php echo $uniqueID ?>">

	<div class="a-audio-player-interface a-loading">
		<div class="a-audio-loading">Loading Audio Player...</div>
		<div class="a-audio-controls" id="icons-<?php echo $uniqueID ?>">
			<a href="#" class="a-audio-play a-audio-button" id="a-audio-play-<?php echo $uniqueID ?>" onclick="return false;">Play</a>
			<a href="#" class="a-audio-pause a-audio-button" id="a-audio-pause-<?php echo $uniqueID ?>"  onclick="return false;">Pause</a>
		</div>
		<div class="a-audio-slider-wrapper playhead">			
			<div class="a-audio-loader"></div>
			<div class="a-audio-playback a-audio-slider" id="a-audio-playback-<?php echo $uniqueID ?>">
				<a href="#" class="a-audio-slider-handle ui-slider-handle">Playback</a>
			</div>
			<div class="a-audio-time"></div>
		</div>
		<div class="a-audio-slider-wrapper volume">
			<div class="a-audio-volume a-audio-slider" id="a-audio-volume-<?php echo $uniqueID ?>">
				<a href="#" class="a-audio-slider-handle ui-slider-handle">Volume</a>
			</div>			
		</div>
	</div>

	<div id="a-audio-player-<?php echo $uniqueID ?>" class="a-audio-player"></div>

	<?php if ($download): ?>
		<div class="a-audio-download"><?php echo a_link_to(__("Download Audio File", null, 'apostrophe'), 'aMediaBackend', 'original',  array('query_string' => array("slug" => $item->getSlug(), "format" => $item->getFormat()), 'class' => 'a-download')) ?></div>
	<?php endif ?>

</div>
	
<?php a_js_call('apostrophe.audioPlayerSetup(?, ?)', "#a-audio-player-container-$uniqueID", a_url('aMediaBackend', 'original', array('slug' => $item->getSlug(), 'format' => $item->getFormat()))) ?>
