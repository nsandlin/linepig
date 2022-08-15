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
                new MultimediaDetailNotification('Testing two random LinEpig mm detail pages.')
            );

        $multimedia = new Multimedia();
        $records = $multimedia->getRecords();
        $this->assertNotEmpty($records);

        if (empty($records)) {
            Notification::route('slack', env('SLACK_HOOK'))
                ->notify(
                    new MultimediaDetailNotification("Couldn't get records from local sqlite.")
                );

            return 1;
        }

        // Test 2 random pages.
        $records = $records->toArray();
        $randomPages = array_rand($records, 2);
        $recordsToTest = [];
        
        foreach($randomPages as $randoKey) {
            $recordsToTest[] = $records[$randoKey];
        }

        foreach ($records as $record) {
            sleep(1);
            $irn = $record->irn;
            $url = "https://linepig.fieldmuseum.org/multimedia/$irn";
            $response = $this->get($url);
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

        Notification::route('slack', env('SLACK_HOOK'))
        ->notify(
            new MultimediaDetailNotification(
                "LinEpig two random mm detail pages successfully tested!"
            )
        );
        return 0;
    }
}
