<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BP Live Salary Sheet For {{$agency->name}} --{{$agency->code}}</title>
<style type="text/css">
    
@import url('https://fonts.googleapis.com/css?family=Lobster&display=swap');
body{
    background: linear-gradient(
      90deg,
      rgba(69, 87, 266, 0.9),
      rgba(196, 80, 193, 1)
    );
}
.container{
/*  max-width: 1100px;
  margin: auto;
  padding: 0 2rem;*/
  overflow: hidden;
}

h1{
    padding: 1rem;
    color: #fff;
    text-align: center;
    font-size: 3rem;
    position: relative;
}

.title-underline{
     width: 200px;
    border: 3px solid white;
    position: absolute;
    top: 47%;
    right: 43%;
    border-radius: 7px;
}

table{
    border-collapse: collapse;
    width: 100%;
    text-align: center
}
table thead{ 
    background-color: #000;
    color: #fff;
    font-size: 1.5rem;
   
}
table th{
    border: 2px solid #ddd;
    padding: 14px 12px;
    color: #fff;
    position: sticky;
    top: 0;
    z-index: 5;
    
}

table tbody td{
    border: 2px solid #ddd;
/*    padding: 15px 12px;*/
    color: #fff;
    font-size: 18px;
}


table tr:nth-child(even)
{background-color: rgba(237, 237, 237, 0.5);}

table tr:hover {background-color: #36314A;}



table tbody td a{
    display: block;
    padding: 10px; 
    /*IMPORTANT: padding just for a 
    so we fill all the A:link cell
    (delete the padding for td)*/
     text-decoration: none;
    color: #fff;
    
}
table tbody td a:hover{
    background-color: aqua;
}

</style>
    
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    
    <div class="container">
      <h1>{{$agency->name}} --{{$agency->code}} --{{$start_date}} to {{$end_date}}</h1>
    <table  id="salaryTable">
        
        <thead>
            <tr>
                <th>Sl</th>
                <th>Name</th>
                <th>ID</th>
                <th>Type</th>
                <th>Day</th>
                <th>Time</th>
                <th>Total Point</th>
                <th>Basic Point</th>
                <th>Extra Point</th>
                <th>Basic Salary (BD TK)</th>
                <th>Extra Point Bonus (BD TK)</th>
               
                <th>BP Lucky Star Bonus (BD TK)</th>
                
                <th>Total Host Salary (BD TK)</th>
                <th>Agency Commision (BD TK)</th>
                <th>Agency Extra Point Bonus (BD TK)</th>
                <th>Total Salary (BD TK)</th>
            </tr>
        </thead>
        <tbody>
            @php
            $i=0;
            @endphp
           @foreach($data as $host)
           @php
               if ($host['extra_point'] >= 50000) {
            $percentage = ($host['hosting_type'] == 'Video') ? 7 : 6;
            $extra_point_bonus = $percentage * $host['extra_point'] / 1000;
            $extra_point_bonus = $extra_point_bonus;

            } else {
                $extra_point_bonus = 0; // Set to 0 if extra_point < 15,000
              
            }
            if ($host['extra_point'] >= 50000 && $host['gift']>= 400000) {
           
            $extra_agency_point_bonus=1 * $host['extra_point'] / 1000;
            } else {
                $extra_agency_point_bonus = 0; // Set to 0 if extra_point < 15,000
            }
            list($hours, $minutes, $seconds) = explode(':', $host['time']);

            // Calculate the total number of seconds
            $totalSeconds_done = ($hours * 3600) + ($minutes * 60) + $seconds;
           @endphp
           @if($host['hosting_type'] == 'Video' && $host['day'] >6 && $totalSeconds_done > 43200)
           @php
            if($host['basic_point']>=400000 && $host['basic_point']<=4999999){
               $agency_percentage = ($host['hosting_type'] == 'Video') ? 1 : 1;
               $basic_agency_salary = $agency_percentage * $host['basic_point'] / 1000;
             }elseif($host['basic_point']>=5000000){
                $agency_percentage = ($host['hosting_type'] == 'Video') ? 1.20 : 1;
                $basic_agency_salary = $agency_percentage * $host['basic_point'] / 1000;
             }else{
              $basic_agency_salary=0;
             }
           @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $host['name'] }}</td>
                <td>{{ $host['id'] }}</td>
                <td>{{ ($host['hosting_type'] == 'Audio') ? 'Audio' : 'Video' }}</td>
                <td>{{ $host['day'] }}</td>
                <td>{{ $host['time'] }}</td>
                <td>{{ $host['gift'] }}</td>
                @if( $host['gift']>=200000)
                <td>{{ $host['basic_point'] }}</td>
                <td>{{ $host['extra_point'] }}</td>
                <td>{{ $host['basic_salary'] }}</td>
                <td>{{$extra_point_bonus}}</td>
                <td>0</td>
                <td>{{$extra_point_bonus+$host['basic_salary']}}</td>
                <td>{{$basic_agency_salary}}</td>
                <td>{{$extra_agency_point_bonus}}</td>
                <td>{{$extra_point_bonus+$host['basic_salary']+$basic_agency_salary+$extra_agency_point_bonus}}</td>
                @else
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                @endif
            </tr>
            @elseif($host['hosting_type'] == 'Audio' && $totalSeconds_done > 43200)
             @php
            if($host['basic_point']>=400000 && $host['basic_point']<=4999999){
               $agency_percentage = ($host['hosting_type'] == 'Video') ? 1 : 1;
               $basic_agency_salary = $agency_percentage * $host['basic_point'] / 1000;
             }elseif($host['basic_point']>=5000000){
                $agency_percentage = ($host['hosting_type'] == 'Video') ? 1.20 : 1;
                $basic_agency_salary = $agency_percentage * $host['basic_point'] / 1000;
             }else{
              $basic_agency_salary=0;
             }
           @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $host['name'] }}</td>
                <td>{{ $host['id'] }}</td>
                <td>{{ ($host['hosting_type'] == 'Audio') ? 'Audio' : 'Video' }}</td>
                <td>{{ $host['day'] }}</td>
                <td>{{ $host['time'] }}</td>
                <td>{{ $host['gift'] }}</td>
                @if( $host['gift'] >=200000)
                <td>{{ $host['basic_point'] }}</td>
                <td>{{ $host['extra_point'] }}</td>
                <td> {{ $host['basic_salary'] }}</td>
                <td>{{$extra_point_bonus}}</td>
                <td>0</td>
                <td>{{$extra_point_bonus+$host['basic_salary']}}</td>
               
                
                <td>{{$basic_agency_salary}}</td>
                <td>{{$extra_agency_point_bonus}}</td>
                <td>{{$extra_point_bonus+$host['basic_salary']+$basic_agency_salary+$extra_agency_point_bonus}}</td>
                 @else
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                @endif
            </tr>
            @else
            @php
            if ($host['extra_point'] >= 50000) {
            $percentage = ($host['hosting_type'] == 'Video') ? 5 : 5;
            $without_day_time = $percentage * $host['extra_point'] / 1000;
            $without_day_time = $without_day_time;
            } else {
                $without_day_time = 0; // Set to 0 if extra_point < 50,000
            }
            $extra_point_salary_without_day_time=$without_day_time;
            //main
            $main_point_salary_without_day_time = 5 *$host['basic_point'] / 1000;
           
            if($host['basic_point']>=400000 && $host['basic_point']<=4999999){
               $agency_percentage = ($host['hosting_type'] == 'Video') ? 1 : 1;
               $basic_agency_salary = $agency_percentage * $host['basic_point'] / 1000;
             }elseif($host['basic_point']>=5000000){
                $agency_percentage = ($host['hosting_type'] == 'Video') ? 1.20 : 1;
                $basic_agency_salary = $agency_percentage * $host['basic_point'] / 1000;
             }else{
              $basic_agency_salary=0;
             }
           @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $host['name'] }}</td>
                <td>{{ $host['id'] }}</td>
                <td>{{ ($host['hosting_type'] == 'Audio') ? 'Audio' : 'Video' }}</td>
                <td>{{ $host['day'] }}</td>
                <td>{{ $host['time'] }}</td>
                <td>{{ $host['gift'] }}</td>
                @if($host['gift'] >=200000)
                <td> {{ $host['basic_point'] }}</td>
                <td>{{ $host['extra_point'] }}</td>
                <td>0</td>
                <td>{{$extra_point_salary_without_day_time+$main_point_salary_without_day_time}}</td>
                <td>0</td>
                <td>{{$extra_point_salary_without_day_time+$main_point_salary_without_day_time}}</td>
                
               
                <td>0</td>
                <td>0</td>
                <td>{{$extra_point_salary_without_day_time+$main_point_salary_without_day_time}}</td>
                @else
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                @endif
            </tr>
            @endif
            @endforeach
            
           
       
        </tbody>     
        
    </table>
    </div>
       <!-- Include DataTables JS -->

    <!-- Include jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha512-qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNTlrdUmTzrDgektczlKNRRhy5X5AAOnx5S09ydFYWWNSfcEqDTTHgtNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Include SheetJS -->
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

    <script>
        // Function to export the table as PDF
        function exportAsPDF() {
            const doc = new jsPDF();
            doc.autoTable({ html: '#salaryTable' });
            doc.save("document.pdf");
        }

        // Function to export the table as Excel
        function exportAsExcel() {
            const table = document.getElementById("salaryTable");
            const workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet 1" });
            XLSX.writeFile(workbook, "document.xlsx");
        }

        // Initialize DataTables
      
    </script>

    <button onclick="exportAsPDF()">Export as PDF</button>
    <button onclick="exportAsExcel()">Export as Excel</button>
</body>
</html>


