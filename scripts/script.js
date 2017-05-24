
// стахурского 32 кв 5 

var App = {
  body : $('body'),
  thumbnail : $("#thumbnail"),
  pathinp : $('#source'),

  write : $('#write'),
  writed : '#write',
  text : $('#text'),

  button1 : $('#buttupload'),
  hideDiv : $('#hide'),

  preventdefault : function(e) {
   e = e || window.event;
   if (e.preventDefault) e.preventDefault();
   else e.returnValue = false;

  },

  imgW : 0,
  imgH : 0,
  formData : null
};

App.thumbnail.on('load', function() {
 App.imgW = App.thumbnail.width();
 App.imgH = App.thumbnail.height();
 App.write.css({
   'width': App.imgW,
   'height': App.imgH
 });
});

$('#format img').click(function(){
  var format = $(this).data('format');
  $('[name=format]').val(format);
});

var width = $('[name=width]').width();
$('#goodInp, [name=image], #goodInp2, [name=watermark], #forWater').css('width', width+2+'px');

$('#clearWater').click(function(){
  $('[name=watermark]').val('');
});

if (/web4myself.ru/.test(location.href)) {
 $('span:contains(EXEC)').css('color', 'red');
}

$('#my_form').on('submit', function(e) {

 App.preventdefault(e); // предотвращение отправки формы по умолчанию

 var $that = $(this);
 if (window.FormData) {
   App.formData = new FormData($that.get(0)); // создаем новый экземпляр объекта и передаем ему нашу форму
 } else {
   $.error('FormData не поддерживается');
 }
 var errors = $that.find('[name=width]').validate(/^(\d{1,4})?$/, '<1281') +
   $that.find('[name=height]').validate(/^(\d{1,3})?$/, '<961') +
   $that.find('[name=rlc]').validate(/^\d?$/) +
   $that.find('[name=size_bord]').validate(/^(\d{1,3})?$/, '<101') +
   $that.find('[name=color_bord]').validate(/^#[\da-z]{6}$/i) +
   $that.find('[name=brightness]').validate(/^(-?\d{1,3})?$/, '<101') +
   $that.find('[name=contrast]').validate(/^(-?\d{1,3})?$/, '<101') +
   $that.find('[name=speed]').validate(/^(\d{1,3})?$/, '<334') +
   $that.find('[name=countFrames]').validate(/^(\d{1})?$/, '<10') +
   $that.find('[name=setCountFrames]').validate(/^(\d{1,3})?$/, '<101') +
   $that.find('[name=setSlowdown]').validate(/^([\d\.]{1,3})?$/, '<3') +
   $that.find('[name=trimBackground]').validate(/^(\d{1,3})?$/, '<101') +
   $that.find('[name=filterVal]').validate(/^(\d{1,2})?$/, '<100') +
   $that.find('[name=coeff]').validate(/^(\d{1,3})?$/, '<=100') +
   $that.find('[name=x]').validate(/^(\d{1,4})?$/, '<1281') +
   $that.find('[name=y]').validate(/^(\d{1,4})?$/, '<961') +
   $that.find('[name=opacity]').validate(/^([\d\.]{1,3})?$/, '<=1') +
   $that.find('[name=format]').validate(/^(gif|png|jpg)?$/);

 if (errors) {
   $.error('У вас там что-то не в поряде с введёнными данными, ' + errors + ' раза');
 }

 App.button1.prop("disabled", true); // блокировка кнопки
 
 App.formData.append('ajax', true);

 $.ajax({
   url: $that.attr('action'),
   type: $that.attr('method'),
   contentType: false, // важно - убираем форматирование данных по умолчанию
   processData: false, // важно - убираем преобразование строк по умолчанию
   dataType: 'json',
   data: App.formData,
   
   success: function(response) {
     console.log(response);

     if (response.save == null) {

       App.thumbnail.attr("src", 'img/gon.jpg');
     } else {
       App.thumbnail.attr("src", response.save);
       App.pathinp.val(response.save);
       //thumbnail.imgAreaSelect({remove:true});
       App.thumbnail.on('load', function() {
         App.imgW = App.thumbnail.width();
         App.imgH = App.thumbnail.height();
         var str = App.imgW + 'x' + App.imgH;
         if (response.delay || response.countframes) {
           response.delay = response.delay || 10;
           str += '<br>Скорость гифки ' + response.delay + '<br>Количество кадров ' + response.countframes;
         }
         $('#getimage').html(str);
         App.write.css({
           'width': App.imgW,
           'height': App.imgH
         });
       });
     }
     App.button1.prop("disabled", false);
   }
 });
});

App.areaOn = $('#area');
 App.u = {
   a: 0,
   b: 1
 };
// areaOn.toggle(func1, func2);
App.areaOn.on('click', function() {
 if (App.u.a === 0) {
   App.thumbnail.imgAreaSelect({
     handles: true,
     keys: {
       arrows: 15,
       ctrl: 5,
       shift: 'resize'
     },
     onSelectChange: preview
   });
   App.areaOn.text('Выключить ImageArea');
   App.text.hide();
   App.u.a++;
   App.u.b--;
   
 } else {
   App.thumbnail.imgAreaSelect({
     remove: true
   }); //For hiding the imagearea
   App.areaOn.text('Включить ImageArea');
   App.u.a--;
   App.u.b++;
 }
});

function preview(img, selection) {

 $("#x1").val(selection.x1);
 $("#y1").val(selection.y1);
 $("#x2").val(selection.x2);
 $("#y2").val(selection.y2);
 $("#w").val(selection.width);
 $("#h").val(selection.height);

}

$("#butSave").click(function() {

 App.hideDiv.show();

 var image = $('#source').val();
 var x1 = $("#x1").val();
 var y1 = $("#y1").val();
 var x2 = $("#x2").val();
 var y2 = $("#y2").val();
 var w = $("#w").val();
 var h = $("#h").val();
 $.ajax({
   url: "worker_crop.php",
   type: "POST",
   data: {
     image: image,
     x1: x1,
     y1: y1,
     w: w,
     h: h
   },
   success: function(response) {
     //console.log(response)
     $('#response').html(response);
     App.hideDiv.hide();
   }
 });
});

// расширяем джейкверю на функцию validate
$.fn.validate = function(pattern, condition) {
 var el = $(this),
   val = el.val(),
   out = 0;
 //console.log(val)
 if (!pattern.test(val)) {
   out++;
 }
 if (condition) {
   out += !(eval('val' + condition));
 }
 if (out) {
   el.css({
     textShadow: '0 0 5px red'
   });
 } else {
   el.css({
     textShadow: '0 0 5px green'
   });
 }
 return out;
}

function equalHeightWidth(a, b, c) {
 var a = $(a);
 var b = $(b);
 if (a.height() > b.height()) {
   b.height(a.height());
 } else {
   a.height(b.height());
 }
 if (c === true) {
   if (a.width() > b.width()) {
     b.width(a.width());
   } else {
     a.width(b.width());
   }
 }
}