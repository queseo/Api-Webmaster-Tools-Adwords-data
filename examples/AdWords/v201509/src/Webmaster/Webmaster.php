<?php
require_once dirname(dirname(__FILE__)) . '/../init.php';

require_once UTIL_PATH . '/MapUtils.php';

class Webmaster
{
    private $client_email;
    private $private_key;
    private $scopes;
    private $credentials;

    public function __construct($client_email, $private_key) {
        $this->client_email = $client_email;
        $this->private_key = $private_key;
        $this->scopes = array(Google_Service_Webmasters::WEBMASTERS_READONLY);

        $this->credentials = new Google_Auth_AssertionCredentials(
            $this->client_email,
            $this->scopes,
            $this->private_key
        );
    }

    private function auth(){
        $client = new Google_Client();
        $client->setAssertionCredentials($this->credentials);
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        return $client;
    }

    public function test(){
		//Conexión a base de datos
		$enlace = new mysqli('nombredehost','usuario','contraseña','seo');
		
	  // Get AdWordsUser from credentials in "../auth.ini"
	  // relative to the AdWordsUser.php file's directory.
	  $user = new AdWordsUser();

	  // Log every SOAP XML request and response.
	  $user->LogAll();		
		
		//	AQUI PONEMOS EL DOMINIO A ANALIZAR DE WEBMASTERTOOLS	
        $domain = 'nombre del dominio de webmastertools';


        /*if(!$this_domain){
            die('Can\'t find domain: '.$domain);
        }*/

		//PONEMOS FECHAS QUE QUEREMOS CONSULTAR
        $rows = $this->get_query(
            $domain,
            array('2016-01-01', '2016-01-02'), //ESTAS SON LAS FECHAS, PRIMERO "START DATE", LUEGO "END DATE"
            array('query' => 'query')
        );
		
		
        //$this->pp($rows);
		$i=0;
        foreach($rows as $r){
            //echo $r->keys[0].', '.$r->impressions.', '.$r->clicks.', '.$r->ctr.', '.$r->position.' <br />';

			//GUARDAMOS EN BASE DE DATOS LOS DATOS DE WEBMASTERTOOLS
			$query = $this-> mysql_insert('querys', array(
				'idQuery' => $i,
				'Query' => $r->keys[0],
			),$enlace);
			$i++;
			$WTData = $this-> mysql_insert('wtdata', array(
				'idQuery' => $i,
				'Imp' => $r->impressions,
				'Clic' => $r->clicks,
				'CTR' => $r->ctr,
				'PM' => $r->position,
			),$enlace);
			
			//ENVIAMOS LA PALABRA CLAVE A ADWORDS PARA ANALIZAR
			$ADWDatas = $this->GetKeywordIdeasExample($user,$enlace,$r->keys[0],$i);
        }
		
        exit;

    }
	public function mysql_insert($table, $inserts, $enlace) {
				$values = array_map(array($enlace, 'real_escape_string'), $inserts);

				$keys = array_keys($inserts);
					
				return mysqli_query($enlace,'INSERT INTO `'.$table.'` (`'.implode('`,`', $keys).'`) VALUES (\''.implode('\',\'', $values).'\')');
			}
	
//FUNCION PARA EXTRAER INFORMACION DE WEBMASTERTOOLS	
    public function get_query($domain, $date = null, $this_filter = array()){
        $client = $this->auth();
        $webmastersService = new Google_Service_Webmasters($client);
        $searchanalytics = $webmastersService->searchanalytics;

        // Build query
        $request = new Google_Service_Webmasters_SearchAnalyticsQueryRequest;

        $request->setStartDate($date[0]);
        $request->setEndDate($date[1]);

        $active_query = (isset($this_filter['query'])) ? $this_filter['query'] : 'query';
        $request->setDimensions(array($active_query));

        $active_search_type = (isset($this_filter['search_type'])) ? $this_filter['search_type'] : 'web';
        $request->setSearchType($active_search_type);

       /* if(isset($this_filter['mobile'])){
            $filter = new Google_Service_Webmasters_ApiDimensionFilter;
            $filter->setDimension("device");
            $filter->setExpression("MOBILE");
            $filters = new Google_Service_Webmasters_ApiDimensionFilterGroup;
            $filters->setFilters(array($filter));
            $request->setDimensionFilterGroups(array($filters));
        }*/

        $qsearch = $searchanalytics->query($domain, $request);

        $rows = $qsearch->getRows();

        return $rows;
    }

	
	//FUNCION PARA EXTRAER INFORMACION DE ADWORDS KEYWORDTOOLS
	public function GetKeywordIdeasExample(AdWordsUser $user,$enlace,$keyword,$i) {
  // Get the service, which loads the required classes.
  $targetingIdeaService =
      $user->GetService('TargetingIdeaService', ADWORDS_VERSION);

  // Create selector.
  $selector = new TargetingIdeaSelector();
  //$selector->requestType = 'IDEAS';
  $selector->requestType = 'STATS';
  $selector->ideaType = 'KEYWORD';
  $selector->requestedAttributeTypes = array('KEYWORD_TEXT', 'SEARCH_VOLUME',
      'COMPETITION','AVERAGE_CPC','TARGETED_MONTHLY_SEARCHES');

  // PARA ESPECIFICAR IDIOMA
  // MAS INFO EN  https://developers.google.com/adwords/api/docs/appendix/languagecodes
  $languageParameter = new LanguageSearchParameter();
  $spanish = new Language();
  $spanish->id = 1003; //ESTE ES EL ID A CAMBIAR DEL IDIOMA
  $languageParameter->languages = array($spanish);
  
  //PARA ESPECIFICAR REGION
  // MAS INFO EN https://developers.google.com/adwords/api/docs/appendix/geotargeting
  $locationParameter = new LocationSearchParameter();
  $spain = new Location();
  $spain->id = 2724; //ESTE ES EL ID A CAMBIAR
  $locationParameter->locations = array($spain);

  // Create related to query search parameter.
  $relatedToQuerySearchParameter = new RelatedToQuerySearchParameter();
  $relatedToQuerySearchParameter->queries = array($keyword);
  $selector->searchParameters[] = $relatedToQuerySearchParameter;
  $selector->searchParameters[] = $languageParameter;
  $selector->searchParameters[] = $locationParameter;

  // Set selector paging (required by this service).
  $selector->paging = new Paging(0, AdWordsConstants::RECOMMENDED_PAGE_SIZE);

  do {
    // Make the get request.
    $page = $targetingIdeaService->get($selector);

    // Display results.
    if (isset($page->entries)) {
      foreach ($page->entries as $targetingIdea) {
        $data = MapUtils::GetMap($targetingIdea->data);
        $keyword = $data['KEYWORD_TEXT']->value;
        $search_volume = isset($data['SEARCH_VOLUME']->value)
            ? $data['SEARCH_VOLUME']->value : 0;
        $competition = isset($data['COMPETITION']->value)
            ? $data['COMPETITION']->value : 0;
			$averagecpc = isset($data['AVERAGE_CPC']->value)
            ? $data['AVERAGE_CPC']->value : 0;
			$monthlysearches = isset($data['TARGETED_MONTHLY_SEARCHES']->value)
            ? $data['TARGETED_MONTHLY_SEARCHES']->value : 0;
	/*$competition=(string)$competition;
	$averagecpc=(string)$averagecpc;
	$monthlysearches=(string)$monthlysearches;
			$linea = $keyword./*$sep.$categoryIds.$sep.$search_volume.$sep.$competition./*$sep.$averagecpc.$sep./*$monthlysearches."\n";
			fwrite($f,$linea);*/
		
        /*printf("Keyword idea with text '%s', category IDs (%s) and average "
            . "monthly search volume '%s' was found.\n",
            $keyword, $monthlysearches['1'], $search_volume);*/
			$enero = (string)$monthlysearches['1']->count;
			$febrero = (string)$monthlysearches['2']->count;
			$marzo = (string)$monthlysearches['3']->count;
			$abril = (string)$monthlysearches['4']->count;
			$mayo = (string)$monthlysearches['5']->count;
			$junio = (string)$monthlysearches['6']->count;
			$julio =(string)$monthlysearches['7']->count;
			$agosto = (string)$monthlysearches['8']->count;
			$septiembre = (string)$monthlysearches['9']->count;
			$octubre = (string)$monthlysearches['10']->count;
			$noviembre = (string)$monthlysearches['11']->count;
			$diciembre = (string)$monthlysearches['0']->count;
	
					//ENVIAMOS DATOS A LA BASE DE DATOS
						$ADWData = $this-> mysql_insert('adwdata', array(
							'idQuery' => $i,
							'SearchVolume' => $search_volume,
							'Dificultad' => $competition,
							'CPC' => $averagecpc,
							'Enero' => $enero,
							'Febrero' => $febrero,
							'Marzo' => $marzo,
							'Abril' => $abril,
							'Mayo' => $mayo,
							'Junio' => $junio,
							'Julio' => $julio,
							'Agosto' => $agosto,
							'Septiembre' => $septiembre,
							'Octubre' => $octubre,
							'Noviembre' => $noviembre,
							'Diciembre' => $diciembre,
							),$enlace);
      }
    } else {
      print "No keywords data were found.\n";
    }

    // Advance the paging index.
    $selector->paging->startIndex += AdWordsConstants::RECOMMENDED_PAGE_SIZE;
  } while ($page->totalNumEntries > $selector->paging->startIndex);

}
}