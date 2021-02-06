<?php

use Sabre\VObject;

/**
 * This block requests a shared calendar from an external URL and parses the next events into a usable form.
 * It uses the builtin Kirby cache to limit the amount of requests to the external calendar source.
 * It picks summary, start and end time/date and the url from each calendar entry and hands them to
 * a Kirby template.
 */

$eventArray = array();

// UI parameters
$cacheActive = $block->cache()->toBool();
$cacheDuration = $block->cacheduration()->toInt();
$eventAmount = $block->eventamount()->toInt();
$sourceUrl = $block->source();
$withinDays = $block->withindays()->toInt();
$sortAscending = ($block->order() == 'ascending');
$blockId = $block->id();
$cachedContent = null;


if ($cacheActive) {
  // Hash all settings to use as cache key
  $hash = hash('md5', $blockId . $sourceUrl . $eventAmount . $withinDays . $sortAscending . $cacheDuration);
  $cache = $kirby->cache('preya.kirby-next-events-block');
  $cachedContent = $cache->get($hash);
}

if (!$cachedContent) {
  // Cache miss or no cache active

  // Curl Request
  $curlHandler = curl_init($sourceUrl);
  curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
  $calendar = curl_exec($curlHandler);
  $timezone = new DateTimeZone('Europe/Berlin');

  if ($calendar) {
    try {
      // Parse Calendar
      $vcalendar = VObject\Reader::read($calendar);
      $now = new DateTime();
      $interval = 'P' . $withinDays . 'D';
      $endDateWithin = (new DateTime())->add(new DateInterval($interval));
      // Expand recurring events to be looped over
      $expandedVCalendar = $vcalendar->expand($now, $endDateWithin, $timezone);
    } catch (Exception $e) {
      $expandedVCalendar = array();
    }
  }

  if ($expandedVCalendar) {
    foreach ($expandedVCalendar->VEVENT as $event) {
      $localStartTime = $event->DTSTART->getDateTime()->setTimezone($timezone);
      $localEndTime = $event->DTEND->getDateTime()->setTimezone($timezone);

      array_push($eventArray, array(
        'summary' => (string) $event->SUMMARY,
        'startTs' => $localStartTime->getTimestamp(),
        'startDateString' => (string) strftime("%a, %e.%m.", $localStartTime->getTimestamp()),
        'startTimeString' => (string) $localStartTime->format('G:i'),
        'endTimeString' => (string) $localEndTime->format('G:i'),
        'url' => (string) $event->URL
      ));
    }


    // Sort by time
    uasort($eventArray, fn ($a, $b) => $a['startTs'] <=> $b['startTs']);

    // Only save relevant entries
    $eventArray = array_slice($eventArray, 0, $eventAmount);

    if (!$sortAscending) {
      $eventArray = array_reverse($eventArray);
    }
  }
  if ($cacheActive) {
    $cache->set($hash, $eventArray, $cacheDuration);
  }
} else {
  // Cache hit
  $eventArray = $cachedContent;
}
?>

<ul class="k-block-type-next-events next-events-container">
  <?php foreach ($eventArray as $event) : ?>
    <li class="next-events-item">
      <div><?= $event['startDateString'] ?></div>
      <div class="text-right">
        <?php if ($event['url']) : ?>
          <a href="<?= $event['url'] ?>" class="undecorated-link">
          <?php endif ?>
          <strong>
            <?= $event['summary'] ?>
          </strong>
          <?php if ($event['url']) : ?>
            <div class="icon baseline">
              <?php include("assets/img/open-in-new-24px.svg"); ?>
            </div>
          </a>
        <?php endif ?><br />
        <span class="small">
          <?= $event['startTimeString'] ?> - <?= $event['endTimeString'] ?> Uhr
        </span>
      </div>
    </li>
  <?php endforeach ?>
</ul>