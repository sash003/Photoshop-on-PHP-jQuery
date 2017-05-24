cp = ColorPicker(document.getElementById('slide'), document.getElementById('picker'),
  function(hex, hsv, rgb, mousePicker, mouseSlide) {
    currentColor = hex;
    ColorPicker.positionIndicators(
      document.getElementById('slide-indicator'),
      document.getElementById('picker-indicator'),
      mouseSlide, mousePicker
    );
    document.getElementById('picked').value = hex;
    App.text.css({
      color: hex
    });
  });
cp.setHex('#ffffff');