aCrop = {
  api: null,
  
  // these are the params we expect
  options: {
    ids: [],
    aspectRatio: 0,
    minimumWidth: 0,
    minimumHeight: 0,
    imageInfo: {}
  },
  
  el: {
    previewList: '#a-media-selection-preview',
    previewImages: '#a-media-selection-preview img',
    slideshowList: '#a-media-selection-list',    
    slideshowImages: '#a-media-selection-list img',
  },
   
  init: function(options){
    
    if (!aCrop.api) { // don't do this stuff after each ajax crop
      $(aCrop.el.previewList).find('li').eq(0).addClass('current');
    
      $.extend(aCrop.options, options);
    
      aCrop.startCrop();
    }
    
    $(aCrop.el.slideshowList).find('li').click(aCrop.thumbnailClickHandler);
  },
  
  startCrop: function(){
    aCrop.stopCrop();
    
    var cropEl = aCrop.findPreviewImage();
        
    aCrop.api = $.Jcrop(cropEl);
    aCrop.api.setOptions({
      allowSelect: false,
      aspectRatio: aCrop.options.aspectRatio,
      minSize: [aCrop.options.minimumWidth, aCrop.options.minimumHeight]
    });
    aCrop.setAspectMask(cropEl);
    
    $('.a-media-crop-controls').clone().appendTo('.jcrop-holder div:first').show();
  },
  
  stopCrop: function(){
    if (aCrop.api) {
      aCrop.api.destroy();
    }
  },
  
  findPreviewImage: function(){
    return $(aCrop.el.previewList).find('li.current img');
  },
  
  getPreviewMediaId: function(){
    var $cropEl = aCrop.findPreviewImage();
    return aCrop.getMediaIdForLi($cropEl.parents('li'));
  },
  
  getMediaIdForLi: function(li){
    var mediaId = $(li).attr('id').split('-');
    return mediaId[mediaId.length-1];
  },
  
  thumbnailClickHandler: function(e){
    var mediaId = aCrop.getMediaIdForLi(e.currentTarget);
    $('#' + aCrop.el.previewList.replace('#','') + '-' + mediaId).addClass('current').siblings().removeClass('current');
    
    aCrop.startCrop();
  },
  
  setAspectMask: function(el){    
    if (aCrop.options.aspectRatio > 1) {
      var cropWidth = $(el).width();
      var cropHeight = cropWidth / aCrop.options.aspectRatio;
    } else {
      var cropHeight = $(el).height();
      var cropWidth = cropHeight * aCrop.options.aspectRatio;
    }
    
    var cropY = ($(el).height() - cropHeight) / 2;
        
    var coords = [
      0,
      cropY,
      cropY + cropWidth,
      cropY + cropHeight
    ];
    
    aCrop.api.setSelect(coords);
  },
  
  setCrop: function(url){
    var mediaId = aCrop.getPreviewMediaId();  
    var coords = aCrop.api.tellSelect();
    var params = {
      id: mediaId,
      cropLeft: coords.x,
      cropTop: coords.y,
      cropWidth: coords.w,
      cropHeight: coords.h
    };
        
    var thumbWH = {
      width: $(aCrop.el.slideshowList).find('li.a-media-selection-list-item').eq(0).width() + 'px',
      height: $(aCrop.el.slideshowList).find('li.a-media-selection-list-item').eq(0).height() + 'px'
    };
    
    $.post(url, params, function(response){
      $(aCrop.el.slideshowList).html(response)
        .find('li.a-media-selection-list-item').css(thumbWH); // set width/height on <li> so while image loads there isn't a jump
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
  }
}