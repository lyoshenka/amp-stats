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

$couples = array(
  'Raha|Butter',
  'Devlin|Purifico',
  'Kelly|BenJ',
  'Diana|Grin',
  'Lexa|Sting',
  'Stacy|Bill',
  'Mel|Dan',
  'Jill|Furf',
);

var_export(calculateData($couples));

function calculateData($couples)
{
  $dataFile = '/home/grin/Desktop/Amp2013-stats.csv';
  $goals = array_fill_keys($couples, 0);

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

    if ($eventType != 'Offense' || $action != 'Goal')
    {
      continue;
    }

    $passer = trim($passer);
    $receiver = trim($receiver);
    if ($passer == 'Anonymous' || $receiver == 'Anonymous')
    {
      continue;
    }

    if (array_key_exists("$passer|$receiver", $goals))
    {
      $goals["$passer|$receiver"]++;
    }
    elseif (array_key_exists("$receiver|$passer", $goals))
    {
      $goals["$receiver|$passer"]++;
    }
  }

  return $goals;
}
