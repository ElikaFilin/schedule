<?php
/**
 * Created by IntelliJ IDEA.
 * User: DenysBarsuk
 * Date: 11.06.2018
 * Time: 11:24
 */

namespace Schedule\Core;


use Schedule\Core\Models\Cities;
use Schedule\Core\Models\LocalRegions;
use Schedule\Core\Models\States;
use Schedule\Core\Models\Stations;

class Location extends Kernel
{
    /**
     * @var States
     */
    private $state;
    /**
     * @var Cities
     */
    private $city;
    /**
     * @var LocalRegions
     */
    private $local_reg;
    /**
     * @var Stations
     */
    private $station;

    /**
     * @return array
     */
    public function getState()
    {
        return !is_null($this->state)?$this->state:false;
    }

    /**
     * @return array
     */
    public function getCity()
    {
        return !is_null($this->city)?$this->city:false;
    }

    /**
     * @return array
     */
    public function getLocalReg()
    {
        return !is_null($this->local_reg)?$this->local_reg:false;
    }

    /**
     * @return array
     */
    public function getStations()
    {
        $info = [];
        foreach ($this->station as $k => $item) {
            $info[$k] = $item;
        }
        return $info;

    }

    /**
     * @return bool|Stations
     */
    public function getStation()
    {

        return !is_null($this->station)?$this->station:false;

    }


    public function createLocation()
    {
        //create or modify all location path
    }

    public function addState( States $state)
    {
        $this->state=$state;
    }

    public function addCity( Cities $data)
    {
        $this->city=$data;
    }
    public function addStation( Stations $data)
    {
        $this->station=$data;

    }
    public function addLocalRegion($data)
    {

    }


    public static function findAllLocation($state,$wildcard,&$data_for_clarifying=null)//accross stitions and cities
    {

        if(!is_null($state=States::getOneByAnyName($state))){
            $city=Cities::getIdByAnyName($state->getId(),$wildcard);
            if(count($city)>1){
                $data_for_clarifying=$city;
                return false;
            }elseif(count($city)==0){
                //write your city doesnt exist
            }else{
                if(count($stations=$city[0]->stations)>0){
                    if(count($stations)==1){
                        $loc=new self();
                        $loc->addState($state);
                        $loc->addCity($city[0]);
                        $loc->local_reg=$loc->city->is_regional?null:LocalRegions::findFirst($loc->city->local_district_id);
                        $loc->addStation($stations[0]);
                        return $loc; //building and returning full location
                    }else{
                        $data_for_clarifying=$stations;
                        return false;
                    }
                }else{
                    echo "creating station";
                }

            }
        }

        //search accross all fields
        //find all cities if 1 check for amount of stations of more then 1 return and check once more if 0 create station according to city name
    }
    public static function findAllVariants($wildcard):array //accross stitions and cities
    {

        $variants=[];
        if(!is_null($state=States::getStatesByAnyName($wildcard))){
            $variants['states']=$state;
        }

            $city=Cities::find([
                'conditions'=>"(latin_name like ?0 or cyr_name like ?0 or national_name like ?0)",
                'bind'=>[$wildcard."%"]
            ]);
            if(count($city)>0){
                $variants['city']=$city;
        }
        $loc_reg=LocalRegions::find([
            'conditions'=>"(latin_name like ?0 or cyr_name like ?0 or national_name like ?0)",
            'bind'=>[$wildcard."%"]
        ]);
        if(count($loc_reg)>0){
            $variants['loc_reg']=$loc_reg;
        }
        $stations=Stations::find([
            'conditions'=>"(latin_name like ?0 or cyr_name like ?0 or national_name like ?0)",
            'bind'=>[$wildcard."%"]
        ]);
        if(count($stations)>0){
            $variants['stations']=$stations;
        }
        return $variants;

        //search accross all fields
        //find all cities if 1 check for amount of stations of more then 1 return and check once more if 0 create station according to city name
    }

    /**
     * @param $state
     * @param $local_region
     * @param $city
     * @param $station
     * @return bool|Location
     */
    public static function getLocation($state, $local_region, $city, $station)
    {
        $state_ids = States::getStatesByAnyName($state);
        if (count($state_ids) == 0 || count($state_ids) > 1) {
            return false;
        } else {
            $loc = new self();
            $loc->state = $state_ids[0];
            if (empty($local_region)) {
                $loc->local_reg = null;
                $cities_ids = Cities::getIdByAnyName($state_ids[0]->id, $city);
                if (count($cities_ids) == 0 || count($cities_ids) > 1) {
                    return false;
                } else {
                    $loc->city = $cities_ids[0];
                    $station_ids = Stations::getStationByAnyName($loc->city->id, $station);

                    if (count($station_ids) == 0 || count($station_ids) > 1) {
                        return false;
                    } else {
                        $loc->station = $station_ids[0];
                        return $loc;
                    }


                }
            }
        }

    }

    public static function getLocalRegionByCity($wildcard)
    {
        $city=Cities::getIdByAnyName($wildcard);
        if(count($city)==1){
            if($city[0]->is_regional){
                return new LocalRegions();
            }else{
                return LocalRegions::findFirst($city[0]->local_district_id);
            }
        }else{
            return false;
        }
    }

    public static function getLocalRegionByStation($wildcard)
    {
        $sts=Stations::getStationByAnyName($wildcard);
        if(count($sts)==1){
            $city=Cities::findFirst($sts[0]->getCityId());
            if($city->is_regional){
                return new LocalRegions();
            }else{
                return LocalRegions::findFirst($city->local_district_id);
            }
        }else{
            return false;
        }
    }

    public static function getLocationByStationId(int $id):Location
    {
        return self::getLocationByStation(Stations::findFirst($id));
    }
    public static function getLocationByStation(Stations $station)
    {
        $loc=new self();
        $loc->station=$station;
        $loc->city=Cities::findFirst($station->getCityId());
        $loc->local_reg=$loc->city->is_regional?null:LocalRegions::findFirst($loc->city->local_district_id);
        $loc->state=States::findFirst($loc->city->country_id);
        return $loc;

    }

    public function __toString()
    {
        $info='';
       if(!is_null($this->state)){
           $info.= "<b>State info:</b><br>";
           foreach ($this->state as $k => $item) {
               $info .= $k . ' ' . $item . "<br>";
           }
       }
        if(!is_null($this->city)){
            $info .= "<b>City Info:</b><br>";
            foreach ($this->city as $k => $item) {
                $info .= $k . ' ' . $item . "<br>";
            }
        }
        if(!is_null($this->local_reg)){
        $info .= "<b>Local Region Info:</b><br>";
        foreach ($this->local_reg as $k => $item) {
            $info .= $k . ' ' . $item . "<br>";
        }
       }
        if(!is_null($this->station)){
        $info .= "<b>Station Info:</b><br>";
        foreach ($this->station as $k => $item) {
            $info .= $k . ' ' . $item . "<br>";
        }
       }
        return $info;
    }
    public function toArray()
    {
        $info=[];
        if(!is_null($this->state)){
            $info['state_info']= $this->state->toArray();
        }
        if(!is_null($this->city)){
            $info ['city_info']=$this->city->toArray();
        }
        if(!is_null($this->local_reg)){
            $info ['local_region']= $this->local_reg->toArray();
        }
        if(!is_null($this->station)){
            $info ['station_info']= $this->station->toArray();
        }
        return $info;
    }
}