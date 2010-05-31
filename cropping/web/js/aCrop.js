aCrop = {
  slideshowApi: [],
  previewApi: [],
  
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
        
    aCrop.slideshowApi = $(aCrop.el.slideshowImages).each(function(){
      //aCrop.slideshowApi.push($.Jcrop(this));
      var api = $.Jcrop(this);
      api.setOptions({allowResize: false});
      aCrop.setAspectMask(api, this);
      api.disable();
    });
    
    aCrop.previewApi = $(aCrop.el.previewImages).each(function(){
      //aCrop.previewApi.push($.Jcrop(this));
      var api = $.Jcrop(this);
      api.setOptions({aspectRatio: aCrop.options.aspectRatio});
      aCrop.setAspectMask(api, this);
    });
    
    $(aCrop.el.slideshowList).find('li').click(function(){
      var mediaId = $(this).attr('id').split('-');
      mediaId = mediaId[mediaId.length-1];
      $('#' + aCrop.el.previewList.replace('#','') + '-' + mediaId).addClass('current').siblings().removeClass('current');
    });
  },
  
  setAspectMask: function(api, el){
    var bounds = api.getBounds();
    
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
    
    api.setSelect(coords);
  }
}