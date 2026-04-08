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

    const URL =  'https://test.realtycalendar.ru/v2/integrations/rentysoft/xml_feed?token=';

    public function fetch()
    {
        try {
              $response = Http::get($this::URL . auth()->user()->realty_key);
            //$response = Http::get($this::URL . 'y4qVxZQNvQRw4KrEVZb3sxKZCnVtV0lA');


            if (!$response->successful()) {
                // throw new \Exception("Failed to fetch XML from {$url}, status: " . $response->status());
                return ['status' => false, 'count' => 0, 'error' => 'Не удалось получить адрес списка'];
            }

            $xmlString = $response->body();
            
            if (!$xmlString) {
                // throw new \Exception("Failed to fetch XML , status: " . $response->status());
                return ['status' => false, 'count' => 0, 'error' => 'Не удалось получить адрес списка'];
            }
           
            $response = Http::get($xmlString);

            if (!$response->successful()) {
                // throw new \Exception("Failed to fetch XML from {$url}, status: " . $response->status());
                return ['status' => false, 'count' => 0, 'error' => 'Не удалось получить xml'];
            }

            $xmlString = $response->body();

            
            return $this->parse($xmlString);
        } catch (\Exception $e) {
            Log::error('Error fetching or processing XML: ' . $e->getMessage());
            return ['status' => false, 'count' => 0];
        }
    }

    private function parse(string $xmlString)
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString);

        if ($xml === false) {
            $errors = libxml_get_errors();
            return ['status' => false, 'count' => 0];
            throw new \Exception('Invalid XML format: ' . collect($errors)->pluck('message')->join(', '));
        }


        // Просто ищем все теги <offer>, без namespace
        $xml->registerXPathNamespace('y', 'http://webmaster.yandex.ru/schemas/feed/realty/2010-06');
        // Теперь используем префикс в запросе
        $offers = $xml->xpath('//y:offer');


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
                    'timezone' => $timezone,
                    'user_id' => auth()->user()->id,
                    'name' => (string)$offer->title
                ]
            );
        }

        return ['status' => true, 'count' => count($offers)];
    }
}
