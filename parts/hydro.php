    <div class="row panel text-center">
      <h1>Гидроустановка v.5.08 Beta2</h1>
      <div class="col-md-6">
        <h2>Вентиляторы</h2>
        <canvas id="GAGE-FAN"></canvas>
        <p><span id="GAGE-FAN_VAL" data-var="ServoDrive-%"></span>%</p>
      </div>
      <div class="col-md-6">
        <h2>Температура</h2>
        <canvas id="GAGE-TEMP"></canvas>
        <p><span id="GAGE-TEMP_VAL" data-var="TermoSensor-1"></span>°C</p>
      </div>
      <div class="col-md-12" style="margin:20px 0; border-bottom:1px solid #ccc;"></div>
      <div class="col-md-6 text-left">
        <p>Подача: <span data-var="SetNomT"></span>°C</p>
        <p>Поток: <span data-var="Flow"></span> л/ч</p>
        <p>Обратка: <span data-var="TermoSensor-2"></span>°C</p>
        <p>Давление: <span data-var="Pressure"></span> Bar</p>
      </div>
      <div class="col-md-6 text-center">
        <h3>Питание</h3>
        <svg id="PUMP_STATUS-ICON" xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="red" viewBox="0 0 16 16">
          <path d="M7.5 1v7h1V1h-1z"/>
          <path d="M3 8.812a4.999 4.999 0 0 1 2.578-4.375l-.485-.874A6 6 0 1 0 11 3.616l-.501.865A5 5 0 1 1 3 8.812z"/>
        </svg>
        <span id="PUMP_STATUS" data-var="PowerON" style="display:none;"></span>
        <p id="PUMP_STATUS-TEXT">Off</p>
      </div>
    </div>
<script>
    var opts = {
      angle: 0.15, // The span of the gauge arc
      lineWidth: 0.44, // The line thickness
      radiusScale: 1, // Relative radius
      pointer: {
        length: 0.6, // // Relative to gauge radius
        strokeWidth: 0.035, // The thickness
        color: '#000000' // Fill color
      },
      limitMax: false,     // If false, max value increases automatically if value > maxValue
      limitMin: false,     // If true, the min value of the gauge will be fixed
      colorStart: '#6FADCF',   // Colors
      colorStop: '#8FC0DA',    // just experiment with them
      strokeColor: '#E0E0E0',  // to see which ones work best for you
      generateGradient: true,
      highDpiSupport: true,     // High resolution support 
      staticLabels: {
        font: "10px sans-serif",  // Specifies font
        labels: [0, 50, 100],  // Print labels at these values
        color: "#000000",  // Optional: Label text color
        fractionDigits: 0  // Optional: Numerical precision. 0=round off.
      },
    };

    var gaugeFan = new Gauge(document.getElementById('GAGE-FAN')).setOptions(opts);
    gaugeFan.maxValue = 100;
    gaugeFan.setMinValue(0);
    gaugeFan.animationSpeed = 32;

    var gaugeTemp = new Gauge(document.getElementById('GAGE-TEMP')).setOptions(opts);
    gaugeTemp.maxValue = 100;
    gaugeTemp.setMinValue(0);
    gaugeTemp.animationSpeed = 32;

    setInterval(()=>{
      gaugeFan.set(document.getElementById("GAGE-FAN_VAL").innerHTML);
      gaugeTemp.set(document.getElementById("GAGE-TEMP_VAL").innerHTML);

      var pumpVal = document.getElementById('PUMP_STATUS').textContent.trim();
      if(pumpVal === '0'){
        document.getElementById('PUMP_STATUS-TEXT').textContent = 'OFF';
        document.getElementById('PUMP_STATUS-ICON').setAttribute('fill','gray');
      } else if(pumpVal === '1'){
        document.getElementById('PUMP_STATUS-TEXT').textContent = 'ON';
        document.getElementById('PUMP_STATUS-ICON').setAttribute('fill','green');
      } else {
        document.getElementById('PUMP_STATUS-TEXT').textContent = pumpVal;
        document.getElementById('PUMP_STATUS-ICON').setAttribute('fill','gray');
      }

    }, 500);
  </script>