    <div class="row panel text-center">
      <h1>Электропитание</h1>

      <div class="col-md-4">
        <h3>Напряжение Фаза-1</h3>
        <canvas id="GAGE-V1"></canvas>
        <p><span id="GAGE-V1_VAL" data-var="U-L1"></span> V</p>
      </div>

      <div class="col-md-4">
        <h3>Напряжение Фаза-2</h3>
        <canvas id="GAGE-V2"></canvas>
        <p><span id="GAGE-V2_VAL" data-var="U-L2"></span> V</p>
      </div>

      <div class="col-md-4">
        <h3>Напряжение Фаза-3</h3>
        <canvas id="GAGE-V3"></canvas>
        <p><span id="GAGE-V3_VAL" data-var="U-L3"></span> V</p>
      </div>

      <div class="col-md-12" style="margin:20px 0; border-bottom:1px solid #ccc;"></div>

      <table class="table table-striped text-left">
        <tr><td>Ток Ф-1:</td><td><span data-var="I-L1"></span> A</td>
            <td>Мощн. Ф-1:</td><td><span data-var="P-L1"></span> W</td></tr>
        <tr><td>Ток Ф-2:</td><td><span data-var="I-L2"></span> A</td>
            <td>Мощн. Ф-2:</td><td><span data-var="P-L2"></span> W</td></tr>
        <tr><td>Ток Ф-3:</td><td><span data-var="I-L3"></span> A</td>
            <td>Мощн. Ф-3:</td><td><span data-var="P-L3"></span> W</td></tr>
        <tr><td>Общая мощность:</td><td><span data-var="P-Total"></span> W</td>
            <td>Частота:</td><td><span data-var="Frequency"></span> Hz</td></tr>
        <tr><td>Энергия:</td><td colspan="3"><span data-var="Energy"></span> kWh</td></tr>
      </table>
    </div>

    <script>
    var opts = {angle:0.15, lineWidth:0.44, radiusScale:1,
      pointer:{length:0.6, strokeWidth:0.035, color:'#000'},
      limitMax:false, limitMin:false, colorStart:'#6FADCF', colorStop:'#8FC0DA', strokeColor:'#E0E0E0',
      staticLabels:{font:'10px sans-serif', labels:[0,100,200,300], color:'#000', fractionDigits:0},
      generateGradient:true, highDpiSupport:true
    };
    var gaugeV1 = new Gauge(document.getElementById('GAGE-V1')).setOptions(opts);
    gaugeV1.maxValue = 300; gaugeV1.setMinValue(0); gaugeV1.animationSpeed = 32;
    var gaugeV2 = new Gauge(document.getElementById('GAGE-V2')).setOptions(opts);
    gaugeV2.maxValue = 300; gaugeV2.setMinValue(0); gaugeV2.animationSpeed = 32;
    var gaugeV3 = new Gauge(document.getElementById('GAGE-V3')).setOptions(opts);
    gaugeV3.maxValue = 300; gaugeV3.setMinValue(0); gaugeV3.animationSpeed = 32;
    setInterval(()=>{
      gaugeV1.set(document.getElementById('GAGE-V1_VAL').innerHTML);
      gaugeV2.set(document.getElementById('GAGE-V2_VAL').innerHTML);
      gaugeV3.set(document.getElementById('GAGE-V3_VAL').innerHTML);
    }, 500);
    </script>
