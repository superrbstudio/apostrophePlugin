aCrop = {
  cropApi: null,
  previewApi: null,
  
  el: {
    cropImages: '#a-media-selection-list img',
    previewImages: '#a-media-selection-preview img'
  },
   
  init: function(){
    aCrop.cropApi = $(aCrop.el.cropImages).each(function(){
      $.Jcrop(this);
    });
    
    aCrop.previewApi = $(aCrop.el.cropImages).each(function(){
      $.Jcrop(this);
    });
    //aCrop.previewApi.setOptions({allowResize: false});
  }
}