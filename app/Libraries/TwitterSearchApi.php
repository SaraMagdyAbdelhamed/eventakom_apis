<?php 
/**
 *	TwitterSearchApi : Simple php Class to get top and recent tweets 
 *   according to giving keyword
 *@author Ahmed Alaa
 *
 */

namespace App\Libraries;
use App\Libraries\tmhOAuth;
/**
* 
*/
class TwitterSearchApi
{

	/**
     * Preform twitter Search with given keyword and search type ['recent' or poular]
     *@param $query (string) related to search word
     *@param $search_type (string) 'recent' , popular or mixed
     *@return int
     * 
     */
    public function StartTwitterSearch($query,$search_type,$limit)
    {
        $max_id = 0;
        $i = 0 ; 
        $tweets_found=0; 
        $search_query=['q' =>'%23'.$query , 'result_type' =>$search_type,'count' => 10];   
        $connection = $this->PrepareConnection(); 
        //loop to call the api again to load more results         
        while ($i < 2) {
            sleep(1);
            if ($max_id == 0) {
              $this->TalkToTwitterSearchApi($connection,$search_query);
            // Repeated API call
            } else {
                // Collect older tweets using max_id in the search query to get more tweets
                --$max_id;
                $search_query['max_id'] = $max_id;
                 $this->TalkToTwitterSearchApi($connection,$search_query);
            }           
            // Exit on error
          if ($connection->response['code'] != 200) {

              print "Exited with error: " . $connection->response['code'] . "<br>";
                break;            
            } 
            // Process each tweet returned
            $results = json_decode($connection->response['response']);
            $tweets = $results->statuses;
         
         
         $i++;
         
        }
        return $results;
    }
    /**
     * Configure the settings required to talk to twitter search api
     *
     *@return object
     * 
     */
    private function PrepareConnection(){
        return new tmhOAuth(array(
            'user_token' => env('USER_TOKEN'),
            'user_secret' => env('USER_SECRET'),
            'consumer_key' => env('CONSUMER_KEY'),
            'consumer_secret' => env('CONSUMER_SECRET')
                )); 
    }
    /**
     * Send Get request with search parameters to twitter
     * this will make the url like @ https://api.twitter.com/1.1/search/tweets.json?q=obama&result_type=recent&count=100
     *@param $connection (object)
     *@param $search_query(array) with search query and seach type
     *@return int
     * 
     */
    private function TalkToTwitterSearchApi($connection,$search_query=[]){

       return    $connection->request('GET', $connection->url('1.1/search/tweets'),$search_query);                   
    }
 
   

    

    
    
	
}






















?>