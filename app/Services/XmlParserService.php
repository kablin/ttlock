<?php


namespace App\Services;




use App\Models\Rent;
use App\Models\LockJob;
use App\Models\User;
use App\Models\CodePacket;
use App\Models\LocksToken;
use Carbon\Carbon;

use App\Models\LockEvent;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

class XmlParserService
{

    public function fetch(string $url)
    {
        try {
            $response = Http::get($url);

            if (!$response->successful()) {
               // throw new \Exception("Failed to fetch XML from {$url}, status: " . $response->status());
               return false;
            }

            $xmlString = $response->body();

            return $this->parse($xmlString);
        } catch (\Exception $e) {
            Log::error('Error fetching or processing XML: ' . $e->getMessage());
        }
    }

    private function parse(string $xmlString)
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);

        if ($xml === false) {
            $errors = libxml_get_errors();
            return false;
            //throw new \Exception('Invalid XML format: ' . collect($errors)->pluck('message')->join(', '));
        }

        // Просто ищем все теги <offer>, без namespace
        $offers = $xml->xpath('//offer');

        foreach ($offers as $offer) {
            $internalId = (string)$offer['internal-id'];

            $location = trim(
                (string)$offer->location->country . ' ' .
                (string)$offer->location->region . ' ' .
                (string)$offer->location->locality_name . ' ' .
                (string)$offer->location->address
            );

            $timezone = (int)$offer->location->timezone;

            Rent::updateOrCreate(
                ['internal_id' => $internalId],
                [
                    'location' => $location,
                    'timezone' => $timezone
                ]
            );
        }

        return true;
    }
}
