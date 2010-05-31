aCrop = {
  cropApi: null,
  previewApi: null,
  
  el: {
    cropImages: '#a-media-selection-list img'
    previewImages: '#a-media-selection-preview img',
  },
   
  init: function(){
    aCrop.JcropAPI = $(aCrop.el.cropImages).each(function(){
      $.Jcrop(this);
    });
    
    aCrop.JcropAPI = $(aCrop.el.cropImages).each(function(){
      $.Jcrop(this);
    });
  }
}