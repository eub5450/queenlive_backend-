<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QueenLive Supper Agency Salary Sheet For {{$master_agency->name}} --{{$master_agency->code}}</title>
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
      <h1>QueenLive Supper Agency Salary Sheet For {{$master_agency->name}} --{{$master_agency->code}} --{{$start_date}} to {{$end_date}}</h1>
    <table  id="salaryTable">
        
        <thead>
            <tr>
                <th>Sl</th>
                <th>Agency</th>
                <th>Agency Code</th>
                <th>Total Target</th>
            </tr>
        </thead>
        <tbody>
             @php
			 $i=0;
			 $reciving_history_total=0;
			 @endphp
			 @foreach($lists as $item)
             </tr>
                <td>{{ ++$i }}</td>
                <td>{{ $item['agency'] }}</td>
                <td>{{ $item['agency_code'] }}</td>
                <td>{{ number_format($item['total_target']) }}</td>
            </tr>
            @php
            $reciving_history_total+=$item['total_target'];
            @endphp
           @endforeach
       
        </tbody>  
         <tfoot>
			 <tr>
			 <th colspan="3">Total Target</th>
              <th>{{number_format($reciving_history_total)}}</th>
			</tr>
		</tfoot>
        
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


