$(document)
  .ready(function() {
    $('.ui.rating')
      .rating({
        clearable: true
      })
    ;

    $('.ui.sidebar')
      .sidebar('attach events', '.launch.button')
    ;
    $('.demo.sidebar')
      .sidebar('setting', 'transition', 'overlay')
    ;


  })
;