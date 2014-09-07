<html>
  <head>
  
    <style>
      /* general */
      body,textarea,input{background:0;border-radius:0;font:16px sans-serif;margin:0}.smooth{transition:all .2s}.btn,.nav a{text-decoration:none}*{outline:0}.container{margin:0 20px;width:auto}@media(min-width:1310px){.container{margin:auto;width:1270px}}label>*{display:inline}form>*{display:block;margin-bottom:10px}

      /* grid */
      .row{margin:1% 0;overflow:auto}.col{float:left}.c12,.table{width:100%}.c11{width:91.66%}.c10{width:83.33%}.c9{width:75%}.c8{width:66.66%}.c7{width:58.33%}.c6{width:50%}.c5{width:41.66%}.c4{width:33.33%}.c3{width:25%}.c2{width:16.66%}.c1{width:8.33%}@media(max-width:870px){.row .col{width:100%}}

      /* headings */
      h1{font-size:4em}h2,.btn{font-size:2em}

      /* tables */
      .table th,.table td{padding:.5em;text-align:left}.table tbody>*:nth-child(2n-1){background:#ddd}

      /* messages */
      .msg{padding:1.5em;background:#def;border-left:5px solid #59d}
      
      .stuff {
        max-width: 500px;
        margin: 0 auto;
      }
      
      .table td.teamName {
        text-align: center;
        vertical-align: middle;
      }
      
    </style>

  </head>

  <body>
    <div class="stuff">
  <?php 
    
    include 'stats.php';
    $table = $table = efficiency($pdo->query('SELECT * FROM point WHERE line = "D"')->fetchAll(PDO::FETCH_ASSOC));
    
    $currTourney = null;
    $currOpp = null;
    
    foreach($table as $row)
    {
      if ($currOpp != $row[1])
      {
        if ($currTourney != $row[0])
        {
          if ($currTourney)
          {
            ?>
              </tbody>
            </table>
            <?php           
          }
        
          ?>
          <h2><?php echo $row[0] ?></h2>
          <table class="table">
            <thead>
              <th>Player</th>
              <th>Ds</th>
              <th>Points</th>
              <th>%</th>
            </thead>
            <tbody>
          <?php 
        }
      
        ?>
        <tr><td class="teamName" colspan=6><h3><?php echo $row[1] ?></h3></td></tr>
        <?php      
      }

      $currTourney = $row[0];
      $currOpp = $row[1];
      
  ?>
    <tr>
      <td><?php echo $row[2] ?></td>
      <td><?php echo $row[3] ?></td>      
      <td><?php echo $row[4] ?></td>
      <td><?php echo $row[5] ?></td>
    </tr>

  <?php
    }
  ?>
      </tbody>
    </table>
    </div>
  </body>
</html>
