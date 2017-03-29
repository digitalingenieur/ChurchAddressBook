<?php

/**
 * Contao Churchtools-Bundle
 *
 * Copyright (c) 2016 Samuel Heer
 *
 * @license LGPL-3.0+
 */

namespace App\Churchtools;
/**
 * Provide methods regarding churchtools api.
 *
 * @author Samuel Heer <https://github.com/digitalingenieur>
 */
class ChurchtoolsApi{

	/**
	 *  Store Authentication Cookie From Login Response
	 * @var array
	 */
	protected $auth = array();

	protected $url;


	/**
	 * On object creation API gets contacted with credentials to authenticate the user
	 *
	 * @param string $email
	 * @param string $password
	 */
	public function __construct($email='', $password='', $url = ''){

		$this->url = $url==''? config('churchtools.url'):$url;
		
		$postfields = array(
				'email' => $email==''? config('churchtools.username'):$email,
				'password' => $password==''? config('churchtools.password'):$password,
				'directtool' => 'yes'
		);
		$url = $this->url.'/?q=login';
		
		$this->request($url, $postfields);
	}

	/**
	 * Return categories array from api.
	 * Called api function: getMasterData
	 *
	 * @return array categories
	 */
	public function getCalendarCategories(){

		$postfields = array(
			'func' => 'getMasterData',
			'directtool' 	=> 'yes'
		);

		$url = $this->url.'/index.php?q=churchcal/ajax';
		$masterData = $this->request($url,$postfields);
		return $masterData->category;
	}

	public function getAllPerson(){

		$postfields = array(
			'func' => 'getAllPersonData',
			'directtool' 	=> 'yes'
		);

		$url = config('churchtools.url').'/index.php?q=churchdb/ajax';
		$relationships = $this->request($url,$postfields);
		return $relationships;
	}


	public function getAllRelationships(){

		$postfields = array(
			'func' => 'getAllRels',
			'directtool' 	=> 'yes'
		);

		$url = config('churchtools.url').'/index.php?q=churchdb/ajax';
		$masterData = $this->request($url,$postfields);
		return $masterData->rels;
	}


	public function getPersonDetails($id){
		$postfields = array(
			'func' => 'getPersonDetails',
			'directtool' 	=> 'yes',
			'id' => $id
		);

		$url = $this->url.'/index.php?q=churchdb/ajax';
		$masterData = $this->request($url,$postfields);
		return $masterData;
	}

	/**
	 * Return Events for given categories
	 * Called api function: getCalenderEvents
	 *
	 * @param array $arrCategories
	 * @param int $daysFrom
	 * @param int $daysTo
	 * @return array events
	 */
	public function loadEvents($arrCategories,$daysFrom,$daysTo){
		$postfields = array(
			'func'			=> 'getCalendarEvents',
			'category_ids' 	=> $arrCategories,
			'directtool' 	=> 'yes',
			'from' 			=> $daysFrom,
			'to' 			=> $daysTo
		);

		$url = $this->url.'/index.php?q=churchcal/ajax';
		return $this->request($url,$postfields);
	}

	/**
	 * Request helper function to query the api via curl
	 * 
	 * @param string $strUrl
	 * @param array $arrPostfields
	 * @return array data
	 */
	protected function request($strUrl, $arrPostfields){

		$ch = curl_init();
		
		$arrOptions = array(
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_POST 			=> true,
			CURLOPT_HEADER 			=> true,
			CURLOPT_COOKIE			=> implode($this->auth,';'),
			CURLOPT_URL 			=> $strUrl,
			CURLOPT_POSTFIELDS 		=> http_build_query($arrPostfields)
		);
		curl_setopt_array($ch, $arrOptions);

		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

 		if($info['http_code'] == 200 && $info['content_type'] == 'application/json'){
 			$this->getCookiesFromCurlHeader($result);

			/*if($result->status == 'fail'){
				throw new \Exception($result->data);
			}*/

			$arrResult = json_decode(substr($result, strrpos($result, "\r\n")));
			return $arrResult->data;	
		}
		else if($info['http_code'] == 0){
			throw new \Exception('Churchtools could not be reached. Please check settings.');
		}
		else{
			throw new \Exception('Something went wrong with the API call.');
		}
		
	}

	/**
	 * Helper class to get Set-Cookie out of CURL header and store it in auth attribute.
	 * TODO: This could be store in SESSION also, to reduce api calls
	 *
	 * @param $response
	 */
	private function getCookiesFromCurlHeader($response){
    	$header_text = substr($response, 0, strrpos($response, "\r\n"));

	    foreach (explode("\r\n", $header_text) as $i => $line){
	    	if(strpos($line,':')){
	    		list ($key, $value) = explode(': ', $line);
		    	if($key == 'Set-Cookie'){
		    		$this->auth[] = substr($value,0, strpos($value, ";"));	
		    	}	
	    	}
	    	
	    }
	}
}