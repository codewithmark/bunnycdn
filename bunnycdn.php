<?php

class BunnyCDN
{    
    private $api_key_account;
	private $api_key_storage;
	
	protected $api_url = array
	(
		"zone" => "https://bunnycdn.com/api",
		'storage' => 'https://storage.bunnycdn.com'
	);


	//--->account > start

	public function Account($api_key_account='')
	{ 
		if(!$api_key_account)
		{			 
			return array('status' =>'error' ,'code' =>'missing_api_key_account' ,'msg'=> 'missing api key account');
			die();
		}
		$this->api_key_account = $api_key_account;
        return $this;	 
	}

	public function GetZoneList()
	{	
		/*
			will get all of the zones for the account
		*/
		
		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone';

		$get_header = $this->create_header($key);

		$api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header , ) );
		
		if($api_call['http_code'] !=200)
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array)->Message , 
			);
			return $result;
			die();
		}

		$zone_data =  json_decode($api_call['data']);	

 		$a1 = array();

 		foreach ($zone_data as  $k1 => $v1) 
		{			  
			$arr_hostnames  = array();

			//--->get all the hostnames > start
			if($v1->Hostnames)
			{
				foreach ($v1->Hostnames as $key => $v2) 
				{
					array_push($arr_hostnames,  $v2->Value);
				}
			}
			//--->get all the hostnames > end

			$d = array
			(	
				"zone_id" => $v1->Id,
				"zone_name"=>$v1->Name,
				"monthly_bandwidth_used" =>$this->format_bytes($v1->MonthlyBandwidthUsed),				
				"host_names" =>$arr_hostnames,
			);
			array_push($a1,$d);
		}
		
		return array('status' => 'success', 'zone_smry'=>$a1,"zone_details" => $zone_data);

	}
	public function GetZone($zone_id = '')
	{	
		/*
			will get a user zone for the account
		*/
		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}
		
		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}


		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/'.$zone_id;

		$get_header = $this->create_header($key);
		$post_data_array = array('id'=>$zone_id);

		$api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		

		if($api_call['http_code'] !=200)
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}

		$zone_data =  json_decode($api_call['data']);

		$a1 = array();
		$arr_hostnames  = array();

		//--->get all the hostnames > start
		if($zone_data->Hostnames)
		{
			foreach ($zone_data->Hostnames as $key => $v1) 
			{
				array_push($arr_hostnames,  $v1->Value);
			}
		}
		//--->get all the hostnames > end

		$d = array
		(	
			"zone_id" => $zone_data->Id,
			"zone_name"=>$zone_data->Name,
			"monthly_bandwidth_used" =>$this->format_bytes($zone_data->MonthlyBandwidthUsed),				
			"host_names" =>$arr_hostnames,
		);
		array_push($a1,$d);

		return array('status' => 'success', 'zone_smry'=>$a1,"zone_details" => $zone_data);
		die(); 
	}


	public function CreateNewZone($zone_name = '', $zone_url = '')
	{	
		/*
			will create a new zone for the account
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_name)
		{
			return array('status' =>'error' ,'code' =>'zone_name' ,'msg'=> 'missing zone name');
			die();
		}

		if(!$zone_url)
		{
			return array('status' =>'error' ,'code' =>'zone_url' ,'msg'=> 'missing zone url');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone';

		$get_header = $this->create_header($key);


		$post_data_array = array('Name' => $zone_name, 'OriginUrl' => $zone_url);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		 
		if($api_call['http_code'] !=201)
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}

		//convert to php array for data parsing
		$zone_data =  json_decode($api_call['data']);

		//--->get all the hostnames > start
		$cdnurl = '';
		if($zone_data->Hostnames)
		{
			foreach ($zone_data->Hostnames as $key => $v1) 
			{
				$cdnurl = $v1->Value;				 
			}
		}
		//--->get all the hostnames > end

	 

		return array
		(
			'status' => 'success', 
			"zone_id" => $zone_data->Id,
			"zone_name"=>$zone_data->Name,
			"origin_url"=>$zone_data->OriginUrl,
			"cdn_url"=>$cdnurl,			
			"zone_details" => $zone_data
		);
		die();
	}


	public function DeleteZone($zone_id= '')
	{	
		/*
			will delete a zone for the account
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}
 

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/'. $zone_id;

		$get_header = $this->create_header($key);
 
		$api_call = $this->run( array('call_method' => 'DELETE', 'api_url' => $api_url,'header' => $get_header , ) );
		
		
		if($api_call['http_code'] !=200 && $api_call['http_code'] !=302)
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}		 
 		
 		return array(
			'status' => 'success', 
			"msg" => $api_call,
 
		);
 		//return $api_call;
		die();
	}



	public function PurgeZoneCache($zone_id= '')
	{	
		/*
			will purge cache for the whole zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}
 

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/'. $zone_id.'/purgeCache';

		$get_header = $this->create_header($key);


		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , ) );
		
		
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}


	public function AddHostName($zone_id = '', $host_name_url = '')
	{	
		/*
			will add a host name for the zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}

		if(!$host_name_url)
		{
			return array('status' =>'error' ,'code' =>'host_name_url' ,'msg'=> 'missing host name url');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/addHostname';

		$get_header = $this->create_header($key);


		$post_data_array = array('PullZoneId' => $zone_id, 'Hostname' => $host_name_url);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}


	public function DeleteHostName($zone_id = '', $host_name_url = '')
	{	
		/*
			will delete a host name for the zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}

		if(!$host_name_url)
		{
			return array('status' =>'error' ,'code' =>'host_name_url' ,'msg'=> 'missing host name url');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/deleteHostname?id='.$zone_id.'&hostname='.$host_name_url ;

		$get_header = $this->create_header($key);


		$api_call = $this->run( array('call_method' => 'DELETE', 'api_url' => $api_url,'header' => $get_header , ) );
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}

	public function AddBlockedIP($zone_id = '', $blocked_ip = '')
	{	
		/*
			will add a blocked ip for the zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}

		if(!$blocked_ip)
		{
			return array('status' =>'error' ,'code' =>'blocked_ip' ,'msg'=> 'missing blocked ip');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/addBlockedIp' ;

		$get_header = $this->create_header($key);


		$post_data_array = array('PullZoneId' => $zone_id, 'BlockedIp' => $blocked_ip);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}


	public function RemoveBlockedIP($zone_id = '', $blocked_ip = '')
	{	
		/*
			will remove a blocked ip for the zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$zone_id)
		{
			return array('status' =>'error' ,'code' =>'zone_id' ,'msg'=> 'missing zone id');
			die();
		}

		if(!$blocked_ip)
		{
			return array('status' =>'error' ,'code' =>'blocked_ip' ,'msg'=> 'missing blocked ip');
			die();
		}

		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/pullzone/removeBlockedIp' ;

		$get_header = $this->create_header($key);


		$post_data_array = array('PullZoneId' => $zone_id, 'BlockedIp' => $blocked_ip);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , 'post_data_array'=>$post_data_array) );
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}

	public function PurgeURL($url = '')
	{	
		/*
			will purge a url for the account
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$url)
		{
			return array('status' =>'error' ,'code' =>'url' ,'msg'=> 'missing url');
			die();
		}
 
		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/purge?url='.$url ;

		$get_header = $this->create_header($key);


		//$post_data_array = array('PullZoneId' => $zone_id, 'BlockedIp' => $blocked_ip);

		$api_call = $this->run( array('call_method' => 'POST', 'api_url' => $api_url,'header' => $get_header , ));
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}

	public function Stats()
	{	
		/*
			will get all the statistics for the account
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		} 
 
		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/statistics';

		$get_header = $this->create_header($key);

		$api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header , ));
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => json_decode( ($api_call['data'])), 
		);
		die();
	}

	public function Billing()
	{	
		/*
			will get the billing information for the account
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		} 
 
		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/billing';

		$get_header = $this->create_header($key);
 
		$api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header , ));
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => json_decode( ($api_call['data'])), 
		);
		die();
	}


	public function ApplyCode($apply_code = '')
	{	
		/*
			will apply a promo code to account to save money
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		}

		if(!$apply_code)
		{
			return array('status' =>'error' ,'code' =>'apply_code' ,'msg'=> 'missing apply code');
			die();
		}
 
		$key =  $this->api_key_account;
		$api_url = $this->api_url['zone'].'/billing/applycode?couponCode='.$apply_code ;

		$get_header = $this->create_header($key);

		$api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header , ));
		 
		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		
 		return array(
			'status' => 'success', 
			"msg" => $api_call, 
		);
		die();
	}

	//--->account > end




	//--->storage > start

	public function Storage($api_key_storage='')
	{ 
		if(!$api_key_storage)
		{			 
			return array('status' =>'error' ,'code' =>'api_key_storage' ,'msg'=> 'missing storage api key');
			die();
		}

		$this->api_key_storage = $api_key_storage;
        return $this;	 
	}

	public function GetStorageZone($storage_path ='')
	{	
		/*
			will get all of the files and subfolders for storage zone
		*/

		if( !$this->api_key_storage)
		{
			return array('status' =>'error' ,'code' =>'api_key_storage' ,'msg'=> 'missing storage api key');
			die();
		}
		if(!$storage_path  )
		{
			return array('status' =>'error' ,'code' =>'missing_zone_id' ,'msg'=> 'missing zone id');
			die();
		}

		$key =  $this->api_key_storage;
		$api_url = $this->fix_url($this->api_url['storage'].$storage_path );

		$get_header = $this->create_header($key);		

		$api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header ,) );

		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		$request_array =  json_decode(json_encode($api_call['data']));

		//convert to php array for data parsing
		$zone_data =  json_decode(($api_call['data']));

		//--->get all the hostnames > start
		$files = array();
		$folders = array(); 		
		//--->get all the hostnames > start
		if($zone_data)
		{
			foreach ($zone_data as $key => $v1) 
			{
				$folder_path = str_replace('/'.$v1->StorageZoneName.'/',"/",$v1->Path);
				if(!$v1->IsDirectory)
				{
					//files only
					$d = array
					(	
						"storage_zone_name"=>$v1->StorageZoneName,
						"folder_path"=>$folder_path,
						"file_name" =>$v1->ObjectName,
						"file_zone_path" =>$v1->Path.$v1->ObjectName,
						"file_dl_path" => $folder_path.$v1->ObjectName,
					);
					array_push($files,  $d);
				}
				else if($v1->IsDirectory)
				{
					//folders only
					$d = array
					(	
						"storage_zone_name"=>$v1->StorageZoneName,
						"main_folder"=>$v1->Path,
						"sub_folder" =>$v1->ObjectName,
						"folder_path" =>$v1->Path.$v1->ObjectName,							
					);
					array_push($folders,  $d);
				}
			}
		}
		//--->get all the hostnames > end

		return array(
			'status' => 'success', 			
			'zone_smry'=> array('folders' => $folders,'files' => $files,),			
			"zone_details" => json_decode($request_array), 
		);
		die();
	}

	public function PutFile($file_storage_path ='', $file_storage_name='' , $file_local_path='')
	{
		/*
			will upload a file to storage zone
		*/

		if( !$this->api_key_storage)
		{
			return array('status' =>'error' ,'code' =>'api_key_storage' ,'msg'=> 'missing storage api key');
			die();
		}
		if(!$file_storage_path  )
		{
			return array('status' =>'error' ,'code' =>'file_storage_path' ,'msg'=> 'missing storage path');
			die();
		}

		if(!$file_local_path  )
		{
			return array('status' =>'error' ,'code' =>'file_local_path' ,'msg'=> 'missing file path');
			die();
		}

		$key =  $this->api_key_storage;
		$api_url = $this->fix_url($this->api_url['storage'].$file_storage_path.$file_storage_name);

		$get_header = $this->create_header($key);
		
	
		// Open the file
		$fileStream = fopen($file_local_path, "r") or die("Unable to open file!");
		$dataLength = filesize($file_local_path);


		// Initialize and configure curl
		$curl = curl_init();
		curl_setopt_array( $curl,
			array( CURLOPT_CUSTOMREQUEST => 'PUT'
			//, CURLOPT_URL => 'https://storage.bunnycdn.com/' . $pullZoneName . $filePath
			, CURLOPT_URL => $api_url
			
			, CURLOPT_RETURNTRANSFER => 1   // means output will be a return value from curl_exec() instead of simply echoed
			, CURLOPT_TIMEOUT => 60
			, CURLOPT_FOLLOWLOCATION => 0   // don't follow any Location headers, use only the CURLOPT_URL, this is for security
			, CURLOPT_FAILONERROR => 0      // do not fail verbosely fi the http_code is an error, this is for security
			, CURLOPT_SSL_VERIFYPEER => 1   // do verify the SSL of CURLOPT_URL, this is for security
			, CURLOPT_VERBOSE => 0          // don't output verbosely to stderr, this is for security
			, CURLOPT_INFILE => $fileStream
			, CURLOPT_INFILESIZE => $dataLength
			, CURLOPT_UPLOAD => 1
			, CURLOPT_HTTPHEADER => array(
		            'AccessKey: ' . $key
		        )
			) );

		// Send the request
		$response = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		// Cleanup
		curl_close($curl);
		fclose($fileStream);

		if($http_code !=201 )
		{
			//error message
			$request_array =  json_decode(json_encode($response));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$http_code,
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
		

		return array(
			'status' => 'success', 
			'file_storage_path'	=>$file_storage_path,		
			'file_storage_name'	=>$file_storage_name,
			'file_path'	=>$file_storage_path.$file_storage_name,
			'msg'=> $response,			
		);
		die();
 
	}

	public function DeleteFile($storage_path ='')
	{ 
		/*
			will delete a file from the storage zone
		*/

		if(!$storage_path || !$this->api_key_storage)
		{
			return array('status' =>'error' ,'code' =>'missing_api_key_storage' ,'msg'=> 'missing storage missing kpi');
			die();
		}

		$key =  $this->api_key_storage;
		$api_url = $this->fix_url($this->api_url['storage'].$storage_path );

		$accessKey = $this->api_key_storage;
 		
 		$get_header = $this->create_header($key);

		$api_call = $this->run( array('call_method' => 'DELETE', 'api_url' => $api_url,'header' => $get_header  ) );		 

		if($api_call['http_code'] !=200  )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => json_decode($request_array) , 
			);
			return $result;
			die();
		}
	 
		return array(
			'status' => 'success',  
			'msg'=> $api_call,			
		);
		die();
 
	}
	//--->storage > end

  	public function DownloadFile($file_url) 
  	{
  		/*
			will allow you to download a remote file from any server that is accessible 
		*/

  		$filename = $file_url; 
	    $filedata = @file_get_contents($filename);

	    // SUCCESS
	    if ($filedata)
	    {
	        // GET A NAME FOR THE FILE
	        $basename = basename($filename);

	        // THESE HEADERS ARE USED ON ALL BROWSERS
	        header("Content-Type: application-x/force-download");
	        header("Content-Disposition: attachment; filename=$basename");
	        header("Content-length: " . (string)(strlen($filedata)));
	        header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
	        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

	        // THIS HEADER MUST BE OMITTED FOR IE 6+
	        if (FALSE === strpos($_SERVER["HTTP_USER_AGENT"], 'MSIE '))
	        {
	            header("Cache-Control: no-cache, must-revalidate");
	        }

	        // THIS IS THE LAST HEADER
	        header("Pragma: no-cache");

	        // FLUSH THE HEADERS TO THE BROWSER
	        flush();

	        // CAPTURE THE FILE IN THE OUTPUT BUFFERS - WILL BE FLUSHED AT SCRIPT END
	        ob_start();
	        echo $filedata;
	    }

	    // FAILURE
	    else
	    {
	        die("ERROR: UNABLE TO OPEN $filename");
	    }
	}


	//--->process functions > start

	private function create_header($api_key)
	{
		$header = array('Content-Type:application/json','accesskey:'.$api_key.'' );
		return $header;
	}

	private function run($call_arr = array('call_method' => 'GET', 'api_url' => 'api_url','header' => array(),'post_data_array' => array() , ) )
	{ 
		$call_method 		= isset($call_arr['call_method']) ? $call_arr['call_method'] : 'GET' ;
	    $api_url 			= isset($call_arr['api_url']) ? $call_arr['api_url'] : 'api_url' ;
	    $header 			= isset($call_arr['header']) ? $call_arr['header'] : '' ;
	    $post_data_array 	= isset($call_arr['post_data_array']) ? $call_arr['post_data_array'] : '' ;


	    $post_data = json_encode($post_data_array);

	    $curl = curl_init($api_url);   

	   	curl_setopt($curl, CURLOPT_HTTPHEADER,$header);
	    
	    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $call_method); 

	    curl_setopt($curl, CURLOPT_URL, $api_url);
	    
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    
	    curl_setopt($curl, CURLOPT_POST, 1);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	    
	    $result = curl_exec($curl);
	    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	    curl_close($curl);	 

	    
	    //For error checking
	    if ( $result === false )
	    {
	        return array('status' =>'error' ,'code'=> 'curl_error', 'result' => curl_error($curl) ,);
	    	die();
	    }
	 	
	 	return array('http_code'=> $http_code, 'data' =>  $result,);
	}	  
	//--->process functions > end
	


	//--->private functions > start

	private  function fix_url($url ='')
	{
		return str_replace("\\", "/",  $url );
	}

	private function format_bytes($bytes, $force_unit = NULL, $format = NULL, $si = TRUE)
	{
	    // Format string
	    $format = ($format === NULL) ? '%01.2f %s' : (string) $format;

	    // IEC prefixes (binary)
	    if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE)
	    {
	        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
	        $mod   = 1024;
	    }
	    // SI prefixes (decimal)
	    else
	    {
	        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
	        $mod   = 1000;
	    }
	    // Determine unit to use
	    if (($power = array_search((string) $force_unit, $units)) === FALSE)
	    {
	        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
	    }
	    return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}
	//--->private functions > end
}	