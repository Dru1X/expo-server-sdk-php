<?php

namespace Collections;

use Dru1x\ExpoPush\Collections\PushTicketCollection;
use Dru1x\ExpoPush\Data\PushTicket;
use Dru1x\ExpoPush\Enums\PushStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PushTicketCollectionTest extends TestCase
{
    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushTicketCollection(
            new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        foreach ($collection as $ticket) {
            $this->assertInstanceOf(PushTicket::class, $ticket);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_ticket_exists(): void
    {
        $ticket1 = new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $ticket2 = new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');
        $ticket3 = new PushTicket(status: PushStatus::Ok, receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ');

        $collection = new PushTicketCollection($ticket1, $ticket2, $ticket3);

        $this->assertTrue($collection->contains($ticket1));
        $this->assertTrue($collection->contains($ticket2));
    }

    #[Test]
    public function contains_returns_false_when_push_ticket_doesnt_exist(): void
    {
        $ticket1 = new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $ticket2 = new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');
        $ticket3 = new PushTicket(status: PushStatus::Ok, receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ');

        $collection = new PushTicketCollection($ticket1, $ticket2);

        $this->assertFalse($collection->contains($ticket3));
    }

    #[Test]
    public function get_returns_correct_push_ticket(): void
    {
        $ticket1 = new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $ticket2 = new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $collection = new PushTicketCollection($ticket1, $ticket2);

        $ticket = $collection->get(1);

        $this->assertEquals($ticket2, $ticket);
    }

    #[Test]
    public function get_returns_null_if_push_ticket_not_found(): void
    {
        $collection = new PushTicketCollection(
            new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        );

        $ticket = $collection->get(99);

        $this->assertNull($ticket);
    }

    #[Test]
    public function count_returns_correct_push_ticket_count(): void
    {
        $collection = new PushTicketCollection(
            new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushTicketCollection(
            new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'),
        );

        $chunks = $collection->chunk(2);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(2, $chunk);
        }
    }

    #[Test]
    public function to_array_returns_push_ticket_array(): void
    {
        $collection = new PushTicketCollection(
            new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $ticket) {
            $this->assertInstanceOf(PushTicket::class, $ticket);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushTicketCollection(
            new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $this->assertJsonStringEqualsJsonString(
            '[{"status": "ok", "receiptId": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"}, {"status": "ok", "receiptId": "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY"}, {"status": "ok", "receiptId": "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ"}]',
            json_encode($collection),
        );
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushTicketCollection(
            new PushTicket(status: PushStatus::Ok, receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new PushTicket(status: PushStatus::Ok, receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $this->assertJsonStringEqualsJsonString(
            '[{"status": "ok", "receiptId": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"}, {"status": "ok", "receiptId": "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY"}, {"status": "ok", "receiptId": "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ"}]',
            $collection->toJson(),
        );
    }
}