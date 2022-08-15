<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\MultimediaDetailNotification;
use App\Multimedia;

class MMAre200sTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_all_mm_pages_are_200()
    {
        Notification::route('slack', env('SLACK_HOOK'))
            ->notify(
                new MultimediaDetailNotification('Test LinEpig mm detail pages load properly')
            );

        $multimedia = new Multimedia();
        $records = $multimedia->getRecords();
        $this->assertNotEmpty($records);

        if (empty($records)) {
            Notification::route('slack', env('SLACK_HOOK'))
                ->notify(
                    new MultimediaDetailNotification("Couldn't get records from IMu (is it down?)")
                );

            return 1;
        }

        foreach ($records as $record) {
            sleep(1);
            $irn = $record->irn;
            $response = $this->get("https://linepig.fieldmuseum.org/multimedia/$irn");
            $statusCode = $response->getStatusCode();
            $response->assertStatus(200);

            if ($statusCode !== 200) {
                Notification::route('slack', env('SLACK_HOOK'))
                ->notify(
                    new MultimediaDetailNotification("LinEpig mm NOT LOADING, IMu down?")
                );

                return 1;
            }
        }
    }
}
