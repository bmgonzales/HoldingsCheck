<?php
  //form submit
  
  	$option = '';
	$output = '';
	$results = '';
	$holding = '';
	$isbnlist = '';
	$issnlist = '';
  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
	
	$option = $_POST['option'];
	

	
	if ($option == 'opt_isbn') {
		
		//retrieve ISBN list
		$isbnlist = $_POST['isbnlist'];
		$isbnlist = str_replace('-', '', $isbnlist);
		$isbnlist = explode("\r", $isbnlist);
		$isbnlist = array_map("trim", $isbnlist);
		$isbnlist = array_filter($isbnlist);
		$isbnlist = array_values($isbnlist);
		
		//loop through ISBN array
		foreach ($isbnlist as $isbn) {
			if (strlen($isbn) == 9) {
				$isbn = "0" . $isbn;
				
			}
			
			$url = "http://www.worldcat.org/webservices/catalog/content/libraries/isbn/{$isbn}?oclcsymbol=SAT&wskey=pm9SJTEW02D38zwtAQgHq2gWh9PGTsusDklgnPNb8ZSMwhoSduzUPokOXWlg7HXj3vgSj5CY3LJR7mM2";
			$xmlrecord = array();
			$isbn_checked = '';
			$xml = simplexml_load_file($url);
			
			
				if (!$xml) {
					$message = "Could not retrieve record from WorldCat";
				} else {
					
					$get_citation = "http://www.worldcat.org/webservices/catalog/content/citations/isbn/{$isbn}?wskey=pm9SJTEW02D38zwtAQgHq2gWh9PGTsusDklgnPNb8ZSMwhoSduzUPokOXWlg7HXj3vgSj5CY3LJR7mM2";
					$citation = file_get_contents($get_citation);	
					
						if ($citation == "info:srw/diagnostic/1/65Record does not exist") {
							$citation = "<span style='color:red;'>Record not found in WorldCat</span>";
						}
					
						if ($xml->holding) {
							$href = $xml->holding->electronicAddress->text;
							$copies = $xml->holding->holdingSimple->copiesSummary->copiesCount;
							
							$isbn_checked = "<tr><td>" . $isbn . "</td><td><a href='" . $href . "' target='_blank'><strong>" . $citation . "</strong></a></td><td><span style='color:red;'><strong>" . $copies . " copy(s)</strong></span></td></tr>";
							
						} elseif ($xml->diagnostic) {
							$notfound = $xml->diagnostic->message;
							
							if ($notfound == "Record does not exist") {
								$message = "<span style='color:red;'>May not be published yet</span>";
							} elseif ($notfound == "Holding not found") {
								$message = "NOT HELD BY OLLU";
							}
							
							$isbn_checked = "<tr><td>" . $isbn . "</td><td> " . $citation . "</td><td> " . $message . "</td></tr>";
						}
				}
				
				$output .= $isbn_checked;
				
		}//end foreach loop
	
		//results
		ob_start();
		print "<table id='output' style='width:100%;'><thead><th>ISBN</th><th style='width:70%;'>Title</th><th>OLLU Holdings</th></thead><tbody>";
		print $output;
		print "</tbody></table>";
		$results = ob_get_clean();	
		

  } //end opt_isbn
  
  elseif ($option == 'opt_issn') {
		
		//retrieve ISSN list
		$issnlist = $_POST['issnlist'];
		$isbnlist = str_replace('-', '', $issnlist);
		$issnlist = explode("\r", $issnlist);
		$issnlist = array_map("trim", $issnlist);
		$issnlist = array_filter($issnlist);
		$issnlist = array_values($issnlist);
		
		//loop through ISSN array
		foreach ($issnlist as $issn) {
			
			$url = "http://www.worldcat.org/webservices/catalog/content/issn/{$issn}?wskey=pm9SJTEW02D38zwtAQgHq2gWh9PGTsusDklgnPNb8ZSMwhoSduzUPokOXWlg7HXj3vgSj5CY3LJR7mM2";
			$xmlrecord = array();
			$issn_checked = '';
			$xml = simplexml_load_file($url);
			
			
				if (!$xml) {
					$message = "Could not retrieve record from WorldCat";
				} else {
					
					$get_citation = "http://www.worldcat.org/webservices/catalog/content/citations/issn/{$issn}?wskey=pm9SJTEW02D38zwtAQgHq2gWh9PGTsusDklgnPNb8ZSMwhoSduzUPokOXWlg7HXj3vgSj5CY3LJR7mM2";
					$citation = file_get_contents($get_citation);	
					
						if ($citation == "info:srw/diagnostic/1/65Record does not exist") {
							$citation = "<span style='color:red;'>Record not found in WorldCat</span>";
						}
						//get oclc number
						$oclcnum = $xml->controlfield;
						
						//search oclc number
						$url2 = "http://www.worldcat.org/webservices/catalog/content/libraries/{$oclcnum}&oclcsymbol=SAT?wskey=pm9SJTEW02D38zwtAQgHq2gWh9PGTsusDklgnPNb8ZSMwhoSduzUPokOXWlg7HXj3vgSj5CY3LJR7mM2";
						
						$xml2 = simplexml_load_file($url2);
						
							if ($xml2->holding) {
							$href = $xml2->holding->electronicAddress->text;
							$copies = $xml2->holding->holdingSimple->copiesSummary->copiesCount;
							
							$issn_checked = "<tr><td>" . $issn . "</td><td><a href='" . $href . "' target='_blank'><strong>" . $citation . "</strong></a></td><td><span style='color:red;'><strong>Held by OLLU</strong></span></td></tr>";
							
						} elseif ($xml2->diagnostic) {
							$notfound = $xml2->diagnostic->message;
							
							if ($notfound == "Record does not exist") {
								$message = "<span style='color:red;'>Record does not exist</span>";
							} elseif ($notfound == "Holding not found") {
								$message = "NOT HELD BY OLLU";
							}
							
							$issn_checked = "<tr><td>" . $issn . "</td><td> " . $citation . "</td><td> " . $message . "</td></tr>";
						}
				
							$output .= $issn_checked;
							
					}
		}//end foreach loop
	
		//results
		ob_start();
		print "<table id='output' style='width:100%;'><thead><th>ISSN</th><th style='width:70%;'>Title</th><th>OLLU Holdings</th></thead><tbody>";
		print $output;
		print "</tbody></table>";
		$results = ob_get_clean();	
		
	  
  } //end opt_issn
  
}//end form submit
 ?>
 
