<html><head>
  <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
  <style type="text/css">
    .dataTables_filter { float: left; }
    table { font-size: 13px; }
  </style>
</head><body>
<?php

  include 'amp.php';

  list($stats, $perPoint) = stats('timestamp > "2013-07-10"');


  makeTable($stats, array_merge([''], array_keys(reset($stats))));

  function makeTable($data, $headers)
  {
    echo "<table><thead><tr>";
    foreach($headers as $header)
    {
      if ($header == 'gender')
      {
        $header = '&#9893;';
      }
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

<script src="http://ajax.aspnetcdn.com/ajax/jquery/jquery-1.9.0.min.js"></script>
<script src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
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
