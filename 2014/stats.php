#!/usr/bin/env php

<?php

require_once 'vendor/autoload.php';

use League\Csv\Reader;

const DBHOST = 'localhost';
const DBNAME = 'amp';
const DBPORT = '3306';
const DBUSER = 'root';
const DBPASS = 'FILL-ME-IN';

$pdo = new PDO('mysql:dbname='.DBNAME.';host='.DBHOST.';port='.DBPORT,DBUSER,DBPASS);

import($pdo);
//continuation($pdo);

function continuation($pdo)
{
  $data = $pdo->query('SELECT * FROM event WHERE tournament = "NY Invite" and type = "Offense" order by timestamp')->fetchAll(PDO::FETCH_ASSOC);

  $handlerOrder = [
    13 => 'Miggs',
    10 => 'Owen',
    22 => 'Charlie',
    16 => 'Kelly',
    9 => 'Sara',
    4 => 'Ben P',
    15 => 'Gabe',
    18 => 'Alex',
    26 => 'Bulb',
    2 => 'Raha',
    25 => 'Katie',
    0 => 'Diana',
    1 => 'Bill',
    14 => 'Panna',
    21 => 'Trey',
    8 => 'Grin',
    5 => 'Melanie',
    17 => 'Zumba',
    11 => 'Dre',
    3 => 'Lexa',
    6 => 'Papa',
    12 => 'Jessie',
    19 => 'Glazer',
    20 => 'Devlin',
    23 => 'Purifico',
    24 => 'Furf',
  ];

  $count = 0;
  $allThrowsCount = 0;

  foreach($data as $row)
  {
    $throwActions = ['Catch', 'Drop', 'Goal'];
    if (!in_array($row['action'], $throwActions))
    {
      continue;
    }

    $allThrowsCount++;

    $players = [];
    foreach(range(1,10) as $player)
    {
      $players[] = $row["p$player"];
    }
    $players = array_filter($players);

    $handlersThisPoint = [];
    foreach($handlerOrder as $handler)
    { 
      if (in_array($handler, $players))
      {
        $handlersThisPoint[] = $handler;
      }
      if (count($handlersThisPoint) >= 3)
      {
        break;
      }
    }

    if (!in_array($row['passer'], $handlersThisPoint) && !in_array($row['receiver'], $handlersThisPoint))
    {
      $count++;
      echo $row['passer'] . ' => ' . $row['receiver'] . ' (' . join(',',$handlersThisPoint) .  ")\n";
    }
  }

  echo $count . '/' . $allThrowsCount . ' cutter-to-cutter throws'. "\n";
}


function import($pdo)
{
  $csv = new Reader('AMP2014-stats.csv');
  
  $headers = $csv->fetchOne();
  
  $res = $csv
    ->setOffset(1)
  //  ->setLimit(1)
    ->fetchAssoc($headers);
  
  $pdo->exec("CREATE TABLE IF NOT EXISTS `event` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `game_id` int(2),
    `point_id` int(2),
    `point_in_game` int(2),
    `timestamp` datetime NOT NULL,
    `tournament` varchar(50) NOT NULL,
    `opponent` varchar(50) NOT NULL,
    `seconds` int(2) NOT NULL,
    `line` varchar(1) NOT NULL,
    `our_score` int(1) NOT NULL,
    `their_score` int(1) NOT NULL,
    `type` varchar(20) NOT NULL,
    `action` varchar(20) NOT NULL,
    `passer` varchar(25) DEFAULT NULL,
    `receiver` varchar(25) DEFAULT NULL,
    `defender` varchar(25) DEFAULT NULL,
    `p1` varchar(25) NOT NULL,
    `p2` varchar(25) NOT NULL,
    `p3` varchar(25) NOT NULL,
    `p4` varchar(25) NOT NULL,
    `p5` varchar(25) NOT NULL,
    `p6` varchar(25) NOT NULL,
    `p7` varchar(25) NOT NULL,
    `p8` varchar(25) DEFAULT NULL,
    `p9` varchar(25) DEFAULT NULL,
    `p10` varchar(25) DEFAULT NULL,
    `hang_time` decimal(5,3) DEFAULT NULL,
    `females` int(1) DEFAULT NULL,
    `players` int(1),
    PRIMARY KEY (`id`),
    KEY `type_idx` (`type`),
    KEY `action_idx` (`action`),
    KEY `timestamp_idx` (`timestamp`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
  
  
  foreach($res as $row)
  {
    //if ($row['Tournamemnt'] != 'NY Invite') continue;

    //var_export ($row);

    $sql = sprintf("INSERT INTO event
    (timestamp, tournament, opponent, seconds, line, our_score, their_score, type, action, passer, receiver,
    defender, p1, p2, p3, p4, p5, p6, p7, p8, p9, p10, hang_time)
    VALUES ('%s', '%s', '%s', %d, '%s', %d, %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s)", 
    $row['Date/Time'], $row['Tournamemnt'], $row['Opponent'], $row['Point Elapsed Seconds'], $row['Line'], $row['Our Score - End of Point'], 
    $row['Their Score - End of Point'], $row['Event Type'], $row['Action'], $row['Passer'], $row['Receiver'], $row['Defender'], $row['Player 0'], 
    $row['Player 1'], $row['Player 2'], $row['Player 3'], $row['Player 4'], $row['Player 5'], $row['Player 6'], $row['Player 7'], 
    $row['Player 8'], $row['Player 9'], $row['Hang Time (secs)'] ?: "NULL"
    );

    //echo "$sql\n";

    if (!$pdo->exec($sql)) 
    {
      var_export($pdo->errorInfo());
    }
  }
}
