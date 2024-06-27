<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\Request as SheetsRequest;
use Google\Service\Sheets\ValueRange;
use Illuminate\Http\Request;

class SheetController extends Controller
{
    //
    public function __construct()
    {
        
    }
    public function handle()
    {
        // dd(gettype(now()->format('m-Y')));
        $client = $this->initializeGoogleClient();

        $sheetId = '1xBnAbZRCbD1szehaAA1HBlJie7y9F9RNqVbHPjpvf-8';

        $sheetTitle = now()->addMonths(3)->format('m-Y');

        $this->addSheetHasFormatHeader($sheetId, $client, $sheetTitle);

        // $formData = [
        //     'truong',
        //     'mail@gmail.com',
        //     'hello wine',
        //     '10/09/1997'
        // ];

        // $this->addDataToSheet($client, $sheetId, $sheetTitle, $formData);
        
    }

    public function addDataToSheet(Client $client, $spreadsheetId, $sheetTitle, $formData)
    {
        $service = new Sheets($client);

        // Tìm hàng trống tiếp theo
        $range = $sheetTitle . '!A:A';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $value = $response->getValues();
        $nextRow = count($value) + 1;

        // Thêm dữ liệu vào hàng tiếp theo
        $range = $sheetTitle . '!A' . $nextRow;
        $values = [$formData];
        $body = new ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];
        $result = $service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);

        dd($result);
    }

    public function initializeGoogleClient() {
        $cred = storage_path('key_api/manager-gg-sheet-6f9a0050eeb6.json');

        $client = new Client();
        $client->setApplicationName('Google Sheets API PHP');
        $client->setScopes(Sheets::SPREADSHEETS);
        $client->setAuthConfig($cred);
        $client->setAccessType('offline');

        return $client;
    }

    public function addSheet(string $spreadsheetId, Client $client, $sheetTitle)
    {
        $service = new Sheets($client);

        // $spreadsheet = $service->spreadsheets->get($spreadsheetId);

        // dd($spreadsheet->getSheets()[0]->getProperties()->getTitle());

        $requests = [
            new SheetsRequest([
                'addSheet' => [
                    'properties' => [
                        'title' => $sheetTitle
                    ]
                ]
            ])
        ];

        $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        $response = $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);

        // Định nghĩa các tiêu đề cột
        $headerValues = [['your-name', 'your-email', 'your-message', 'timestamp']];
        $body = new ValueRange(['values' => $headerValues]);
        $params = ['valueInputOption' => 'RAW'];
        $service->spreadsheets_values->update($spreadsheetId, $sheetTitle . '!A1', $body, $params);

        dd($response);
    }

    public function addSheetHasFormatHeader(string $spreadsheetId, Client $client, $sheetTitle)
    {
        $service = new Sheets($client);

        // $spreadsheet = $service->spreadsheets->get($spreadsheetId);

        // dd($spreadsheet->getSheets()[0]->getProperties()->getTitle());

        $requests = [
            new SheetsRequest([
                'addSheet' => [
                    'properties' => [
                        'title' => $sheetTitle
                    ]
                ]
            ])
        ];

        $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        $response = $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);
        $sheetId = $response->getReplies()[0]->getAddSheet()->getProperties()->getSheetId();

        // Định nghĩa các tiêu đề cột
        $headerValues = [['your-name', 'your-email', 'your-message', 'timestamp']];
        $body = new ValueRange(['values' => $headerValues]);
        $params = ['valueInputOption' => 'RAW'];
        $service->spreadsheets_values->update($spreadsheetId, $sheetTitle . '!A1', $body, $params);

        $requests = [
            new SheetsRequest([
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $sheetId,
                        'startRowIndex' => 0,
                        'endRowIndex' => 1,
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'backgroundColor' => [
                                'red' => 0.9,
                                'green' => 0,
                                'blue' => 0
                            ],
                            'textFormat' => [
                                'fontSize' => 16,
                                'bold' => true,
                                'foregroundColor' => ['red' => 1, 'green' => 1, 'blue' => 1]
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat(backgroundColor,textFormat)'
                ]
            ])
        ];

        $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);

        $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);

        dd($response);
    }
}
