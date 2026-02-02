<?php

namespace Dru1x\ExpoPush\PushMessage;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use Dru1x\ExpoPush\Support\Collection;
use ValueError;

/**
 * A collection of PushMessage objects
 *
 * @extends Collection<int, PushMessage>
 */
final class PushMessageCollection extends Collection
{
    public function __construct(PushMessage ...$pushMessages)
    {
        $this->items = $pushMessages;
    }

    /**
     * Count the number of notifications that would be sent by this set of PushMessages
     *
     * PushMessage objects can each have multiple recipients, and each of these would result in a single notification.
     * This method takes this into account and counts the total number of resultant notifications.
     *
     * @return int
     */
    public function notificationCount(): int
    {
        return array_reduce($this->items, static function (int $count, PushMessage $message): int {
            return $count + ($message->to instanceof PushTokenCollection ? $message->to->count() : 1);
        }, 0);
    }

    /**
     * Break this collection into smaller chunks where the total notification count doesn't exceed the given size
     *
     * PushMessage objects can each have multiple recipients, and each of these would result in a single notification.
     * This method takes this into account and splits the messages into multiple chunks where the total number of
     * resultant notifications does not exceed the given size.
     *
     * @param int $size
     *
     * @return array<array-key, PushMessageCollection>
     */
    public function chunkByNotifications(int $size): array
    {
        if ($size < 1) {
            throw new ValueError('Size must be greater than 0');
        }

        $chunks            = [[]];
        $currentChunkIndex = 0;

        foreach ($this->items as $pushMessage) {

            // Calculate how much space is left in the current chunk
            $currentChunkCapacity = $size - count($chunks[$currentChunkIndex]);

            // If the current chunk is full, create a new one
            if ($currentChunkCapacity < 1) {
                $chunks[] = [];
                $currentChunkIndex++;
                $currentChunkCapacity = $size;
            }

            // Calculate how many notifications the current push message will send
            $notificationCount = $pushMessage->to instanceof PushTokenCollection ? $pushMessage->to->count() : 1;

            // If the current push message fits in the current chunk, add it
            if ($notificationCount <= $currentChunkCapacity) {
                $chunks[$currentChunkIndex][] = $pushMessage;
                continue;
            }

            // Split the message into separate messages if the recipient count is greater than the remaining capacity
            if ($notificationCount >= 2 && $pushMessage->to instanceof PushTokenCollection) {

                // Get a copy of the recipient list
                $allRecipients = $pushMessage->to->all();

                // Fill the current chunk with the first recipients
                $chunks[$currentChunkIndex][] = $pushMessage->copy(
                    to: new PushTokenCollection(...array_splice($allRecipients, 0, $currentChunkCapacity))
                );

                // Start a new chunk
                $chunks[] = [];
                $currentChunkIndex++;

                // Break the remaining recipients into chunks no larger than the given size
                foreach (array_chunk($allRecipients, $size) as $recipientSet) {
                    $chunks[$currentChunkIndex][] = $pushMessage->copy(
                        to: new PushTokenCollection(...$recipientSet)
                    );

                    // If the current chunk is now full, start a new one
                    if (count($recipientSet) >= $size) {
                        $chunks[] = [];
                        $currentChunkIndex++;
                    }
                }
            }
        }

        // Convert the chunks into PushMessageCollection objects
        return array_map(fn(array $chunk) => new self(...$chunk), $chunks);
    }

    /**
     * Get an ordered collection of all push tokens used by the push messages in this collection
     *
     * @return PushTokenCollection
     */
    public function getTokens(): PushTokenCollection
    {
        $extractPushTokens = fn(array $carry, PushMessage $message) => array_merge($carry,
            $message->to instanceof PushToken ? [$message->to] : $message->to->all()
        );

        return new PushTokenCollection(
            ...array_reduce($this->items, $extractPushTokens, [])
        );
    }
}