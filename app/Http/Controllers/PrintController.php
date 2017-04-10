<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Churchtools\ChurchtoolsApi as ChurchtoolsApi;
use App\ChurchAddressBook as CAB;
use PDF;
use DB;
use App\Person;


class PrintController extends Controller
{
    // Load Person Data from Churchtools and save as Models in Local Database
	function load(){
		
		Person::truncate();
		DB::table('person_person')->truncate();

		//Truncate tables
		//Read Person list with given Paramter (Filters?)
		$api = new ChurchtoolsApi();
		$allPersons = $api->getAllPerson();

		//Put Information in Person Model (name, surname, telefonprivat, geb, partner?, children?...)
		foreach($allPersons as $json_id => $json_person){
			$person = Person::create([
				'surname' => $json_person->name,
				'name' => $json_person->vorname,
                'tel' => $json_person->telefonprivat,
                'birthday' => $json_person->geb,
				'ct_id' => intval($json_person->p_id),
                //...
				]);
		}

		//Create Relationships //getAllRels
		$allRelationships = $api->getAllRelationships();
		foreach($allRelationships as $relationship){
			$person = Person::where('ct_id',$relationship->v_id)->first();

			//Partner
			if($relationship->typ_id == 2){
				$partner = Person::where('ct_id',$relationship->k_id)->first();
				$person->partner()->save($partner);
				$partner->partner()->save($person);
			}
			//Children
			if($relationship->typ_id == 1){
				$person->children()->attach(Person::where('ct_id',$relationship->k_id)->first()->id);
			}
			$person->save();	
		}
		//Set flag on Person if it's information are complete
		//Skip if Person does have completion flag
	}


	//Make an PDF File (CMYK A5 etc) from Local Database
	function generate(){
		//TODO IMPLEMENT
		$persons = Person::all();
		

		$filteredCollection = $persons->filter(function($value, $key){
			return $value->partner != NULL;
		});
		$sorted = $filteredCollection->sortBy('surname');

		//print_r($sorted->values()->all());
        
        /*$samu = Person::where('name', 'samuel')->limit(1)->get();
        echo $samu;
        $partner = $samu->partner();
        echo $partner;
    
        PDF::SetTitle('Hello World');*/
        
        //############################HEADER########################
        PDF::setHeaderCallback(function($pdf){
    
        });
        
        //############################FOOTER########################
        //Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')

        PDF::setFooterCallback(function($pdf){
            PDF::SetY(-15);
            // Set font
            PDF::SetFont('helvetica', '', 8);
            // Footer Text
            PDF::Cell(10, 10, 'Gemeindeverzeichnis',0 ,false , 'L', 0, '', 0, false, 'T', 'M');
            // Page number
            PDF::Cell(0, 10, PDF::getAliasNumPage(), 0, true, 'C', 0, '', 0, false, 'T', 'M');
            

            });
        
        
            
        foreach($persons as $person){
            
            
            
            PDF::AddPage();
	        PDF::Write(0, $person->name.' '.$person->surname);  
            
            PDF::Ln(10);
            
            if($person->partner() != NULL){
                PDF::Write(0, $person->partner()->name);
            }
        }
		
	    PDF::Output('hello_world.pdf');
	}

    
    function execute(){
    	$dir = new CAB();

    	$allPersons = $api->getAllPerson();

    	foreach($arrPersons as $pid => $person){
    		
    		if($dir->handlePerson($pid)){
    			$personDetails = $api->getPersonDetails($pid);

	    		echo $person->name.', '.$person->vorname;
	    		print_r($personDetails->rels);	
	    		echo '<br>';
    		}    			
    	}


    
 	
    }
}
