<?php

/**
 * ShaarliApiClient
 */
class ShaarliApiClient {

	public $url = null;

	/**
	 * Constructor
	 */
	public function __construct( $url ) {

		$this->url = $url;
	}

	/**
	 * Call API
	 * @param string action
	 * @param array arguments
	 */
	public function callApi( $action, $arguments = null ) {

		$url = rtrim($this->url, '/') . '/' . $action;

		if( $arguments != null && !empty($arguments) ) {

			$url .= ('?' . http_build_query($arguments) );
		}

		$options = array(
		  'http' => array(
		    'method' => "GET",
		    'header' => "Accept-language: fr\r\n" .
		              "User-Agent: shaarli-api-client\r\n"
		  )
		);

		$context = stream_context_create($options);

		$content = @file_get_contents($url, false, $context);

		if( !empty($content) ) {

			$content = json_decode($content);
			$content = $this->_filter($action, $content);
			return $content;
		}
		else {

			throw new Exception('Unable to call API');
		}
	}

	/**
	 * feeds
	 * La liste des shaarlis
	 */
	public function feeds( $arguments = null ) {
		return $this->callApi('feeds', $arguments);
	}

	/**
	 * latest
	 * Les derniers billets
	 */
	public function latest( $arguments = null ) {
		return $this->callApi('latest', $arguments);
	}
	/**
	 * top
	 * Les liens les plus partagés
	 */
	public function top( $arguments = null ) {
		return $this->callApi('top', $arguments);
	}

	/**
	 * search
	 * Rechercher dans les billets
	 */
	public function search( $term, $arguments = array() ) {

		$arguments['q'] = $term;

		return $this->callApi('search', $arguments);
	}

	/**
	 * discussion
	 * Rechercher une discussion
	 */
	public function discussion( $url, $arguments = array() ) {

		$arguments['url'] = $url;

		return $this->callApi('discussion', $arguments);
	}

	/**
	 * #vieuxhacktoutpourri
	 *
	 * Filter la reponse de l'API suivant les ids specifies
	 * dans la variable filter (1ere variable de la fonction)
	 *
	 * Pour avoir une idee des id a mettre dans cette liste
	 * allez sur SHAARLI_API_URL/feeds avec SHAARLI_API_URL
	 * definie dans config.php (Par defaut : https://nexen.mkdir.fr/shaarli-api/feeds),
	 * et recuperez les ids qui vous interessent.
	 * Par exemple pour sebsauvage sur l'api de nexen, l'id est 1.
	 * Cela filtrera les reponses 'feeds / search / latest' et vous
	 * evitera d'avoir a installer l'API. Par contre vous ne pourrez pas
	 * ajouter de liens, mais seulement filtrer parmi ceux disponibles sur
	 * l'API que vous avez choisi.
	 *
	 * Il manque la reponse 'top'.
	 * Ah et il me semble que ca ne marchera qu'avec PHP >= 5.3 (closure).
	 */
	private function _filter($action, $content){
		/* ici je recupere les liens de sebsauvage et Horyax via l'API de nexen. */
		$filters = array(1, 3);

		if($action == 'feeds' || $action == 'search'){
			return array_filter($content, function($item) use ($filters){
				return in_array($item->id, $filters);
			});
		}
		if($action == 'latest'){
			return array_filter($content, function($item) use ($filters){
				return in_array($item->feed->id, $filters);
			});
		}
		return $content;
	}
}
