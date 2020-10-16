<?php

namespace App\Altcore\Middleware;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Altcore\Helpers\AltAuth;
use Illuminate\Support\Facades\File;

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
      
      } catch(\Exception $e){
          return abort(401,$e->getMessage());
      }
    }
}
