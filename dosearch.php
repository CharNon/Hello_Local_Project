<?php
				
	$bingID = "5F66EB2E4B8414A8283F00B19CA7B37E3E422B31";
	$kw = $_GET["kw"];
	$page = intval($_GET["page"]);
	$sourceType = $_GET["type"];
	$endpoint = $_GET["endpoint"];
	// construct search query for Bing
	if($sourceType=='image'){
		$offset = ($page - 1) * 9;
		$request = 'http://api.search.live.net/json.aspx?Appid='.$bingID.'&query='.urlencode($kw).'&sources=image'.'&Options=EnableHighlighting&image.count=9&image.offset='.$offset;
		$response = file_get_contents($request);
	}else if($sourceType=='web'){
		$offset = ($page - 1) * 5;
		$request = 'http://api.search.live.net/json.aspx?Appid='.$bingID.'&query='.urlencode($kw).'&sources=web'.'&Options=EnableHighlighting&web.count=5&web.offset='.$offset;
		$response = file_get_contents($request);
	}else if($sourceType=='pdf'){
		$offset = ($page - 1) * 5;
		$request = 'http://api.search.live.net/json.aspx?Appid='.$bingID.'&query='.urlencode($kw).'&sources=web'.'&Options=EnableHighlighting&web.count=5&web.fileType=pdf&web.offset='.$offset;
		$response = file_get_contents($request);
	}else if($sourceType=='ppt'){
		$offset = ($page - 1) * 5;
		$request = 'http://api.search.live.net/json.aspx?Appid='.$bingID.'&query='.urlencode($kw).'&sources=web'.'&Options=EnableHighlighting&web.count=5&web.fileType=ppt&web.offset='.$offset;
		$response = file_get_contents($request);
	}else if($sourceType=='teacher_name_site'){
		$index = strpos($kw," ");
		$fname = substr($kw,0,strpos($kw," "));
		$lname = substr($kw,strpos($kw," ")+1);
		if($index == NULL){
			//$query =	'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
			//			'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
			//			'SELECT ?s ?fname ?lname ?workAt WHERE {'.
			//			'?s foaf:firstName ?fname. '.
			//			'FILTER regex(?fname,"'.$kw.'",\'i\').'.
			//			'?s foaf:lastName ?lname.'.
			//			'?s otp:workAt ?workAt.}';
			$query =		'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
							'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
							'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
							'SELECT ?s ?fname ?lname ?InstAddress ?InstDistrict ?InstProvince ?InstPostcode ?area WHERE {'.
									'?s foaf:firstName ?fname. '.
									'FILTER regex(?fname,"'.$kw.'",\'i\').'.
									'?s foaf:lastName ?lname.'.
									'?s otp:institutionalAddress ?InstAddress.'.
									'?s otp:institutionalDistrict ?InstDistrict.'.
									'?s otp:institutionalProvince ?InstProvince.'.
									'?s otp:institutionalPostcode ?InstPostcode.'.
									'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Discipline ?area.}';
		}else{
			//$query =	'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
			//			'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
						
			//			'SELECT ?s ?fname ?lname ?workAt WHERE {'.
			//			'?s foaf:firstName ?fname. '.
			//			'FILTER regex(?fname,"'.$fname.'",\'i\').'.
			//			'?s foaf:lastName ?lname.'.
			//			'FILTER regex(?lname,"'.$lname.'",\'i\').'.
			//			'?s otp:workAt ?workAt.}';
			$query =		'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
							'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
							'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
							'SELECT ?s ?fname ?lname ?InstAddress ?InstDistrict ?InstProvince ?InstPostcode ?area WHERE {'.
										'?s foaf:firstName ?fname.'.
										'FILTER regex(?fname,"'.$fname.'",\'i\').'.
										'?s foaf:lastName ?lname.'.
										'FILTER regex(?lname,"'.$lname.'",\'i\').'.
										'?s otp:institutionalAddress ?InstAddress.'.
										'?s otp:institutionalDistrict ?InstDistrict.'.
										'?s otp:institutionalProvince ?InstProvince.'.
										'?s otp:institutionalPostcode ?InstPostcode.'.
										'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Discipline ?area.}';
		}

		$i = 0;

		$response = '';

		//$endpoint = 'http://127.0.0.1/openteacher/,http://127.0.0.1/drupal6/';

		$avail_end = explode(",", $endpoint);

		$h = 0;
		$t = 0;
	

		foreach($avail_end as $n){
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			if($h > 224){													// กรณี search แล้วมีผลลัพธ์
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
				}else if($t==224){
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),215);
				}else{
					$response = substr($response,0,-7);
					$response .= ",";
					$response .= substr(file_get_contents($url),215);
				}
			}else{															// กรณี search แล้วไม่มีผลลัพธ์
				$t = strlen($response);									// หาความยาวของ response ในขณะนี้
				if($t==0){													// ถ้า response มีความยาวเท่ากับ 0 (หมายถึงหาที่ endpoint ตัวแรก)
					$response .= file_get_contents($url);
				}else if($t==224){
					$response = $response;
				}else{
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),215);
				}
			}
		}

		//$url = 'http://127.0.0.1/openteacher/sparql_endpoint?query='.urlencode($query).'&output=json';
		//$response = file_get_contents($url);
	}else if($sourceType=='teacher_expertise'){
		//$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
		//		'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
				
		//		'SELECT ?s ?fname ?lname ?expert ?workAt WHERE {'.
		//		'?s foaf:firstName ?fname.'.
		//		'?s foaf:lastName ?lname.'.
		//		'?s otp:expertIn ?expert.'.
		//		'FILTER regex(?expert,"'.$kw.'",\'i\').'.
		//		'?s otp:workAt ?workAt.}';
		
		$query =	'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
				'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
				
				'SELECT ?s ?fname ?lname ?expert ?InstAddress ?InstDistrict ?InstProvince ?InstPostcode WHERE {'.
				'?s foaf:firstName ?fname.'.
				'?s foaf:lastName ?lname.'.
				'?s otp:expertIn ?expert.'.
				'FILTER regex(?expert,"'.$kw.'",\'i\').'.
                 '?s otp:institutionalAddress ?InstAddress.'.
				'?s otp:institutionalDistrict ?InstDistrict.'.
				'?s otp:institutionalProvince ?InstProvince.'.
				'?s otp:institutionalPostcode ?InstPostcode.}';
		$i = 0;

		$response = '';

		//$endpoint = 'http://127.0.0.1/openteacher/,http://127.0.0.1/drupal6/';

		$avail_end = explode(",", $endpoint);

		$h = 0;
		$t = 0;
	
		/**foreach($avail_end as $n){
			//echo $n;
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 155){
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
					//$response = $response.', ';
				}else{
					$response = substr($response,0,-7);
					$response .= ", ";
					$response .= substr(file_get_contents($url),150);
				}
			}
		
		}**/
		foreach($avail_end as $n){
			//echo $n;
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			if($h > 226){													// กรณี search แล้วมีผลลัพธ์
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
				}else if($t==226){
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),221);
				}else{
					$response = substr($response,0,-7);
					$response .= ",";
					$response .= substr(file_get_contents($url),221);
				}
			}else{															// กรณี search แล้วไม่มีผลลัพธ์
				$t = strlen($response);									// หาความยาวของ response ในขณะนี้
				if($t==0){													// ถ้า response มีความยาวเท่ากับ 0 (หมายถึงหาที่ endpoint ตัวแรก)
					$response .= file_get_contents($url);
				}else if($t==226){
					$response = $response;
				}else{
					$response = substr($response,0,-5);
					$response .= substr(file_get_contents($url),221);
				}
			}
		}
	}else if($sourceType=='academicwork_name'){
		/**$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
				 'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
				 'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
				 'SELECT ?s ?title WHERE {'.
				 '?s dc:title ?title. '.
				 'FILTER regex(?title,"'.$kw.'",\'i\').}'.**/
		
		$query =		'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
						'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
						'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
						'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
							'SELECT ?s ?title ?type ?discipline ?level ?year WHERE {'.
										'?s dc:title ?title.'.
										'FILTER regex(?title,"'.$kw.'",\'i\').'.
										'?s lom:EducationalLearningResourceType ?type.'.
										'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Discipline ?discipline.'.
										'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.EducationLevel ?level.'.
										'?s otp:yearPublished ?year.}'.					
				$i = 0;

		$response = '';

		//$endpoint = 'http://127.0.0.1/openteacher/,http://127.0.0.1/drupal6/';

		$avail_end = explode(",", $endpoint);

		$h = 0;
		$t = 0;
	

		/**foreach($avail_end as $n){
			//echo $n;
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 108){
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
					//$response = $response.', ';
				}else{
					$response = substr($response,0,-7);
					$response .= ", ";
					$response .= substr(file_get_contents($url),100);
				}
			}
		
		}**/
		foreach($avail_end as $n){
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			if($h > 171){													// กรณี search แล้วมีผลลัพธ์ ของเดิม 108
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
				}else if($t==171){
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),163);	// ของเดิม 100
				}else{
					$response = substr($response,0,-7);
					$response .= ",";
					$response .= substr(file_get_contents($url),163);
				}
			}else{															// กรณี search แล้วไม่มีผลลัพธ์
				$t = strlen($response);									// หาความยาวของ response ในขณะนี้
				if($t==0){													// ถ้า response มีความยาวเท่ากับ 0 (หมายถึงหาที่ endpoint ตัวแรก)
					$response .= file_get_contents($url);
				}else if($t==171){
					$response = $response;
				}else{
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),163);
				}
			}
		}
	}else if($sourceType=='academicwork_keyword'){
		//$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
		//		'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
		//		'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
		//		'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
		//		'SELECT ?s ?title ?key WHERE {'.
		//		'?s dc:title ?title. '.
		//		'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Idea ?key. '.
		//		'FILTER regex(?key,"'.$kw.'",\'i\').}'.
												
		$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
				'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
				'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
				'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
				'SELECT ?s ?title ?key ?type ?discipline ?level ?year WHERE {'.
				'?s dc:title ?title. '.
				'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Idea ?key. '.
				'FILTER regex(?key,"'.$kw.'",\'i\').'.
				'?s lom:EducationalLearningResourceType ?type.'.
				'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Discipline ?discipline.'.
				'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.EducationLevel ?level.'.
				'?s otp:yearPublished ?year.}'.
		$i = 0;

		$response = '';

		//$endpoint = 'http://127.0.0.1/openteacher/,http://127.0.0.1/drupal6/';

		$avail_end = explode(",", $endpoint);

		$h = 0;
		$t = 0;
	

		/**foreach($avail_end as $n){
			//echo $n;
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 121){
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
					//$response = $response.', ';
				}else{
					$response = substr($response,0,-7);
					$response .= ", ";
					$response .= substr(file_get_contents($url),115);
				}
			}
		}**/
		foreach($avail_end as $n){
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 184){													// กรณี search แล้วมีผลลัพธ์ 121
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
				}else if($t==184){
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),178);	//115
				}else{
					$response = substr($response,0,-7);
					$response .= ",";
					$response .= substr(file_get_contents($url),178);
				}
			}else{															// กรณี search แล้วไม่มีผลลัพธ์
				$t = strlen($response);									// หาความยาวของ response ในขณะนี้
				if($t==0){													// ถ้า response มีความยาวเท่ากับ 0 (หมายถึงหาที่ endpoint ตัวแรก)
					$response .= file_get_contents($url);
				}else if($t==184){
					$response = $response;
				}else{
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),178);
				}
			}
		}
		
	}else if($sourceType=='academicwork_year'){
		//$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
		//		'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
		//		'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
		//		'SELECT ?s ?title ?year WHERE {'.
		//		'?s dc:title ?title. '.
		//		'?s otp:yearPublished ?year. '.
		//		'FILTER regex(?year,"'.$kw.'",\'i\').}'.
		
		$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
				'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
				'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
				'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
				'SELECT ?s ?title ?type ?discipline ?level ?year WHERE {'.
				'?s dc:title ?title. '.
				'?s lom:EducationalLearningResourceType ?type.'.
				'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Discipline ?discipline.'.
				'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.EducationLevel ?level.'.
				'?s otp:yearPublished ?year. '.
				'FILTER regex(?year,"'.$kw.'",\'i\').}'.										

		$i = 0;

		$response = '';

		//$endpoint = 'http://127.0.0.1/openteacher/,http://127.0.0.1/drupal6/';

		$avail_end = explode(",", $endpoint);

		$h = 0;
		$t = 0;
	

		/**foreach($avail_end as $n){
			//echo $n;
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 122){
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
					//$response = $response.', ';
				}else{
					$response = substr($response,0,-7);
					$response .= ", ";
					$response .= substr(file_get_contents($url),115);
				}
			}
		
		}**/
		foreach($avail_end as $n){
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 171){													// กรณี search แล้วมีผลลัพธ์ 122
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
				}else if($t==171){
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),164);	//115
				}else{
					$response = substr($response,0,-7);
					$response .= ",";
					$response .= substr(file_get_contents($url),164);
				}
			}else{															// กรณี search แล้วไม่มีผลลัพธ์
				$t = strlen($response);									// หาความยาวของ response ในขณะนี้
				if($t==0){													// ถ้า response มีความยาวเท่ากับ 0 (หมายถึงหาที่ endpoint ตัวแรก)
					$response .= file_get_contents($url);
				}else if($t==171){
					$response = $response;
				}else{
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),164);
				}
			}
		}
	}else if($sourceType=='course_name'){
		//$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
		//		'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
		//		'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
		//		'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
		//		'SELECT ?s ?title WHERE {'.
		//		'?s dc:title ?title. '.
		//		'FILTER regex(?title,"'.$kw.'",\'i\').}'.
												
		$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
				'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
				'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
				'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
				'SELECT ?s ?title ?level WHERE {'.
				'?s dc:title ?title. '.
				'FILTER regex(?title,"'.$kw.'",\'i\').'.
				'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Educationlevel ?level.}'.

		$i = 0;

		$response = '';

		//$endpoint = 'http://127.0.0.1/openteacher/,http://127.0.0.1/drupal6/';

		$avail_end = explode(",", $endpoint);

		$h = 0;
		$t = 0;
	

		/**foreach($avail_end as $n){
			//echo $n;
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 108){
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
					//$response = $response.', ';
				}else{
					$response = substr($response,0,-7);
					$response .= ", ";
					$response .= substr(file_get_contents($url),100);
				}
			}
		
		}**/
		foreach($avail_end as $n){
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 123){													// กรณี search แล้วมีผลลัพธ์  122
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
				}else if($t==123){
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),115);    //115
				}else{
					$response = substr($response,0,-7);
					$response .= ",";
					$response .= substr(file_get_contents($url),115);
				}
			}else{															// กรณี search แล้วไม่มีผลลัพธ์
				$t = strlen($response);									// หาความยาวของ response ในขณะนี้
				if($t==0){													// ถ้า response มีความยาวเท่ากับ 0 (หมายถึงหาที่ endpoint ตัวแรก)
					$response .= file_get_contents($url);
				}else if($t==123){
					$response = $response;
				}else{
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),115);
				}
			}
		}
	}else if($sourceType=='course_level'){
		$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
				'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
				'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
				'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
				'SELECT ?s ?title ?level WHERE {'.
				'?s dc:title ?title. '.
				'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Educationlevel ?level.'.
				'FILTER regex(?level,"'.$kw.'",\'i\').}'.
												

		$i = 0;

		$response = '';

		//$endpoint = 'http://127.0.0.1/openteacher/,http://127.0.0.1/drupal6/';

		$avail_end = explode(",", $endpoint);

		$h = 0;
		$t = 0;
	

		/**foreach($avail_end as $n){
			//echo $n;
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 123){
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
					//$response = $response.', ';
				}else{
					$response = substr($response,0,-7);
					$response .= ", ";
					$response .= substr(file_get_contents($url),115);
				}
			}
		
		}**/
		foreach($avail_end as $n){
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 123){													// กรณี search แล้วมีผลลัพธ์
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
				}else if($t==123){
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),115);
				}else{
					$response = substr($response,0,-7);
					$response .= ",";
					$response .= substr(file_get_contents($url),115);
				}
			}else{															// กรณี search แล้วไม่มีผลลัพธ์
				$t = strlen($response);									// หาความยาวของ response ในขณะนี้
				if($t==0){													// ถ้า response มีความยาวเท่ากับ 0 (หมายถึงหาที่ endpoint ตัวแรก)
					$response .= file_get_contents($url);
				}else if($t==123){
					$response = $response;
				}else{
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),115);
				}
			}
		}
	}else if($sourceType=='course_discipline'){
		//$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
		//		'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
		//		'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
		//		'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
		//		'SELECT ?s ?title ?discipline WHERE {'.
		//		'?s dc:title ?title. '.
		//		'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Discipline ?discipline.'.
		//		'FILTER regex(?discipline,"'.$kw.'",\'i\').}'.
		
		$query = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>'.
				'PREFIX otp:<http://vocab.ipst.ac.th/openteacher/1.0/>'.
				'PREFIX dc:<http://purl.org/dc/elements/1.1/>'.
				'PREFIX lom:<http://ltsc.ieee.org/xsd/LOM/>'.
				'SELECT ?s ?title ?discipline ?level WHERE {'.
				'?s dc:title ?title. '.
				'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Discipline ?discipline.'.
				'FILTER regex(?discipline,"'.$kw.'",\'i\').'.
				'?s lom:Classification.TaxonPath.TaxonEntry.Purpose.Educationlevel ?level.}'.										

		$i = 0;

		$response = '';

		//$endpoint = 'http://127.0.0.1/openteacher/,http://127.0.0.1/drupal6/';

		$avail_end = explode(",", $endpoint);

		$h = 0;
		$t = 0;
	

		/**foreach($avail_end as $n){
			//echo $n;
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 128){
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
					//$response = $response.', ';
				}else{
					$response = substr($response,0,-7);
					$response .= ", ";
					$response .= substr(file_get_contents($url),120);
				}
			}
		
		}**/
		foreach($avail_end as $n){
			$url = $n.'sparql_endpoint?query='.urlencode($query).'&output=json';
			$h = strlen(file_get_contents($url));
			//echo $h.'</br></br>';
			if($h > 143){													// กรณี search แล้วมีผลลัพธ์ 128
				$t = strlen($response);
				if($t==0){
					$response .= file_get_contents($url);
				}else if($t==143){
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),135);		//120
				}else{
					$response = substr($response,0,-7);
					$response .= ",";
					$response .= substr(file_get_contents($url),135);
				}
			}else{															// กรณี search แล้วไม่มีผลลัพธ์
				$t = strlen($response);									// หาความยาวของ response ในขณะนี้
				if($t==0){													// ถ้า response มีความยาวเท่ากับ 0 (หมายถึงหาที่ endpoint ตัวแรก)
					$response .= file_get_contents($url);
				}else if($t==143){
					$response = $response;
				}else{
					$response = substr($response,0,-7);
					$response .= substr(file_get_contents($url),135);
				}
			}
		}
	}
	
	echo $response;
;
?>

