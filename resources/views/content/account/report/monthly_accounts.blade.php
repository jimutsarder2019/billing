@extends('layouts/layoutMaster')
@section('title', 'account Report')
@section('content')
<style type="text/css">
  #container {
    height: 400px;
  }

  .highcharts-figure,
  .highcharts-data-table table {
    min-width: 310px;
    min-width: 800px;
    margin: 1em auto;
  }

  #datatable {
    font-family: Verdana, sans-serif;
    border-collapse: collapse;
    border: 1px solid #ebebeb;
    margin: 10px auto;
    text-align: center;
    width: 100%;
    max-width: 500px;
  }

  #datatable caption {
    padding: 1em 0;
    font-size: 1.2em;
    color: #555;
  }

  #datatable th {
    font-weight: 600;
    padding: 0.5em;
  }

  #datatable td,
  #datatable th,
  #datatable caption {
    padding: 0.5em;
  }

  #datatable thead tr,
  #datatable tr:nth-child(even) {
    background: #f8f8f8;
  }

  #datatable tr:hover {
    background: #f1f7ff;
  }
</style>

<script src="{{asset('assets/js')}}/chart/highcharts.js"></script>
<script src="{{asset('assets/js')}}/chart/data.js"></script>

<div class="row">
  <div class="col-2">
    <form action="{{route('monthly_accounts')}}">
      <select name="year" id="" class="form-control" onchange="this.form.submit()">
        <option>Select</option>
        @for($i = 2020; $i <= 2050; $i++) @if(request('year')) <option @if(request('year')==$i) selected @endif value="{{$i}}">{{$i}}</option>
          @else
          <option @if(date('Y')==$i) selected @endif value="{{$i}}">{{$i}}</option>
          @endif
          @endfor
      </select>
    </form>
  </div>
</div>

<figure class="highcharts-figure">
  <div id="container"></div>

  <table id="datatable">
    <thead>
      <tr>
        <th>Month</th>
        <th>Income</th>
        <th>Expense</th>
      </tr>
    </thead>
    <tbody>
      @foreach($data as $f_data)
      <tr>
        @foreach($f_data as $item)
        <th>{{ $item }}</th>
        @endforeach
      </tr>
      @endforeach
    </tbody>
  </table>
</figure>



<script type="text/javascript">
  Highcharts.chart('container', {
    data: {
      table: 'datatable'
    },
    chart: {
      type: 'column'
    },
    title: {
      text: 'Monthly Report'
    },
    xAxis: {
      type: 'category'
    },
    yAxis: {
      allowDecimals: false,
      title: {
        text: 'Amount'
      }
    }
  });
</script>
@endsection