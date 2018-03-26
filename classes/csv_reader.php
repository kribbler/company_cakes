<?php

class CsvReader 
{
    public $aBirthdays = []; //keeps in array the birthdays list read from csv
    public $aCakeList = []; //keeps the list of cake dates
    public $sCakesCsv = "Date, Number of Small Cakes, Number of Large Cakes, Names of people getting cake\n";
    private $sCurrentYear = '2017'; //hardcoded year so I can stick to the example; otherwise use date("Y")
    private $aHolidays = []; //company's holidays

    function __construct()
    {
        // init the array of company's holidays - 
        // all cake days are added to this array so no consecutive cake days
        $this->aHolidays = [
            $this->sCurrentYear . '-12-25',
            $this->sCurrentYear . '-12-26',
            $this->sCurrentYear . '-01-01',
        ];
    }

    //read and converts the csv to array
    public function readCsv($fileName)
    {
        try {
            $csv = array_map('str_getcsv', file($fileName));
            array_walk($csv, function(&$a) use ($csv) {
                $a = array_combine($csv[0], $a);
            });
            array_shift($csv);
            
            //the array should be ordered by date
            $this->aBirthdays = $csv;
        } catch (Exception $ex) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    //main engine
    public function processBirthdays() 
    {
        $k = 1; //number of cakes for one day
        $aNames = []; //array with names for a day

        //parse the birthdays array
        foreach($this->aBirthdays as $key => $value){
            //init the postpone - used to join consecutive birthdays in one day
            if (!isset($this->aBirthdays[$key]['postpone'])) {
                $this->aBirthdays[$key]['postpone'] = false;
            }
            
            $cakeOn = $this->getCakeDate($value['birthday']);
            $aNames[] = $this->aBirthdays[$key]['name'];
            if ($this->consecutiveBirthDays($cakeOn) && $this->aBirthdays[$key-1]['postpone'] == false) {
                $this->aBirthdays[$key]['postpone'] = true;
            }
            
            //creates the row for the final export
            if (!$this->aBirthdays[$key]['postpone']) {
                $aRowForCakes = [
                    $cakeOn, 
                    ($k > 1) ? "0" : "1",
                    ($k > 1) ? "1" : "0",
                    implode(", ", $aNames)
                ];

                $this->aCakeList[] = implode(", ", $aRowForCakes);
                $this->aHolidays[] = date("Y-m-d", strtotime($cakeOn . ' +1 days'));
                
                $k = 1;
                $aNames = [];
            } else {
                $k++;
            }
        }
    }

    //checks if cake date should be postponed if another birthday is due to come
    private function consecutiveBirthDays($date) 
    {
        $response = false;
        $date = date_parse_from_format("Y-m-d", $date);
        $date = sprintf("%02d", $date['month']) . '-' . sprintf("%02d", $date['day']);

        foreach($this->aBirthdays as $key => $value){
            if (stristr($value['birthday'], $date)){
                $response = true;
            }
        }

        return $response;
    }

    //coverts the birthday to current year next working/available day for cake
    private function getCakeDate($date) 
    {
        $date = date_parse_from_format("Y-m-d", $date);
        $date = $this->sCurrentYear . '-' . sprintf("%02d", $date['month']) . '-' . sprintf("%02d", $date['day']);
        $i = 1;
        $nextBusinessDay = date('Y-m-d', strtotime($date . ' +' . $i . ' Weekday'));
        
        while (in_array($nextBusinessDay, $this->aHolidays)) {
            $i++;
            $nextBusinessDay = date('Y-m-d', strtotime($date . ' +' . $i . ' Weekday'));
        }
        
        return $nextBusinessDay;
    }

    public function setCakesCsv()
    {
        $this->sCakesCsv .= implode("\n", $this->aCakeList);
    }

    //simple var_dump to see if the logic is fine. should send it maybe to a file
    public function exportCsv() 
    {
        var_dump($this->sCakesCsv);        
    }
}