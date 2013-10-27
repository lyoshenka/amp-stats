<html><head>
  <link rel="stylesheet" type="text/css" href="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
  <style type="text/css">
    .dataTables_filter { float: left; }
  </style>
</head><body>
<?php

  include 'amp.php';

  $throwStats = throwCharts();


  echo "<h1>Throws</h1>";
  makeTable($throwStats[0], array_merge([''], array_keys($throwStats[0])));

  echo "<br/><br/><h1>Goals</h1>";
  makeTable($throwStats[1], array_merge([''], array_keys($throwStats[0])));

  echo "<br/><br/><h1>Drops</h1>";
  makeTable($throwStats[2], array_merge([''], array_keys($throwStats[0])));

  function makeTable($data, $headers)
  {
    echo "<table><thead><tr>";
    foreach($headers as $header)
    {
      echo "<td>$header</td>";
    }

    echo "</tr></thead><tbody>";


    foreach($data as $leftHeader => $row)
    {
      echo "<tr><th>$leftHeader</th>";
      foreach($row as $topHeader => $datum)
      {
        if ($topHeader == $leftHeader)
        {
          $datum = '-';
        }
        echo "<td>$datum</td>";
      }
      echo "</tr>";
    }

    echo "</tbody></table>";
  }

?>

<script src="//ajax.aspnetcdn.com/ajax/jquery/jquery-1.9.0.min.js"></script>
<script src="//ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
<script type="text/javascript">
  $(function(){
    $('table').dataTable({
      "bPaginate": false,
      "aoColumnDefs": [
        { "asSorting": [ "desc", "asc" ], "aTargets": "_all" }
      ],
    });
  });
</script>

</body></html>
