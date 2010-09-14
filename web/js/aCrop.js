aCrop = {
  api: null,
  
  // these are the params we expect
  options: {
    ids: [],
    aspectRatio: 0,
    imageInfo: {}
  },
  
  el: {
    previewList: '#a-media-selection-preview',
    previewImages: '#a-media-selection-preview img',
    slideshowList: '#a-media-selection-list',    
    slideshowImages: '#a-media-selection-list img',
  },
   
  init: function(options){
    aCrop.setOptions(options);
    
    if (!aCrop.api) { // don't do this stuff after each ajax crop
      $(aCrop.el.previewList).find('li').eq(0).addClass('current');    
      aCrop.startCrop();
    }
    
    $(aCrop.el.slideshowList).find('li .a-crop').click(aCrop.thumbnailClickHandler);
  },
  
  setOptions: function(options){
    $.extend(aCrop.options, options);
  },
  
  startCrop: function(){
    aCrop.stopCrop();
    
    var cropEl = aCrop.findPreviewImage();
    var imageInfo = aCrop.getCurrentImageInfo();
		if (!imageInfo)
		{
			return;
		}

		// TODO: Make this work -- 
		// This would set the initial crop selection to be the maximum size, centered for the image being cropped
		// This would work except it's using the full image dimensions, not the dimensions of the crop image workspace
		// setSelectCenter = imageInfo.height / 2;
		// setSelectHeight = imageInfo.width * aCrop.options.aspectRatio;
		// setSelectY1 = setSelectHeight / 2;
		// setSelectY2 = setSelectY1 + setSelectHeight;

    aCrop.api = $.Jcrop(cropEl);
    aCrop.api.setOptions({
      allowSelect: false,
      aspectRatio: aCrop.options.aspectRatio,
			// setSelect: [0,setSelectY1,imageInfo.width,setSelectY2],
			minSize: aCrop.options.minimumSize ? aCrop.options.minimumSize : [1,1],
      maxSize: aCrop.options.maximumSize ? aCrop.options.maximumSize : [imageInfo.width, imageInfo.height],
			trueSize: [imageInfo.width, imageInfo.height]
    });
    aCrop.setAspectMask(cropEl);
    
    $('.a-media-crop-controls').clone().appendTo('.a-media-selection-preview-item.current .jcrop-holder').show();
  },
  
  stopCrop: function(){
    if (aCrop.api) {
      aCrop.api.destroy();
    }
  },
  
  findPreviewImage: function(){
    // find the first image in the current list-item; subsequent images may be Jcrop clones
    return $(aCrop.el.previewList).find('li.current img');
  },
  
  getCurrentImageInfo: function(){
    var id = aCrop.getPreviewMediaId();
    if (id && aCrop.options.imageInfo && aCrop.options.imageInfo[id])
    {
      return aCrop.options.imageInfo[id];
    }
    return false;
  },
  
  getPreviewMediaId: function(){
    var $cropEl = aCrop.findPreviewImage();
    return aCrop.getMediaIdForLi($cropEl.parents('li'));
  },
  
  getMediaIdForLi: function(li){
		var s = $(li).attr('id');
		if (!s)
		{
			return false;
		}
    var mediaId = s.split('-');
    return mediaId[mediaId.length-1];
  },
  
  thumbnailClickHandler: function(e){
		$('.cropping-now').removeClass('cropping-now');
		$(e.target).parents('.a-media-selection-list-item').addClass('cropping-now');
    var mediaId = aCrop.getMediaIdForLi($(e.currentTarget).parents('.a-media-selection-list-item'));
		if (mediaId)
		{
	    $('#' + aCrop.el.previewList.replace('#','') + '-' + mediaId).addClass('current').siblings().removeClass('current');
			$('.a-crop-workspace').fadeIn();
    }
    aCrop.startCrop();
		$('#a-save-crop').click(function() { aCrop.setCrop(apostrophe.selectOptions['setCropUrl']); return false; });
		$('#a-cancel-crop').click(function() { aCrop.resetCrop(); return false; });
		return false;
  },
  
  setAspectMask: function(el){
    var imageInfo = aCrop.getCurrentImageInfo();

    if (!imageInfo.cropWidth) {
      if (aCrop.options.aspectRatio > 1) {
        imageInfo.cropWidth = imageInfo.width;
        imageInfo.cropHeight = Math.floor(imageInfo.width / aCrop.options.aspectRatio);
      } else {
        imageInfo.cropHeight = imageInfo.height;
        imageInfo.cropWidth = Math.floor(imageInfo.height * aCrop.options.aspectRatio);
      }
      
      imageInfo.cropLeft = 0;
      imageInfo.cropTop = Math.floor((imageInfo.height - imageInfo.cropHeight) / 2);
    }
    
    var coords = [
      imageInfo.cropLeft,
      imageInfo.cropTop,
      imageInfo.cropLeft + imageInfo.cropWidth,
      imageInfo.cropTop + imageInfo.cropHeight
    ];
        
    aCrop.api.setSelect(coords);
  },
  
  setCrop: function(url){
    var mediaId = aCrop.getPreviewMediaId();
    var coords = aCrop.api.tellSelect();
    var imageInfo = aCrop.getCurrentImageInfo();
    var $img = aCrop.findPreviewImage();
    var $tmb = $(aCrop.el.slideshowList).find('li.a-media-selection-list-item').eq(0);
    var params = {
      id: mediaId,
      cropLeft: coords.x,
      cropTop: coords.y,
      cropWidth: coords.w,
      cropHeight: coords.h,
      width: imageInfo.width,
      height: imageInfo.height
    };
    
    var thumbWH = {};
    if (aCrop.options.aspectRatio){
      thumbWH = {
        width: $tmb.width() + 'px',
        height: $tmb.height() + 'px'
      };
    }

    $.post(url, params, function(response){
      $(aCrop.el.slideshowList).html(response)
        .find('li.a-media-selection-list-item').css(thumbWH); // set width/height on <li> so while image loads there isn't a jump
      // make sure delete button is visible
      aUI("a-media-selection-list");
			$('.cropping-now').removeClass('cropping-now');
			$(".a-crop-workspace").fadeTo(500,1).fadeOut();
    });
  },
  
  resetCrop: function(hardReset){
    if (hardReset) { // reinstantiate crop
      aCrop.startCrop();
    }
        
    var mediaId = aCrop.getPreviewMediaId();
    
    if (!aCrop.options.imageInfo) return;
    
    var imageInfo = aCrop.options.imageInfo[mediaId];
    
    if (!imageInfo) return;
    
    var coords = [
      imageInfo.cropLeft,
      imageInfo.cropTop,
      imageInfo.cropLeft + imageInfo.cropWidth,
      imageInfo.cropTop + imageInfo.cropHeight
    ];
    
    aCrop.api.setSelect(coords);
  	aUI("a-media-selection-list");
		$('.cropping-now').removeClass('cropping-now');
		$(".a-crop-workspace").fadeOut();
  }
}