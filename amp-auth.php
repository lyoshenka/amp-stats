<?php


$pdo = new PDO('mysql:dbname=d192f356bebeb43539deb44b7b690eae2;host=127.0.0.1;port=10000','uZQVKDjzK8nvh','p0UCH7QrdKyZ6');

//list($stats, $statsPerPoint) = stats("timestamp > '2013-07-10'");
// list($stats, $statsPerPoint) = stats();
// var_export([$stats['Grin'], $statsPerPoint['Grin']]);


function throwCharts()
{
  $throwData = query('SELECT passer, receiver, action FROM event ' .
    'WHERE passer IS NOT NULL AND receiver IS NOT NULL AND receiver != "Anonymous" AND passer != "Anonymous" ' .
    'AND timestamp > "2013-07-10"');

  $allNames = [];
  foreach($throwData as $row)
  {
    $allNames[] = $row['passer'];
    $allNames[] = $row['receiver'];
  }
  $allNames = array_unique($allNames);
  sort($allNames);

  $goals = $drops = $throws = array_fill_keys($allNames, array_fill_keys($allNames, 0));

  foreach($throwData as $row)
  {
    $throws[$row['passer']][$row['receiver']]++;
    if ($row['action'] == 'Goal')
    {
      $goals[$row['passer']][$row['receiver']]++;
    }
    if ($row['action'] == 'Drop')
    {
      $drops[$row['passer']][$row['receiver']]++;
    }
  }

  return [$throws, $goals, $drops];
}

function query($query)
{
  global $pdo;
  $results = $pdo->query($query);
  if ($results === false)
  {
    var_export($pdo->errorInfo());
    die('PDO ERROR');
  }
  return $results->fetchAll();
}

function stats($where = null)
{

  // actions: goal, catch, drop, pull, pullob, throwaway, d
  // type: offense, defense. this is whether our team has the disc or not
  // line: o, d. this is whether we started the point on O or D
  // passer, receiver, defender


  if (!$where)
  {
    $where = 'TRUE';
  }

  $recStatsQuery = "SELECT receiver AS name, " .
    "SUM(IF(action = 'drop', 1, 0)) as drops, " .
    "SUM(IF(action = 'goal', 1, 0)) as goals, " .
    "SUM(IF(action IN ('catch','goal'), 1, 0)) as catches " .
    "FROM event WHERE type = 'Offense' AND $where GROUP BY receiver";

  $passStatsQuery = "SELECT passer AS name, " .
    "SUM(IF(action = 'throwaway', 1, 0)) as throwaways, " .
    "SUM(IF(action = 'goal', 1, 0)) as assists, " .
    "SUM(IF(action IN ('catch','goal','throwaway','drop'), 1, 0)) as throws " .
    "FROM event WHERE type = 'Offense' AND $where GROUP BY passer";

  $defStatsQuery = "SELECT defender AS name, " .
    "SUM(IF(action IN ('pull','pullob'), 1, 0)) as pulls, " .
    "SUM(IF(action = 'pullob', 1, 0)) as ob_pulls, " .
    "SUM(IF(action = 'd', 1, 0)) as ds, " .
    "SUM(IF(action = 'pull', hang_time, 0)) as total_hang_time " .
    "FROM event WHERE type = 'Defense' AND $where GROUP BY defender";

  $gamesPointsQuery = "SELECT p.name AS name, " .
    "COUNT(DISTINCT e.game_id) as games, " .
    "COUNT(DISTINCT CONCAT(e.game_id, '-', e.point_id)) as points " .
    "FROM player p " .
    "LEFT JOIN event e ON p.name IN (e.p1,e.p2,e.p3,e.p4,e.p5,e.p6,e.p7,e.p8,e.p9,e.p10) ".
    "WHERE $where GROUP BY p.name";


  $ppQuery = 

  $allStats = ['+/-','games','points','goals','assists','ds','throwaways','drops','throws','catches','touches',
  'pulls','ob_pulls','avg_hang_time'];
  $perPointStats = ['touches', 'goals', 'assists', 'throws', 'catches', 'throwaways', 'drops', 'ds', '+/-'];

  $playerData = query('SELECT name, gender FROM player ORDER BY name ASC');
  foreach($playerData as $row)
  {
    $stats[$row['name']] = array_merge(['gender' => $row['gender']] , array_fill_keys($allStats, 0));
    $statsPerPoint[$row['name']] = array_fill_keys($perPointStats, 0);
  }


  // $pdo->query($defStatsQuery);
  // var_export($pdo->errorInfo());
  // die();

  $recStats = query($recStatsQuery);
  $passStats = query($passStatsQuery);
  $defStats = query($defStatsQuery);
  $gamesPoints = query($gamesPointsQuery);

  foreach($recStats as $row)
  {
    if ($row['name'] == 'Anonymous') continue;
    $stats[$row['name']]['drops'] = $row['drops'];
    $stats[$row['name']]['goals'] = $row['goals'];
    $stats[$row['name']]['catches'] = $row['catches'];
  }
  foreach($passStats as $row)
  {
    if ($row['name'] == 'Anonymous') continue;
    $stats[$row['name']]['assists'] = $row['assists'];
    $stats[$row['name']]['throwaways'] = $row['throwaways'];
    $stats[$row['name']]['throws'] = $row['throws'];
  }
  foreach($defStats as $row)
  {
    if ($row['name'] == 'Anonymous') continue;
    $stats[$row['name']]['pulls'] = $row['pulls'];
    $stats[$row['name']]['ob_pulls'] = $row['ob_pulls'];
    $stats[$row['name']]['ds'] = $row['ds'];
//    $stats[$row['name']]['total_hang_time'] = $row['total_hang_time'];
    $stats[$row['name']]['avg_hang_time'] =
      $row['pulls'] > $row['ob_pulls'] ?
      sprintf('%.2f', $row['total_hang_time'] / ($row['pulls'] - $row['ob_pulls'])) :
      0;
  }
  foreach($gamesPoints as $row)
  {
    if ($row['name'] == 'Anonymous') continue;
    $stats[$row['name']]['games'] = $row['games'];
    $stats[$row['name']]['points'] = $row['points'];
  }

  foreach(array_keys($stats) as $name)
  {
    if ($stats[$name]['points'] == 0)
    {
      unset($stats[$name]);
      continue;
    }

    $stats[$name]['touches'] = $stats[$name]['throws'] + $stats[$name]['goals']; // + times you caught the disc but were subbed out. how do you find that?

    $stats[$name]['+/-'] = $stats[$name]['goals'] + $stats[$name]['assists'] +
                            $stats[$name]['ds'] - $stats[$name]['throwaways'] - $stats[$name]['drops'];

    foreach($perPointStats as $key)
    {
      $statsPerPoint[$name][$key] =
        $stats[$name]['points'] ?
        sprintf('%.2f', $stats[$name][$key] / $stats[$name]['points']) :
        0;
    }
  }

  return [$stats, $statsPerPoint];
}

function printTable($data, $columnNames = [])
{
  if (!$data || !is_array($data))
  {
    return;
  }

  $columns = _getColumnsAndMaxLengths($data);

  $header = join(' | ', array_map(function($key, $value) {
    return myString::mb_str_pad($key, $value);
  }, $columnNames ?: array_keys($columns), $columns));

  $hr = myString::mb_str_pad('', mb_strlen($header), '-');

  echo '--' . $hr . "--\n";
  echo '| ' . $header . " |\n";
  echo '|-' . $hr . "-|\n";

  foreach($data as $row)
  {
    $row = array_intersect_key($row, $columns); // only print columns that are set
    echo '| ' . join(' | ', array_map(function($value, $length) {
      return myString::mb_str_pad($value, $length);
    }, $row, $columns)) . " |\n";
  }

  echo '--' . $hr . "--\n";
}

/**
 * Get the lengths of each column so they can be lined up nicely for printing
 * @return array an array of key-value pairs where the key is the column title and the value is either the length of
 *               the longest string in that column or the length of the column title, whichever is longer
 */
function _getColumnsAndMaxLengths($data)
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
      foreach ($a as $key => $value)
      {
        $ret[$key] = max($a[$key], $b[$key], mb_strlen($key));
      }
      return $ret;
    }
  );
}