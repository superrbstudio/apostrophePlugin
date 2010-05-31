aCrop = {
  cropApi: null,
  previewApi: null,
  
  el: {
    cropImages: '#a-media-selection-list img',
    previewImages: '#a-media-selection-preview img'
  },
   
  init: function(){
    aCrop.cropApi = $(aCrop.el.cropImages).each(function(){
      return $.Jcrop(this);
    });
    
    aCrop.previewApi = $(aCrop.el.previewImages).each(function(){
      return $.Jcrop(this);
    });
    aCrop.previewApi.each(function(i,e){
      //e.setOptions({allowResize: false});
    });
  }
}