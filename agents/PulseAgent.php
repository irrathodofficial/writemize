<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class PulseAgent
{
    public function run(array $article, array $input, array &$logs): array
    {
        $logs[] = 'Pulse Agent: setting publishing rhythm and schedule.';

        $publishDate = new \DateTimeImmutable('today');
        $publishTime = \clean_text($input['publish_time'] ?? '09:00', 20);

        if (preg_match('/^\d{2}:\d{2}$/', $publishTime) === 1) {
            $publishDate = $publishDate->setTime((int) substr($publishTime, 0, 2), (int) substr($publishTime, 3, 2));
        }

        $article['scheduled_for'] = $publishDate->format('Y-m-d H:i:s');
        $article['pulse_status'] = 'daily cadence ready';

        return $article;
    }
}
