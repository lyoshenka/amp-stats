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

//import($pdo);
//continuation($pdo);

// offense
//$table = efficiency($pdo->query('SELECT * FROM point WHERE line = "O" and our_turns > 0')->fetchAll(PDO::FETCH_ASSOC));
//echo "On O points they played where we turned it at least once, how likely were we to force at least one turnover?\n";
//printTable($table, ['Tournament', 'Opponent', 'Player', 'Ds', 'Points', 'Percent']);

// defense 
//$table = efficiency($pdo->query('SELECT * FROM point WHERE line = "D"')->fetchAll(PDO::FETCH_ASSOC));
//echo "On D points they played, how likely were we to force at least one turnover?\n";
//printTable($table, ['Tournament', 'Opponent', 'Player', 'Ds', 'Points', 'Percent']);





function efficiency($data)
{
  $currTournament = null;
  $currOpponent = null;
  $currGame = null;
  $players = [];
  $tourneyPlayers = [];
  foreach($data as $row)
  {
    if ($currGame != $row['game_id'])
    {
      if ($currGame)
      {
        $tournaments[$currTournament][$currOpponent] = $players;
        $players = [];
      }
      if ($currTournament && $row['tournament'] != $currTournament)
      {
        $tournaments[$currTournament]['Overall'] = $tourneyPlayers;
        $tourneyPlayers = [];
      }
    
      $currTournament = $row['tournament'];
      $currOpponent = $row['opponent'];
      $currGame = $row['game_id'];
    }

    foreach(range(1,9) as $n)
    {
      if (!$row["p$n"])
      {
        continue;
      }
    
      if (!isset($players[$row["p$n"]]))
      {
        $players[$row["p$n"]] = ['points' => 0, 'ds' => 0];
      }
      if (!isset($tourneyPlayers[$row["p$n"]]))
      {
        $tourneyPlayers[$row["p$n"]] = ['points' => 0, 'ds' => 0];
      }
      
      $players[$row["p$n"]]['points']++;
      $tourneyPlayers[$row["p$n"]]['points']++;
      if ($row['their_turns'] > 0)
      {
        $players[$row["p$n"]]['ds']++;
        $tourneyPlayers[$row["p$n"]]['ds']++;      
      }
    }
  }
  
  $tournaments[$currTournament][$currOpponent] = $players;
  $tournaments[$currTournament]['Overall'] = $tourneyPlayers;


  $table = [];
  foreach($tournaments as $tourneyName => $opponents)
  {
    foreach($opponents as $oppName => $players)
    {
      uasort($players, function($a,$b) {
        $aPercent = $a['ds'] / $a['points'];
        $bPercent = $b['ds'] / $b['points'];
        if ($aPercent == $bPercent)
        {
          return $a['points'] == $b['points'] ? 0 : ($a['points'] > $b['points'] ? -1 : 1);
        }
        return $aPercent > $bPercent ? -1 : 1;
      });
  
      foreach($players as $player => $stats)
      {
        $table[] = [$tourneyName, $oppName, $player, $stats['ds'], $stats['points'], sprintf('%0.3f',$stats['ds']/$stats['points']) ];
      }
      //$table[] = ['---', '---', '---', '---', '---', '---'];
    }
    //$table[] = [' ', ' ', ' ', ' ', ' ', ' '];
    //$table[] = [' ', ' ', ' ', ' ', ' ', ' '];
    //$table[] = [' ', ' ', ' ', ' ', ' ', ' '];
  }
  
  return $table;
}


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

  $ret = $pdo->exec("CREATE TABLE IF NOT EXISTS `point` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `game_id` int(2),
    `point_in_game` int(2) NOT NULL,
    `timestamp` datetime NOT NULL,
    `tournament` varchar(50) NOT NULL,
    `opponent` varchar(50) NOT NULL,
    `seconds` int(2) NOT NULL,
    `line` varchar(1) NOT NULL,
    `our_score` int(1) NOT NULL,
    `their_score` int(1) NOT NULL,
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
    `our_turns` int(2),
    `their_turns` int(2),
    `we_scored` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
  
  if ($ret === false)
  {
    var_export($pdo->errorInfo());
  }
  
  $ret = $pdo->exec("CREATE TABLE IF NOT EXISTS `event` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `point_id` int(11) NOT NULL,
    `type` varchar(20) NOT NULL,
    `action` varchar(20) NOT NULL,
    `passer` varchar(25) DEFAULT NULL,
    `receiver` varchar(25) DEFAULT NULL,
    `defender` varchar(25) DEFAULT NULL,
    `hang_time` decimal(5,3) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `type_idx` (`type`),
    KEY `action_idx` (`action`),
    CONSTRAINT `event_point_id` FOREIGN KEY (`point_id`) REFERENCES `point` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

  if ($ret === false)
  {
    var_export($pdo->errorInfo());
  }

  $lastPointId = null;
  $lastPointKey = null;

  foreach($res as $row)
  {
    //if ($row['Tournamemnt'] != 'NY Invite') continue;

    //var_export ($row);
    
    if (!$row['Action']) 
    {
      continue;
    }

    $thisPointKey = $row['Date/Time'] . '-' . $row['Our Score - End of Point'] . '-' . $row['Their Score - End of Point'];
    if ($lastPointKey != $thisPointKey)
    {
      $lastPointKey = $thisPointKey;
      $pointInGame = $row['Our Score - End of Point'] + $row['Their Score - End of Point'];
      $sql = sprintf("INSERT INTO point
        (point_in_game, timestamp, tournament, opponent, seconds, line, our_score, their_score, p1, p2, p3, p4, p5, p6, p7, p8, p9, p10)
        VALUES (%d, '%s', '%s', '%s', %d, '%s', %d, %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", 
        $pointInGame, $row['Date/Time'], mysql_real_escape_string($row['Tournamemnt']), mysql_real_escape_string($row['Opponent']), 
        $row['Point Elapsed Seconds'], $row['Line'], $row['Our Score - End of Point'], $row['Their Score - End of Point'], $row['Player 0'], 
        $row['Player 1'], $row['Player 2'], $row['Player 3'], $row['Player 4'], $row['Player 5'], $row['Player 6'], $row['Player 7'], 
        $row['Player 8'], $row['Player 9']
      );
      //echo "$sql\n";
      if (!$pdo->exec($sql))
      {
        var_export($pdo->errorInfo());
      }
      $lastPointId = $pdo->lastInsertId();
    }

    $sql = sprintf("INSERT INTO event
      (point_id, type, action, passer, receiver, defender, hang_time)
      VALUES (%d, '%s', '%s', '%s', '%s', '%s', %s)", 
      $lastPointId, $row['Event Type'], $row['Action'], $row['Passer'], $row['Receiver'], $row['Defender'], $row['Hang Time (secs)'] ?: "NULL"
    );

    //echo "$sql\n";

    if (!$pdo->exec($sql)) 
    {
      var_export($pdo->errorInfo());
    }
  }
  
  // add game ids in the correct order
  $gameIds = $pdo->query("select group_concat(id) as ids, timestamp from point group by timestamp order by timestamp")->fetchAll(PDO::FETCH_ASSOC);
  $gameCount = 1;
  foreach($gameIds as $game)
  {
    $pdo->exec("UPDATE point SET game_id = " . $gameCount++ . " WHERE id IN (" . $game['ids'] . ")");  
  }
  $pdo->exec("ALTER TABLE point MODIFY COLUMN game_id int(2) NOT NULL");
  
  // reset point ids to be in the correct order too
  $pointIds = $pdo->query("select id from point order by game_id, point_in_game")->fetchAll(PDO::FETCH_ASSOC);
  $pointCount = 1001;
  foreach($pointIds as $pointId)
  {
    $pdo->exec("UPDATE point SET id = " . $pointCount++ . " WHERE id = " . $pointId['id']);
  }
  $pdo->exec("UPDATE point SET id = id - 1000");


  // reset event ids to be in order
  $eventIds = $pdo->query("select id from event order by point_id")->fetchAll(PDO::FETCH_ASSOC);
  $eventCount = 10001;
  foreach($eventIds as $eventId)
  {
    $pdo->exec("UPDATE event SET id = " . $eventCount++ . " WHERE id = " . $eventId['id']);
  }
  $pdo->exec("UPDATE event SET id = id - 10000");

return;
  
  // our turns per point
  $pdo->exec("update point p LEFT JOIN (select p.id, sum(if(e.type = 'Offense' and action in ('Drop','Stall','Throwaway','MiscPenalty'), 1, 0)) as turns from event e inner join point p on e.point_id = p.id group by p.id) x on p.id = x.id set p.our_turns = x.turns");
  // their turns per point
  $pdo->exec("update point p LEFT JOIN (select p.id, sum(if(e.type = 'Defense' and action in ('D','Throwaway'), 1, 0)) as turns from event e inner join point p on e.point_id = p.id group by p.id) x on p.id = x.id set p.their_turns = x.turns");
  
  // set we_scored
  $pdo->exec("update point p inner join event e on p.id = e.point_id and e.action = 'Goal' and e.type = 'Offense' set p.we_scored = 1;");

  // fix team name  
  $pdo->exec("update point set opponent = 'SHUYAMOUF' where opponent in ('Shuyomouf', 'Shuyamouf')");
  
  
  
  // queries
  
  // total possessions per game
  // select game_id, tournament, opponent, sum(their_turns + IF(line = 'O',1,0)) as our_possessions, concat(max(our_score),'-',max(their_score)) as final_score from point p group by game_id;
  
  // multiple O possessions
  // select id, tournament,opponent, their_turns+1 as possessions, IF(we_scored,'us','them') as who_scored, concat(our_score,'-',their_score) as score, p1, p2, p3, p4, p5, p6, p7, p8 from point where line = 'O' having possessions > 1;

  // one-turn O points
  // select id, tournament,opponent, concat(our_score,'-',their_score) as score, p1, p2, p3, p4, p5, p6, p7, p8 from point where line = 'O' and their_turns = 0 and our_turns = 1
  
}



function printTable($data, $columnNames = [])
{
  if (!$data || !is_array($data))
  {
    return;
  }

  $columns = getColumnsAndMaxLengths($columnNames ? array_merge($data, [$columnNames]) : $data);

  $header = join(' | ', array_map(function($key, $value) {
    return str_pad($key, $value);
  }, $columnNames ?: array_keys($columns), $columns));

  $hr = str_pad('', mb_strlen($header), '-');

  echo '--' . $hr . "--\n";
  echo '| ' . $header . " |\n";
  echo '|-' . $hr . "-|\n";

  foreach($data as $row)
  {
    $row = array_intersect_key($row, $columns); // only print columns that are set
    echo '| ' . join(' | ', array_map(function($value, $length) {
      return str_pad($value, $length);
    }, $row, $columns)) . " |\n";
  }

  echo '--' . $hr . "--\n";
}

/**
 * Get the lengths of each column so they can be lined up nicely for printing
 * @return array an array of key-value pairs where the key is the column title and the value is either the length of
 *               the longest string in that column or the length of the column title, whichever is longer
 */
function getColumnsAndMaxLengths($data)
{
  return array_reduce(

    # replace each value with the length of that value
    array_map(function($row) {
      return array_map(function($value) {
        return mb_strlen($value);
      }, $row);
    }, $data),

    # find the max length of each value (or the length of the column name) across all rows
    function($a, $b) {
      if (!$a) $a = $b;
      $ret = [];
      foreach (array_keys($a) as $key)
      {
        $ret[$key] = max(
          isset($a[$key]) ? $a[$key] : 0,
          isset($b[$key]) ? $b[$key] : 0,
          mb_strlen($key)
        );
      }
      return $ret;
    }
  );
}
