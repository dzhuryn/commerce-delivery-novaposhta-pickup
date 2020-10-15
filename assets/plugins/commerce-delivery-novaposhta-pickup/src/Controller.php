<?php

namespace CommerceDeliveryNpPickup;

use CommerceDeliveryNpPickup\Model\City;
use CommerceDeliveryNpPickup\Model\Department;
use Helpers\Config;

class Controller
{
    private $config;

    private $request;
    /**
     * @var City
     */
    private $city;
    /**
     * @var \DocumentParser
     */
    private $modx;
    /**
     * @var Department
     */
    private $department;


    private $availableLang = ['ru','ua'];

    public function __construct(\DocumentParser $modx, Config $config)
    {
        $this->config = $config;
        $this->request = new Request($this->config->getCFGDef('apiKey'));
        $this->modx = $modx;
        $this->city = new City($this->modx);
        $this->department = new Department($this->modx);
    }
    public function getDepartments(){
        $cityRef = $_GET['city_ref'];
        $query = $_GET['query'];

        $lang = in_array($_GET['lang'],$this->availableLang)?$_GET['lang']:'ru';
        $field = $lang === 'ua'?'address':'address_'.$lang;
        return [
            'results'=>$this->department->search($cityRef,$query,$field)
        ];

    }
    public function getCities(){
        $query = $_GET['query'];
        $lang = in_array($_GET['lang'],$this->availableLang)?$_GET['lang']:'ru';
        $field = $lang === 'ua'?'city':'city_'.$lang;

        return [
            'results'=>$this->city->search($query,$field)
        ];
    }
    public function update()
    {

        $start = microtime(true);
                
        $citiesResponse = $this->request->request('getCities');
        if ($citiesResponse['success']) {
            $cities = [];
            foreach ($citiesResponse['data'] as $city) {

                $description = trim($city['Description']);
                $descriptionRu = trim($city['DescriptionRu']);
                $cities[] = [
                    'ref' => $city['Ref'],
                    'city' => $description,
                    'city_ru' => !empty($descriptionRu)?$descriptionRu:$description,
                ];
            }
            $this->city->update($cities);
        }

        $departmentsResponse  = $this->request->request('getWarehouses');
        if ($departmentsResponse['success']) {
            $departments = [];
            foreach ($departmentsResponse['data'] as $department) {

                $departments[] = [
                    'ref'=>$department['Ref'],
                    'city_ref'=>$department['CityRef'],
                    'num'=>$department['Number'],
                    'address'=>$department['Description'],
                    'address_ru'=>$department['DescriptionRu'],

                ];
            }

            $this->department->update($departments);
        }

        return [
            'status' => true,
            'time'=>round(microtime(true) - $start, 4).'c'
        ];
    }
}