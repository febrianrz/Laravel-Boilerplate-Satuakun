<?php

namespace App\Altcore\Middleware;

use Closure;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Altcore\Helpers\Form;
use App\Altcore\Helpers\AltAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SSOMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
      if($request->header('Authorization')){
        $user = $this->getUser($request);
        
        
        if($user){
            $isSuperadmin = false;
            $scopes = include(app_path()."/Altcore/scopes/default.php");
            if($user->role){
              $path = (app_path()."/Altcore/scopes/".Str::snake($user->role->name).".php");
              if(File::exists($path)){
                $scopes = include($path);
              }
              

              $alwaysSuperadmin = config('micro')['role_always_true'];
              
              $isSuperadmin = in_array(strtolower($user->role->name),$alwaysSuperadmin);
              
            }
            $request->merge([
                '_auth'  => (object)[
                  'user' => $user->user,
                  'companies' => $user->companies,
                  'company' => $user->company,
                  'service' => $user->service,
                  'frontend' => $user->frontend,
                  'role' => $user->role,
                  'scopes'=> $scopes,
                ],
                '_all'  => $user,
                '_check'=> new AltAuth($user,$scopes,$isSuperadmin),
                '_form' => new Form($request,$user),
                '_superadmin'=> $isSuperadmin
            ]);
            return $next($request);
        } 
        
        return abort(401,'Cannot access this resource or unauthenticate server');
      } 
      return abort(401,'Cannot access this resource or unauthenticate server');
    }

    public function getUser(Request $request)
    {
      try {
        if(!Schema::hasTable('_userapp')){
          $this->createTableUser();
        }
        $session_id = $request->header('SessionAppId');
        if(!$session_id) return $this->loadUserData($request);

        // Cek apakah session id ada datanya
        $session = DB::table('_userapp')->where('session_id',$session_id)->first();
        
        if(!$session) {
            //validate session id
            $this->syncUserApp($request);
            $session = DB::table('_userapp')->where('session_id',$session_id)->first();
        } else {
            $currentDate = Carbon::now();
            $expiredDate = new Carbon($session->expired_at);
            if(!$expiredDate->greaterThan($currentDate)){
                $this->syncUserApp($request);
            }
            $session = DB::table('_userapp')->where('session_id',$session_id)->first();
        }

        return json_decode($session->data);
      
      } catch(\Exception $e){
          return abort(401,$e->getMessage());
      }
    }

    private function validateSessionId($request){
      $session_id = $request->header('SessionAppId');
      $auth_url = config('micro')['url']['auth'];
      $client = new Client([
          'base_uri'  => $auth_url,
          'timeout'   => (isset(config('micro')['timeout'])?config('micro')['timeout']:10)
      ]);
      $uri = '/api/v2/session';
      $response = $client->request('POST', $uri, [
          'headers' => [
              'Authorization' => $request->header('Authorization'),
              'Accept'     => 'application/json',
          ],
      ]);
      // dd($response->getBody()->getContents());
      if($response->getStatusCode() != 200){
          throw new \Exception('Unauthorize');
      }

      $serverSession = json_decode($response->getBody()->getContents());
      if($serverSession->id == $session_id) {
          return $serverSession;
      }
      return null;
  }



    private function syncUserApp($request){
      $session_id = $request->header('SessionAppId');
      $serverSession = $this->validateSessionId($request);
      // dd($serverSession);
      if($serverSession){
          // load data user
          $user = $this->loadUserData($request);
          // dd($user->user);
          $dbUser = DB::table('_userapp')->where('user_id',$user->user->id)->first();
          
          if(!$dbUser) {
              DB::table('_userapp')
                  ->insert([
                      'id'            => Uuid::uuid1(),
                      'session_id'    => $session_id,
                      'user_id'       => $user->user->id,
                      'name'          => $user->user->name,
                      'email'         => $user->user->email,
                      'data'          => json_encode($user),
                      'last_sync'     => date('Y-m-d H:i:s'),
                      'created_at'    => date('Y-m-d H:i:s'),
                      'expired_at'    => $serverSession->expired_at
                  ]);
                  
              $session = DB::table('_userapp')->where('session_id',$session_id)->first();
              // dd($session);
          } else {
              //update
              DB::table('_userapp')
                  ->where([
                      'user_id'       => $user->id,
                  ])
                  ->update([
                      'session_id'    => $session_id,
                      'name'          => $user->name,
                      'email'         => $user->name,
                      'data'          => json_encode($user),
                      'last_sync'     => date('Y-m-d H:i:s'),
                      'updated_at'    => date('Y-m-d H:i:s'),
                      'expired_at'    => $serverSession->expired_at
                  ]);
              $session = DB::table('_userapp')->where('session_id',$session_id)->first();
          }
      } else {
          return abort(401,'Invalid Session ID');
      }
  }

    private function loadUserData($request)
    {
      $auth_url = config('micro')['url']['auth'];
        $client = new Client([
            'base_uri'  => $auth_url,
            'timeout'   => (isset(config('micro')['timeout'])?config('micro')['timeout']:10)
        ]);
        $uri = '/api/srv/v1/myprofile';
        $time = time();
        $sign = config('micro')['key']['service_key'].$time.config('micro')['key']['service_secret'];
        $hash = hash('sha256',$sign);
        $response = $client->request('POST', $uri, [
          'headers' => [
              'Authorization' => $request->header('Authorization'),
              'Accept'        => 'application/json',
              'S-Key'         => config('micro')['key']['service_key'],
              'S-Timestamp'   => $time,
              'S-Sign'        => $hash,
              'S-Frontend'    => $request->header('S-Frontend'),
              'S-Company'    => $request->header('S-Company')
          ],
          'form_params' => [
            
          ]
        ]);
        if($response->getStatusCode() != 200){
            throw new \Exception('Unauthorize');
        } 
        return json_decode($response->getBody()->getContents());
    }

    private function createTableUser(){
      Schema::create('_userapp', function(Blueprint $table){
          $table->uuid('id')->primary();
          $table->uuid('session_id')->index();
          $table->unsignedBigInteger('user_id');
          $table->string('name')->nullable();
          $table->string('email')->nullable();
          $table->longText('data')->nullable();
          $table->longText('role')->nullable();
          $table->longText('preference')->nullable();
          $table->datetime('last_sync')->nullable();
          $table->datetime('last_used')->nullable();
          $table->datetime('expired_at')->nullable();
          $table->timestamps();
      });
  }

}
