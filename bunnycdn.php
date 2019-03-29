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

 	


	public function PutFile($local_upload_file_path ='',  $storage_zone_path='', $storage_zone_file_path='' )
	{
		/*
			will upload a file to storage zone
		*/

		if( !$this->api_key_storage)
		{
			return array('status' =>'error' ,'code' =>'api_key_storage' ,'msg'=> 'missing storage api key');
			die();
		}
		if(!$local_upload_file_path  )
		{
			return array('status' =>'error' ,'code' =>'local_upload_file_path' ,'msg'=> 'missing file path');
			die();
		}

		if(!$storage_zone_file_path  )
		{
			return array('status' =>'error' ,'code' =>'storage_zone_file_path' ,'msg'=> 'missing storage zone file path');
			die();
		}

 

		//file variables
		
		//make folder and file name seo friendly to ensure no problem happen	  
		$cdn_file_path = $this->seo_file_name($storage_zone_file_path);

		$path_info 		= pathinfo($cdn_file_path);

		//will get folders path
		$info_dir_name 	= strtolower($path_info['dirname']);

		//will get file name with ext
		$info_file_name	= $path_info['basename'];

		//$info_file_name = $path_info['filename'];
		$info_file_ext 	= $path_info['extension'];


		$storage_file_path = $storage_zone_path .$cdn_file_path;

		 

		$key =  $this->api_key_storage;
		$api_url = $this->fix_url($this->api_url['storage'].$storage_file_path);

		$get_header = $this->create_header($key);
		
	
		// Open the file
		$file = $local_upload_file_path;
		$fileStream = fopen($file, "r") or die("Unable to open file!");
		$dataLength = filesize($file);


		// Initialize and configure curl
		$curl = curl_init();
		curl_setopt_array( $curl,
			array( CURLOPT_CUSTOMREQUEST => 'PUT'			
			, CURLOPT_URL => $api_url			
			, CURLOPT_RETURNTRANSFER => 1   // means output will be a return value from curl_exec() instead of simply echoed
			, CURLOPT_TIMEOUT => 60000 		// in case you are uploading a really BIG file!!
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
			'file_name'	=> $info_file_name	,
			'storage_file_path'	=> $storage_file_path,
			'cdn_file_path'	=> $cdn_file_path,
			'msg'=> $response,			
		);
		die();
 
	}	

	public function GetFile($storage_path ='' )
	{ 
		/*
			will get a file from the storage zone
		*/

		if(!$storage_path || !$this->api_key_storage)
		{
			return array('status' =>'error' ,'code' =>'missing_api_key_storage' ,'msg'=> 'missing storage missing api');
			die();
		}

		$key =  $this->api_key_storage;
		$api_url = $this->fix_url($this->api_url['storage'].$storage_path );

		$accessKey = $this->api_key_storage;
 		
 		$get_header = $this->create_header($key);

		$api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header  ) );		 

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

		$path_info = pathinfo($storage_path);
		$file_name = $path_info['basename'];


		$file = $api_call['data'];
		
		
 	 	header("Content-type: application/octet-stream");
      	header("Content-Disposition: attachment; filename=$file_name");
      	//will force to download...
	    echo $file ; 
 

	}




	public function DeleteFile($storage_path ='')
	{ 
		/*
			will delete a file from the storage zone
		*/

		if(!$storage_path || !$this->api_key_storage)
		{
			return array('status' =>'error' ,'code' =>'missing_api_key_storage' ,'msg'=> 'missing storage missing api');
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

	public function SecureLink($host_name ='', $security_key ='', $file_path='', $expiry_hr = 24)
	{
		$securityKey = $security_key;
		$path = $file_path;

		// Set the time of expiry to one hour from now
		$expires = (time() + 3600 ) * $expiry_hr; 

		// Generate the token
		$hashableBase = $securityKey.$path.$expires;

		// If using IP validation
		// $hashableBase .= "146.14.19.7";

		$token = md5($hashableBase, true);
		$token = base64_encode($token);
		$token = strtr($token, '+/', '-_');
		$token = str_replace('=', '', $token);  

		// Generate the URL
		$url =  "$host_name$file_path?token={$token}&expires={$expires}" ;
		
		return $url; 
	}
	//--->storage > end

	public function DownloadFile($file_url = '', $oupt_file_name='') 
  	{
		//this is a fast way to download a file
  		//remove any query string data
  		if(isset($oupt_file_name))
  		{
  			$file_name = $oupt_file_name;			
  		}
  		if(empty($oupt_file_name))
  		{
			$file_name = preg_replace('/\?.*/', '', basename($file_url));
  		}
		
		header("Content-Type: application/octet-stream");
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: attachment; filename=$file_name"); 	 
		readfile($file_url);
	}


  	public function DownloadFile1($file_url) 
  	{
  		/*
			this is a slow way to download a file
			will allow you to download a remote file from any server that is accessible 
		*/

  		$filename = $file_url; 
	    $filedata = @file_get_contents($filename);

	    // SUCCESS
	    if ($filedata)
	    {
	        // GET A NAME FOR THE FILE
	        //remove any query string data
			$basename = preg_replace('/\?.*/', '', basename($file_url));
	        //$basename = basename($filename);

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

	public function Logs( $zone_id = '', $log_date = '')
	{	
		/*
			will get log for the zone
		*/	

		if( !$this->api_key_account)
		{
			return array('status' =>'error' ,'code' =>'api_key_account' ,'msg'=> 'missing acount api key');
			die();
		} 

		if(!$log_date)
		{
			$date = new DateTime();

			//today minus 1 day... if today(2019-03-29), then date is: 2019-03-28
			$date = $date->modify("-1 day");
			$date = $date->format('m-d-y');
			$log_dt = $date;
		}
		else if( $log_date)
		{
			$date = new DateTime($log_date);
			$date = $date->format('m-d-y');
			$log_dt = $date;
		}
 
		$key =  $this->api_key_account;

		

		//$api_url = 'https://logging.bunnycdn.com/{mm}-{dd}-{yy}/{pull_zone_id}.log';
		$api_url = 'https://logging.bunnycdn.com/'.$log_dt.'/'.$zone_id.'.log';

		$get_header = $this->create_header($key);
		 
	    $api_call = $this->run( array('call_method' => 'GET', 'api_url' => $api_url,'header' => $get_header , ));
		
		if($api_call['http_code'] !=200 )
		{
			//error message
			$request_array =  json_decode(json_encode($api_call['data']));
			$result = array
			(	
				"status" => 'error',
				"http_code"=>$api_call['http_code'],
				"msg" => ($request_array) , 			 
			);
			return $result;
			//die();
		}
		else if(strlen($api_call['data']) <1 )
		{
			$result = array
			(	
				"status" => 'error',
				"http_code"=> 800,				
				'msg'=> 'Ran successfully but no log data returned for the current selection.',
			);
			return $result;
			//die();
		}
		else if($api_call['http_code'] == 200)
		{

			//convert/parse it to line break
			$t1 = explode("\n",$api_call['data']);

			$a1 = array();

			foreach ($t1 as $v1) 
			{
				if(isset($v1) && strlen($v1) > 0)
				{
					//parse "|"
					$t2 = explode("|", $v1);

					//divide it by 1000 to convert it to php unix time
					$time = round($t2[2] /1000, 0);

					$a2 = array(
						'cache_hit' => $t2[0],

						'status' => $t2[1],
						'status_code' => $this->get_http_status_code($t2[1]),

						'time_js' => $t2[2]*1,

						'time_unix' => $time,						
						'time_dttm' => date('Y-m-d H:i:s',  $time),
						'time_dt' => date('Y-m-d',  $time),
						
						'bytes' => $t2[3],
						'bytes_format' => $this->format_bytes($t2[3]),

						'zone_id' => $t2[4],
						'remote_ip' => $t2[5],

						'referer_url' => strlen($t2[6]) > 1 ? ($t2[6]) : 'direct',
						'referer_url_raw' =>  $t2[6],


						'file_url' => $t2[7],

						'cdn_datacenter_loc' => $t2[8],						

						'user_agent' => $t2[9],
						'request_id' => $t2[10],

						'country' => $t2[11],						
						'country_name' => $this->get_country_name($t2[11]),
					);	 
					array_push($a1, $a2);
				}			
			}

			$get_stats = $this->Account($key)->GetZone($zone_id)['zone_smry'][0];

			return array(
				'status' => 'success',
				"log" => $a1, 
				'zone_current_monthly_bandwidth_used' => $get_stats['monthly_bandwidth_used'],
				'zone_name'=> $get_stats['zone_name'], 
			);
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

	private function seo_file_name($file_name)
	{ 	
		/*
			will convert file name into seo url file name
			
			i.e.
			$file_name = 'code with mark !@#$%^*()_+~ $$%& _03e05 122-9****.mp4';

			//output will be
			code-with-mark-03e05-122-9.mp4

			Note only use this for file names and not for folder names!!!

		*/	
		
		$path_info 		= pathinfo($file_name);		
		$info_dir_name  = preg_replace("/[\s]/", "-", strtolower($path_info['dirname']) ); 
		

		$info_file_name = $path_info['filename'];
		$info_file_ext 	= $path_info['extension'];		

		$string = $info_file_name ;

	    $src = 'àáâãäçèéêëìíîïñòóôõöøùúûüýÿßÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝ';
	    $rep = 'aaaaaceeeeiiiinoooooouuuuyysAAAAACEEEEIIIINOOOOOOUUUUY';
	    // strip off accents (assuming utf8 PHP - note strtr() requires single-byte)
	    $string = strtr(utf8_decode($string), utf8_decode($src), $rep);
	    // convert to lower case
	    $string = strtolower($string);
	    // strip all but alphanumeric, whitespace, dot, underscore, hyphen
	    $string = preg_replace("/[^a-z0-9\s._-]/", "", $string);
	    // merge multiple consecutive whitespaces, dots, underscores, hyphens
	    $string = preg_replace("/[\s._-]+/", " ", $string);
	    // convert whitespaces to hyphens
	    $string = preg_replace("/[\s]/", "-", $string);
		
		
		if(substr($info_dir_name,1))
		{
			$file_path 	= $info_dir_name."/".$string.'.'.$info_file_ext;
		}
		else
		{
			$file_path 	= "/". $string.'.'.$info_file_ext;
		}
 
	    return $file_path;
	}


	private  function get_country_name($country_code)
	{
		$country_name = array(
			"A1" => "Anonymous Proxy",
			"A2" => "Satellite Provider",
			"O1" => "Other Country",
			"AD" => "Andorra",
			"AE" => "United Arab Emirates",
			"AF" => "Afghanistan",
			"AG" => "Antigua and Barbuda",
			"AI" => "Anguilla",
			"AL" => "Albania",
			"AM" => "Armenia",
			"AO" => "Angola",
			"AP" => "Asia/Pacific Region",
			"AQ" => "Antarctica",
			"AR" => "Argentina",
			"AS" => "American Samoa",
			"AT" => "Austria",
			"AU" => "Australia",
			"AW" => "Aruba",
			"AX" => "Aland Islands",
			"AZ" => "Azerbaijan",
			"BA" => "Bosnia and Herzegovina",
			"BB" => "Barbados",
			"BD" => "Bangladesh",
			"BE" => "Belgium",
			"BF" => "Burkina Faso",
			"BG" => "Bulgaria",
			"BH" => "Bahrain",
			"BI" => "Burundi",
			"BJ" => "Benin",
			"BL" => "Saint Bartelemey",
			"BM" => "Bermuda",
			"BN" => "Brunei Darussalam",
			"BO" => "Bolivia",
			"BQ" => "Bonaire, Saint Eustatius and Saba",
			"BR" => "Brazil",
			"BS" => "Bahamas",
			"BT" => "Bhutan",
			"BV" => "Bouvet Island",
			"BW" => "Botswana",
			"BY" => "Belarus",
			"BZ" => "Belize",
			"CA" => "Canada",
			"CC" => "Cocos (Keeling) Islands",
			"CD" => "Congo, The Democratic Republic of the",
			"CF" => "Central African Republic",
			"CG" => "Congo",
			"CH" => "Switzerland",
			"CI" => "Cote d'Ivoire",
			"CK" => "Cook Islands",
			"CL" => "Chile",
			"CM" => "Cameroon",
			"CN" => "China",
			"CO" => "Colombia",
			"CR" => "Costa Rica",
			"CU" => "Cuba",
			"CV" => "Cape Verde",
			"CW" => "Curacao",
			"CX" => "Christmas Island",
			"CY" => "Cyprus",
			"CZ" => "Czech Republic",
			"DE" => "Germany",
			"DJ" => "Djibouti",
			"DK" => "Denmark",
			"DM" => "Dominica",
			"DO" => "Dominican Republic",
			"DZ" => "Algeria",
			"EC" => "Ecuador",
			"EE" => "Estonia",
			"EG" => "Egypt",
			"EH" => "Western Sahara",
			"ER" => "Eritrea",
			"ES" => "Spain",
			"ET" => "Ethiopia",
			"EU" => "Europe",
			"FI" => "Finland",
			"FJ" => "Fiji",
			"FK" => "Falkland Islands (Malvinas)",
			"FM" => "Micronesia, Federated States of",
			"FO" => "Faroe Islands",
			"FR" => "France",
			"GA" => "Gabon",
			"GB" => "United Kingdom",
			"GD" => "Grenada",
			"GE" => "Georgia",
			"GF" => "French Guiana",
			"GG" => "Guernsey",
			"GH" => "Ghana",
			"GI" => "Gibraltar",
			"GL" => "Greenland",
			"GM" => "Gambia",
			"GN" => "Guinea",
			"GP" => "Guadeloupe",
			"GQ" => "Equatorial Guinea",
			"GR" => "Greece",
			"GS" => "South Georgia and the South Sandwich Islands",
			"GT" => "Guatemala",
			"GU" => "Guam",
			"GW" => "Guinea-Bissau",
			"GY" => "Guyana",
			"HK" => "Hong Kong",
			"HM" => "Heard Island and McDonald Islands",
			"HN" => "Honduras",
			"HR" => "Croatia",
			"HT" => "Haiti",
			"HU" => "Hungary",
			"ID" => "Indonesia",
			"IE" => "Ireland",
			"IL" => "Israel",
			"IM" => "Isle of Man",
			"IN" => "India",
			"IO" => "British Indian Ocean Territory",
			"IQ" => "Iraq",
			"IR" => "Iran, Islamic Republic of",
			"IS" => "Iceland",
			"IT" => "Italy",
			"JE" => "Jersey",
			"JM" => "Jamaica",
			"JO" => "Jordan",
			"JP" => "Japan",
			"KE" => "Kenya",
			"KG" => "Kyrgyzstan",
			"KH" => "Cambodia",
			"KI" => "Kiribati",
			"KM" => "Comoros",
			"KN" => "Saint Kitts and Nevis",
			"KP" => "Korea, Democratic People's Republic of",
			"KR" => "Korea, Republic of",
			"KW" => "Kuwait",
			"KY" => "Cayman Islands",
			"KZ" => "Kazakhstan",
			"LA" => "Lao People's Democratic Republic",
			"LB" => "Lebanon",
			"LC" => "Saint Lucia",
			"LI" => "Liechtenstein",
			"LK" => "Sri Lanka",
			"LR" => "Liberia",
			"LS" => "Lesotho",
			"LT" => "Lithuania",
			"LU" => "Luxembourg",
			"LV" => "Latvia",
			"LY" => "Libyan Arab Jamahiriya",
			"MA" => "Morocco",
			"MC" => "Monaco",
			"MD" => "Moldova, Republic of",
			"ME" => "Montenegro",
			"MF" => "Saint Martin",
			"MG" => "Madagascar",
			"MH" => "Marshall Islands",
			"MK" => "Macedonia",
			"ML" => "Mali",
			"MM" => "Myanmar",
			"MN" => "Mongolia",
			"MO" => "Macao",
			"MP" => "Northern Mariana Islands",
			"MQ" => "Martinique",
			"MR" => "Mauritania",
			"MS" => "Montserrat",
			"MT" => "Malta",
			"MU" => "Mauritius",
			"MV" => "Maldives",
			"MW" => "Malawi",
			"MX" => "Mexico",
			"MY" => "Malaysia",
			"MZ" => "Mozambique",
			"NA" => "Namibia",
			"NC" => "New Caledonia",
			"NE" => "Niger",
			"NF" => "Norfolk Island",
			"NG" => "Nigeria",
			"NI" => "Nicaragua",
			"NL" => "Netherlands",
			"NO" => "Norway",
			"NP" => "Nepal",
			"NR" => "Nauru",
			"NU" => "Niue",
			"NZ" => "New Zealand",
			"OM" => "Oman",
			"PA" => "Panama",
			"PE" => "Peru",
			"PF" => "French Polynesia",
			"PG" => "Papua New Guinea",
			"PH" => "Philippines",
			"PK" => "Pakistan",
			"PL" => "Poland",
			"PM" => "Saint Pierre and Miquelon",
			"PN" => "Pitcairn",
			"PR" => "Puerto Rico",
			"PS" => "Palestinian Territory",
			"PT" => "Portugal",
			"PW" => "Palau",
			"PY" => "Paraguay",
			"QA" => "Qatar",
			"RE" => "Reunion",
			"RO" => "Romania",
			"RS" => "Serbia",
			"RU" => "Russian Federation",
			"RW" => "Rwanda",
			"SA" => "Saudi Arabia",
			"SB" => "Solomon Islands",
			"SC" => "Seychelles",
			"SD" => "Sudan",
			"SE" => "Sweden",
			"SG" => "Singapore",
			"SH" => "Saint Helena",
			"SI" => "Slovenia",
			"SJ" => "Svalbard and Jan Mayen",
			"SK" => "Slovakia",
			"SL" => "Sierra Leone",
			"SM" => "San Marino",
			"SN" => "Senegal",
			"SO" => "Somalia",
			"SR" => "Suriname",
			"SS" => "South Sudan",
			"ST" => "Sao Tome and Principe",
			"SV" => "El Salvador",
			"SX" => "Sint Maarten",
			"SY" => "Syrian Arab Republic",
			"SZ" => "Swaziland",
			"TC" => "Turks and Caicos Islands",
			"TD" => "Chad",
			"TF" => "French Southern Territories",
			"TG" => "Togo",
			"TH" => "Thailand",
			"TJ" => "Tajikistan",
			"TK" => "Tokelau",
			"TL" => "Timor-Leste",
			"TM" => "Turkmenistan",
			"TN" => "Tunisia",
			"TO" => "Tonga",
			"TR" => "Turkey",
			"TT" => "Trinidad and Tobago",
			"TV" => "Tuvalu",
			"TW" => "Taiwan",
			"TZ" => "Tanzania, United Republic of",
			"UA" => "Ukraine",
			"UG" => "Uganda",
			"UM" => "United States Minor Outlying Islands",
			"US" => "United States",
			"UY" => "Uruguay",
			"UZ" => "Uzbekistan",
			"VA" => "Holy See (Vatican City State)",
			"VC" => "Saint Vincent and the Grenadines",
			"VE" => "Venezuela",
			"VG" => "Virgin Islands, British",
			"VI" => "Virgin Islands, U.S.",
			"VN" => "Vietnam",
			"VU" => "Vanuatu",
			"WF" => "Wallis and Futuna",
			"WS" => "Samoa",
			"YE" => "Yemen",
			"YT" => "Mayotte",
			"ZA" => "South Africa",
			"ZM" => "Zambia",
			"ZW" => "Zimbabwe",
		);
		//return $country_name[$country_code];	
		foreach ($country_name as $k1 => $v1) 
		{
			if($k1 == $country_code)
			{
				return $v1;
			}			
		} 	 
	}

	private function get_http_status_code($code)
	{
		$status_code = array(
			// 1XX
		    "100" => "Continue",
		    "101" => "Switching Protocols",
		    "102" => "Processing",
		    "103" => "Early Hints",
		    // 2XX
		    "200" => "OK",
		    "201" => "Created",
		    "202" => "Accepted",
		    "203" => "Non-Authoritative Information",
		    "204" => "No Content",
		    "206" => "Partial Content",
		    "207" => "Multi-Status",
		    "208" => "Already Reported",
		    "226" => "IM Used",
		    // 3XX
		    "300" => "Multiple Choices",
		    "301" => "Moved Permanently",
		    "302" => "Found",
		    "303" => "See Other",
		    "304" => "Not Modified",
		    "305" => "Use Proxy",
		    "306" => "Switch Proxy",
		    "307" => "Temporary Redirect",
		    "308" => "Permanent Redirect",
		    // 4XX
		    "400" => "Bad Request",
		    "401" => "Unauthorized",
		    "402" => "Payment Required",
		    "403" => "Forbidden",
		    "404" => "Not Found",
		    "405" => "Method Not Allowed",
		    "406" => "Not Acceptable",
		    "407" => "Proxy Authentication Required",
		    "408" => "Request Timeout",
		    "409" => "Conflict",
		    "410" => "Gone",
		    "411" => "Length Required",
		    "412" => "Precondition Failed",
		    "413" => "Payload Too Large",
		    "414" => "URI Too Long",
		    "415" => "Unsupported Media Type",
		    "416" => "Range Not Satisfiable",
		    "417" => "Expectation Failed",
		    "418" => "I'm a teapot",
		    "421" => "Misdirected Request",
		    "422" => "Unprocessable Entity",
		    "423" => "Locked",
		    "424" => "Failed Dependency",
		    "426" => "Upgrade Required",
		    "428" => "Precondition Required",
		    "429" => "Too Many Requests",
		    "431" => "Request Header Fields Too Large",
		    "451" => "Unavailable For Legal Reasons",

		    // 5XX
		    "500" => "Internal Server Error",
		    "501" => "Not Implemented",
		    "502" => "Bad Gateway",
		    "503" => "Service Unavailable",
		    "504" => "Gateway Timeout",
		    "505" => "HTTP Version Not Supported",
		    "506" => "Variant Also Negotiates",
		    "507" => "Insufficient Storage",
		    "508" => "Loop Detected",
		    "510" => "Not Extended",
		    "511" => "Network Authentication Required",
		);
		
		foreach ($status_code as $k1 => $v1) 
		{
			if($k1 == $code)
			{
				return $v1;
			}			
		} 		
	}

	
	//--->private functions > end
}	
