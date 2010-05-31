aCrop = {
  api: null,
  
  options: {},
  
  el: {
    previewList: '#a-media-selection-preview',
    previewImages: '#a-media-selection-preview img',
    slideshowList: '#a-media-selection-list',    
    slideshowImages: '#a-media-selection-list img',
  },
   
  init: function(options){
    $(aCrop.el.previewList).find('li').eq(0).addClass('current');
    
    $.extend(aCrop.options, options);
    
    aCrop.startCrop();
    
    $(aCrop.el.slideshowList).find('li').click(aCrop.thumbnailClickHandler);
  },
  
  startCrop: function(cropEl){
    if (!cropEl) {
      var cropEl = $(aCrop.el.previewList).find('li.current img');
    }
    
    aCrop.stopCrop();
        
    aCrop.api = $.Jcrop(cropEl);
    aCrop.api.setOptions({aspectRatio: aCrop.options.aspectRatio});
    aCrop.setAspectMask(cropEl);
    
    $('.a-media-crop-controls').clone().appendTo('.jcrop-holder div:first').show();
  },
  
  stopCrop: function(){
    if (aCrop.api) {
      aCrop.api.destroy();
    }
  },
  
  thumbnailClickHandler: function(e){
    var mediaId = $(e.currentTarget).attr('id').split('-');
    mediaId = mediaId[mediaId.length-1];
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
  
  resetCrop: function(){
    
  }
}