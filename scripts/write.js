
  
  App.fontImg = $('#textEdit img[data-font]'),
  App.sizeInp = $('#sizeInp'),
  App.sizeSub = $('#sizeSub'),
  App.textForm = $('#textEdit'),
  App.colorSel = $('.colors img'),
  App.colorInp = $('#colorInp'),
  App.colorIn = $('[name=color]'),
  App.fontIn = $('[name=font]'),
  App.sizeIn = $('[name=size]'),
  App.textSub = $('#submitTextEdit'),

  App.alignSel = $('.align'),
  App.alignIn = $('#align'),

  App.color, App.fontFamily, App.fontSize, App.align;

App.textSub.click(function(e) {
  App.preventdefault(e);
  App.textSub.prop('disabled', true);
  var src = App.pathinp.val(),
    t = App.text.html(),
    x = App.text.css('left'),
    y = App.text.css('top'),
    c = App.colorIn.val(),
    f = App.fontIn.val(),
    s = App.sizeIn.val();

  $.ajax({
    type: 'post',
    url: 'workerText.php',
    //processData: false,
    data: {
      src: src,
      text: t,
      x: x,
      y: y,
      font: f,
      color: c,
      size: s
    },
    success: function(response) {
      //console.log(response);
      if (/[а-я]/.test(response) ||
        !response) {
        App.thumbnail.attr("src", 'img/gon.jpg');
      } else {
        App.thumbnail.attr("src", response);
        App.pathinp.val(response);
        App.thumbnail.on('load', function() {
          App.imgW = App.thumbnail.width();
          App.imgH = App.thumbnail.height();
          //console.log(imgW, imgH);
          App.write.css({
            'width': App.imgW,
            'height': App.imgH
          });
          App.text.hide().html('');
        });
      }
      App.textSub.prop('disabled', false);
    }
  });
});

App.alignSel.click(function() {
  App.align = $(this).attr('data-a');
  App.text.css({
    textAlign: App.align
  });

});

App.body.on('mouseup', App.writed, function(e) {
  if (App.u.b) {
    e = e || window.event;
    if (e.which == 1) {
      if (!App.text.is(e.target) // если клик был не по нашему блоку
        &&
        App.text.has(e.target).length === 0) { // и не по его дочерним элементам
        var offset = App.write.offset();
        App.text.show().css({
          left: e.pageX - offset.left,
          top: e.pageY - offset.top,
          maxWidth: App.imgW - 22
        }).focus();
        //console.log((e.pageX - offset.left) + 'x' + (e.pageY - offset.top));
        $('[name=x]').val(parseInt(e.pageX - offset.left));
        $('[name=y]').val(parseInt(e.pageY - offset.top));
      }
    }
  }
});

App.fontImg.click(function() {
  fontFamily = $(this).attr('data-font');
  App.text.css({
    fontFamily: fontFamily
  });
  App.fontIn.val(fontFamily);
});

App.sizeInp.on('keydown', function(e) {
  if (e.which == 13) {
    App.preventdefault(e);
    if (/^\d{1,3}$/.test($(this).val())) {
      App.text.css({
        fontSize: App.sizeInp.val() + 'px'
      });
      App.sizeIn.val(App.sizeInp.val());
      App.sizeInp.css({
        textShadow: '0 0 5px green'
      });
    } else {
      App.sizeInp.css({
        textShadow: '0 0 5px red'
      });
    }
  }
});

App.sizeSub.on('click', function() {
  if (/^\d{1,3}$/.test(App.sizeInp.val())) {
    App.text.css({
      fontSize: App.sizeInp.val() + 'px'
    });
    App.sizeIn.val(App.sizeInp.val());
    App.sizeInp.css({
      textShadow: '0 0 5px green'
    });
  } else {
    App.sizeInp.css({
      textShadow: '0 0 5px red'
    });
  }

});

App.body.on('keydown', text, function(e) {
  if (e.which == 27) {
    App.text.html('');
  }
});

