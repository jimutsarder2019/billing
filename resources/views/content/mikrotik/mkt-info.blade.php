@extends('layouts/layoutMaster')

@section('title', 'Mikrotik Info')
@section('content')
<div class="card">
  <div class="card-body">
    <div class="text-center mb-4">
      <h3>{{request('username') ? "User Name: ". request('username') :''}}</h3>
      <h3 id="show_ether_name"></h3>
      <h3 class="mb-2">Mikrotik: {{$mikrotik_name}}</h3>
    </div>
    <input type="hidden" value="{{request('id')}}" id="mkt_id">
    <input type="hidden" value="{{request('username')}}" id="username">

    <p id="result"></p>
    <div id="container" style="height: 400px;"></div>
    <div id="traffic"></div>
    <div class="text-center" id="mikrotik_data">
      <input type=hidden name="interface" id="interface" type="text" />
    </div>
    @if(!request('username'))
    <form action="{{route('mikrotik_info', request('id'))}}" method="get">
      <div class="row">
        <div class="col-sm-12 col-md-4 mt-2">
          <select name="ether_name" id="ether_name" class=" select2 form-select" onchange="this.form.submit()">
            <option value=""></option>
            @foreach($ethernates as $index => $ether)
            <option {{ request('ether_name') == $ether || ($index === 0 && !request()->has('ether_name')) ? 'selected' : '' }} value="{{ $ether }}">{{ $ether }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </form>
    @else
    <input type="hidden" id="ether_name">
    @endif
  </div>
</div>
@endsection

<!-- Add the required scripts here -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js" integrity="sha512-uMtXmF28A2Ab/JJO2t/vYhlaa/3ahUOgj1Zf27M5rOo8/+fcTUVH0/E0ll68njmjrLqOBjXM3V9NiPFL5ywWPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
  var chart;
  window.addEventListener("load", function() {
    let ether_name = document.getElementById('ether_name').value;
    document.getElementById('show_ether_name').innerHTML = `${ether_name ? 'Ether Name: '+ ether_name :''}`;
    let username = document.getElementById('username').value;
    let id = document.getElementById('mkt_id').value;
    let app_url = document.head.querySelector('meta[name="app_url"]').content;

    // Function to fetch Mikrotik info and perform necessary actions
    function fetchMikrotikInfo() {
      axios.get(`${app_url}/mikrotik_info/${id}?call_ip=true&ether_name=${ether_name}&username=${username}`)
        .then((resp) => {
          if (resp.status == 200) {
            // high chart
            var midata = resp.data;
            if (midata.length > 0) {
              var TX = parseInt(midata[0].data / (1024 * 1024));
              var RX = parseInt(midata[1].data / (1024 * 1024));
              var x = (new Date()).getTime();
              shift = chart.series[0].data.length > 19;
              chart.series[0].addPoint([x, TX], true, shift);
              chart.series[1].addPoint([x, RX], true, shift);
              // Update the traffic information with appropriate units
              updateTrafficInfo(TX, RX);

            } else {
              document.getElementById("traffic").innerHTML = "- / -";
            }
          }
        })
        .catch((error) => {
          // Handle errors if required
        });
    }

    // Function to update the traffic information with appropriate units (kbps or bps)
    function updateTrafficInfo(TX, RX) {
      const oneKbps = 1000; // 1 kbps in bps
      const oneMbps = 1000000; // 1 Mbps in bps

      // Convert TX and RX to bps
      const TX_bps = TX * oneMbps;
      const RX_bps = RX * oneMbps;

      // Choose appropriate units based on the value
      let TX_display, RX_display;
      if (TX_bps >= oneMbps) {
        TX_display = `${(TX_bps / oneMbps).toFixed(2)} Mbps`;
      } else if (TX_bps >= oneKbps) {
        TX_display = `${(TX_bps / oneKbps).toFixed(2)} kbps`;
      } else {
        TX_display = `${TX_bps.toFixed(2)} bps`;
      }

      if (RX_bps >= oneMbps) {
        RX_display = `${(RX_bps / oneMbps).toFixed(2)} Mbps`;
      } else if (RX_bps >= oneKbps) {
        RX_display = `${(RX_bps / oneKbps).toFixed(2)} kbps`;
      } else {
        RX_display = `${RX_bps.toFixed(2)} bps`;
      }

      // Update the HTML element with the calculated values
      document.getElementById("traffic").innerHTML = `Download ${TX_display} / Upload ${RX_display}`;
    }


    Highcharts.setOptions({
      global: {
        useUTC: false
      }
    });


    chart = new Highcharts.Chart({
      chart: {
        renderTo: 'container',
        animation: Highcharts.svg,
        type: 'line', //line,//area

        events: {
          load: function() {
            setInterval(function() {
              fetchMikrotikInfo();
            }, 3000);
          }
        }
      },
      title: {
        text: 'Monitoring'
      },
      xAxis: {
        type: 'datetime',
        tickPixelInterval: 100,
        maxZoom: 20 * 1000
      },
      yAxis: {
        minPadding: 0.2,
        maxPadding: 0.2,
        title: {
          text: 'Bandwith',
          margin: 20
        },
        startOnTick: true, // Start the yAxis at the lowest value present in data
        min: 0, // Set the minimum value of the yAxis to 0
        tickInterval: 5, // Set the interval between ticks to 5 units
        minPadding: 0, // Set the minimum padding to zero
      },
      tooltip: {
        formatter: function() {
          var point = this.point;
          return `<br>${(new Date()).toString()}</br><b>${this.series.name}</b><br/>Speed: ${point.y} Mbps`;
        }
      },
      series: [{
        name: 'Download Speed',
        data: []
      }, {
        name: 'Upload Speed',
        data: []
      }]
    });
  });
</script>