#!/usr/bin/php
<?php

set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() == 0) {
    return;
  }
  if (error_reporting() & $severity) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
  }
}

$genders = array(
  'Raha' => 'f',
  'Devlin' => 'f',
  'Katie' => 'f',
  'Kelly' => 'f',
  'Weeks' => 'f',
  'Diana' => 'f',
  'Lexa' => 'f',
  'Stacy' => 'f',
  'Mel' => 'f',
  'Jessie' => 'f',
  'Maddie' => 'f',
  'Jill' => 'f',
  'Dre' => 'f',
  'Krista' => 'f',

  'Ben' => 'm',
  'Grin' => 'm',
  'Birdo' => 'm',
  'Alex' => 'm',
  'Zumba' => 'm',
  'Panasci' => 'm',
  'Bill' => 'm',
  'Butter' => 'm',
  'Jesse' => 'm',
  'Satell' => 'm',
  'Tommy' => 'm',
  'Sting' => 'm',
  'Adam' => 'm',
  'BenJ' => 'm',
  'Wheez' => 'm',
  'Bulb' => 'm',
  'Garret' => 'm',
  'H' => 'm',
  'Jeff' => 'm',
  'Nico' => 'm',
  'Charles' => 'm',
  'Papa' => 'm',
  'Dan' => 'm',
  'Miggs' => 'm',
  'Purifico' => 'm',
  'Panna' => 'm',
  'Furf' => 'm'
);

$roles = array(
  'Raha' => 'm',
  'Devlin' => 'c',
  'Katie' => 'h',
  'Kelly' => 'h',
  'Weeks' => 'c',
  'Diana' => 'm',
  'Lexa' => 'c',
  'Stacy' => 'c',
  'Mel' => 'h',
  'Jessie' => 'c',
  'Maddie' => 'c',
  'Jill' => 'c',
  'Dre' => 'c',
  'Krista' => 'c',

  'Ben' => 'h',
  'Grin' => 'c',
  'Birdo' => 'c',
  'Alex' => 'h',
  'Zumba' => 'm',
  'Panasci' => 'h',
  'Bill' => 'm',
  'Butter' => 'c',
  'Jesse' => 'c',
  'Satell' => 'h',
  'Tommy' => 'c',
  'Sting' => 'h',
  'Adam' => 'm',
  'BenJ' => 'c',
  'Wheez' => 'm',
  'Bulb' => 'h',
  'Garret' => 'm',
  'H' => 'h',
  'Jeff' => 'm',
  'Nico' => 'h',
  'Charles' => 'm',
  'Papa' => 'm',
  'Dan' => 'm',
);

$throws = calculateData($genders);
analyzeData($genders, $throws);

function analyzeData($genders, $throws)
{
  $do43 = true;

  $analysis = array();

  foreach($throws as $thrower => $data)
  {
    if ($genders[$thrower] == 'f')
    {
      $expected43MRatio = 4/6;
      $expected43FRatio = 2/6;
      $expected34MRatio = 3/6;
      $expected34FRatio = 3/6;
    }
    else
    {
      $expected43MRatio = 3/6;
      $expected43FRatio = 3/6;
      $expected34MRatio = 2/6;
      $expected34FRatio = 4/6;
    }

    $throwsIn43 = $data['m-43'] + $data['f-43'];
    $throwsIn34 = $data['m-34'] + $data['f-34'];

    if ($do43)
    {
      if ($throwsIn43 < 5)
      {
        continue;
      }

      $analysis[]= array(
        'thrower' => $thrower . ' (' . $genders[$thrower] . ')',
  //      '4/3 M' => formatNum($data['m-43'] / ($throwsIn43)),
        '4/3 Throws' => $data['m-43'] + $data['f-43'],
        '4/3 F' => formatNum($data['f-43'] / ($throwsIn43)),
        '4/3 F Exp' => formatNum($expected43FRatio),
        '4/3 Diff' => sprintf('%+.3f', ($data['f-43'] / ($throwsIn43) - $expected43FRatio)),
  //      '3/4 M' => formatNum($data['m-34'] / ($throwsIn34)),
      );
    }
    else
    {
      if ($throwsIn34 < 5)
      {
        continue;
      }

      $analysis[]= array(
        'thrower' => $thrower . ' (' . $genders[$thrower] . ')',
  //      '3/4 M' => formatNum($data['m-34'] / ($throwsIn34)),
        '3/4 Throws' => $data['m-34'] + $data['f-34'],
        '3/4 F' => $throwsIn34 ? formatNum($data['f-34'] / ($throwsIn34)) : '0',
        '3/4 F Exp' => formatNum($expected34FRatio),
        '3/4 Diff' => $throwsIn34 ? sprintf('%+.3f', ($data['f-34'] / ($throwsIn34) - $expected34FRatio)) : 0,
      );
    }
  }

  $sortField = $do43 ? '4/3 Diff' : '3/4 Diff';

  uasort($analysis, function($a,$b) use($sortField) {
    $aa = (float)$a[$sortField];
    $bb = (float)$b[$sortField];
    return $aa == $bb ? 0 : ($aa > $bb ? -1 : 1);
  });

  printTable($analysis);
}

function formatNum($num)
{
  return sprintf('%.3f', $num);
}

function calculateData($genders)
{
  $dataFile = '/home/grin/Desktop/Amp2013-stats.csv';
  $throws = array_fill_keys(array_keys($genders), array('m-43' => 0, 'm-34' => 0, 'f-43' => 0, 'f-34' => 0));

  foreach (explode("\n", file_get_contents($dataFile)) as $line)
  {
    if (!$line)
    {
      continue;
    }

    @list($date, $event, $opponent, $seconds, $od, $ourScoreAfterPoint,$theirScoreAfterPoint,$eventType,$action,$passer,$receiver,$defender,
      $player1,$player2,$player3,$player4,$player5,$player6,$player7,$player8) = str_getcsv($line);

    if ($date == 'Date/Time')
    {
      continue; // the header
    }

    if ($player8 || !$player7)
    {
      continue; // skip lines with too many or too few players
    }

    if ($eventType != 'Offense')
    {
      continue;
    }

    $passer = trim($passer);
    $receiver = trim($receiver);
    if ($passer == 'Anonymous' || $receiver == 'Anonymous')
    {
      continue;
    }

    $playersThisPoint = array();
    foreach(range(1,7) as $num)
    {
      $pvar = 'player'.$num;
      $$pvar = trim($$pvar);
      $playersThisPoint[] = $$pvar;
    }

    if (in_array($action, array('Catch','Drop','Goal')))
    {
      if (!$receiver)
      {
        echo $line."\n";
      }
      $receiverGender = $genders[$receiver];
      $throws[$passer][$receiverGender.'-'.ratio($genders, $playersThisPoint)]++;
    }
  }

  return $throws;
}

function ratio($genders, $players)
{
  $fCount = 0;
  foreach($players as $player)
  {
    if ($genders[$player] == 'f')
    {
      $fCount++;
    }
  }

  return $fCount == 3 ? '43' : '34';
}

  function printTable($data, $columnNames = array())
  {
    $columns = getColumnsAndMaxLengths($data);

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
        $ret = array();
        foreach ($a as $key => $value)
        {
          $ret[$key] = max($a[$key], $b[$key], mb_strlen($key));
        }
        return $ret;
      });
  }
