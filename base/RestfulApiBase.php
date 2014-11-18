<?php 
/**
 * Base class with general functions to use in a API
 * @package restful-api
 */
abstract class RestfulApiBase
{
	var $collection;

	var $collectionParams;

	var $operationParams;

	var $currentOperation;

	var $data;

	var $messages = array(
			"404" =>"Unknown method."
		);

	/**
	 * Evaluate collection parameter, Use $_GET parameters if $collection is FALSE or using the parameter was sent
	 * @param boolean, string, array $collection 
	 * @return none
	 */
	private function evaluateCollection($collection)
	{

		if( $collection === FALSE )
		{
			$collection = current($_GET);	

			array_shift($_GET);
		}	

		$this->operationParams["get"]=$_GET;		

		$this->buildParams( $collection );	
	}

	/**
	 * Build parameters generating or not , an array with name of collection ( first element of array ) and collection parameters
	 * from url
	 * 
	 * If the current param is a string, we need to cast this to an array and split.
	 * @param array,string $uri 
	 * @return none
	 */
	protected function buildParams( $uri )
	{
		if( is_string($uri) )		
		{
			$uri = explode("/", $uri);
		}

		if( is_array($uri) )
		{
			$this->collection = current($uri);

			$_params=array();

			if(count($uri)>0)
			{
				foreach($uri as $k=>$v)
				{	
					if($k%2==0 or $k==0)
					{
						$_params["collection"][]=$v;
					}
					else
					{
						$_params["resource"][]=$v;	
					}
					
				}
			}

			$this->collectionParams	= $_params;
		}
	}


	/**
	 * Check current operation, to get all related parameters
	 * 
	 * and update current operation and params from that type of operation
	 * 
	 * Available operations:
	 * 
	 * POST
	 * 
	 * @return none
	 */
	private function checkOperation	(  )
	{		
		$this->currentOperation="get";
		if(!empty($_POST))
		{
			$this->operationParams["post"]=$_POST;
			$this->currentOperation="post";
		}
	}

	/**
	 * Validate collection if exists in current object 
	 * 
	 * If not exists, generate a 404 error, else, generate a successful response
	 * 
	 * If we send a "debug" parameter from URL, we can see available properties in this class in final response
	 * 
	 * This save response in $data property to use after in printResult function
	 * 
	 * @param object $object 
	 * @return none
	 */
	private function validateCollection( $object )
	{
		$method = $this->collection."_".$this->currentOperation;
		$response = array();
		if(!method_exists($object, $method))
		{
			http_response_code(404);
			$response["message"]=$this->messages["404"];
		}
		else
		{
			http_response_code(200);
			$response = (array) $object->{$method}();
		}

		if(isset($_GET["debug"]))
		{
			$response["debug_".time()]=array(
					"vars" => get_object_vars($this),
					"METHOD" => $_SERVER['REQUEST_METHOD'],
					"POST" => $_POST,
					"GET" =>$_GET					
				);
		}

		$this->data=array_merge(array("status" => http_response_code()),$response);
	}

	/**
	 * Print current result in json format
	 * @return none
	 */
	private function printResult(  )
	{
		header('Content-Type: application/json');
		echo json_encode($this->data);
	}

	/**
	 * Run all functions related with any calls comming from url 
	 * @param object $object 
	 * @param array,string $collection 
	 * @return none
	 */
	protected function run( $object, $collection = FALSE )
	{
		$this->evaluateCollection($collection);

		$this->checkOperation();

		$this->validateCollection($object);
		
		$this->printResult();
	}
}
?>